<?php

namespace App\Services;

use App\Models\AnnModel;
use App\Models\AnnModelIngredient;
use App\Models\AnnTrainingRun;
use App\Models\DatasetVersion;
use Illuminate\Support\Facades\DB;

class AnnTrainingService
{
    public function __construct(
        protected AnnServiceClient $client
    ) {}

    /**
     * Submit training request dari dataset. Create training run row.
     *
     * @return AnnTrainingRun
     * @throws \RuntimeException kalau submit gagal
     */
    public function submitTraining(DatasetVersion $datasetVersion, array $config = []): AnnTrainingRun
    {
        $payload = $this->buildPayload($datasetVersion, $config);

        $run = AnnTrainingRun::create([
            'dataset_version_id' => $datasetVersion->id,
            'training_id' => $payload['training_id'],
            'training_status' => 'queued',
            'hyperparameters' => $payload['training_config'],
            'created_by' => auth()->id(),
        ]);

        try {
            $response = $this->client->submitTraining($payload);
        } catch (\Throwable $e) {
            $run->update([
                'training_status' => 'failed',
                'error_message' => 'Submit failed: ' . $e->getMessage(),
                'training_completed_at' => now(),
            ]);
            throw $e;
        }

        $run->update([
            'training_status' => 'running',
            'training_started_at' => now(),
        ]);

        return $run->fresh();
    }

    /**
     * Poll status sampai completed/failed, atau max attempts habis.
     * Persist model result saat completed.
     */
    public function pollUntilDone(
        AnnTrainingRun $run,
        int $maxAttempts = 150,
        int $sleepSeconds = 2
    ): AnnTrainingRun {
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            sleep($sleepSeconds);

            try {
                $response = $this->client->getTrainingStatus($run->training_id);
            } catch (\Throwable $e) {
                // 404 / connection error — retry, tapi stop kalau attempt terakhir
                if ($attempt === $maxAttempts) {
                    $run->update([
                        'training_status' => 'failed',
                        'error_message' => 'Polling failed: ' . $e->getMessage(),
                        'training_completed_at' => now(),
                    ]);
                    return $run->fresh();
                }
                continue;
            }

            $data = $response['data'] ?? [];
            $status = $data['status'] ?? null;

            if ($status === 'completed') {
                $this->handleCompleted($run, $data);
                return $run->fresh();
            }

            if ($status === 'failed') {
                $results = $data['results'] ?? [];
                $perIngredientErrors = array_map(fn($r) => [
                    'ingredient_id' => $r['ingredient_id'] ?? null,
                    'ingredient_code' => $r['ingredient_code'] ?? null,
                    'error' => $r['error'] ?? 'unknown',
                ], array_values(array_filter($results, fn($r) => ($r['status'] ?? null) !== 'completed')));

                $run->update([
                    'training_status' => 'failed',
                    'error_message' => $data['error'] ?? 'Training failed di Python service.',
                    'test_metrics' => ['per_ingredient' => $perIngredientErrors],
                    'training_completed_at' => now(),
                ]);
                return $run->fresh();
            }

            // masih queued / running → update timestamp tapi lanjut loop
            $run->update([
                'training_status' => $status === 'running' ? 'running' : 'queued',
            ]);
        }

        // Max attempts habis tanpa terminal status
        $run->update([
            'training_status' => 'failed',
            'error_message' => "Timeout setelah {$maxAttempts} attempts (~" . ($maxAttempts * $sleepSeconds) . "s).",
            'training_completed_at' => now(),
        ]);

        return $run->fresh();
    }

    /**
     * Build payload training dari dataset version.
     */
    public function buildPayload(DatasetVersion $datasetVersion, array $config = []): array
    {
        $records = $datasetVersion->records()
            ->with('ingredient')
            ->get()
            ->groupBy('ingredient_id');

        if ($records->isEmpty()) {
            throw new \RuntimeException("Dataset {$datasetVersion->version} tidak memiliki records.");
        }

        $targetIngredients = [];

        foreach ($records as $ingredientId => $ingRecords) {
            $first = $ingRecords->first();
            $ingredient = $first->ingredient;

            if (! $ingredient) {
                continue;
            }

            $targetIngredients[] = [
                'ingredient_id' => $ingredient->id,
                'ingredient_code' => $ingredient->sku,
                'ingredient_name' => $ingredient->name,
                'records' => $ingRecords->map(fn($r) => [
                    'business_date' => $r->business_date->format('Y-m-d'),
                    'actual_demand' => (float) $r->actual_demand,
                    'lag_d1' => $r->lag_d1 !== null ? (float) $r->lag_d1 : null,
                    'lag_d2' => $r->lag_d2 !== null ? (float) $r->lag_d2 : null,
                    'lag_d3' => $r->lag_d3 !== null ? (float) $r->lag_d3 : null,
                    'lag_d7' => $r->lag_d7 !== null ? (float) $r->lag_d7 : null,
                    'rolling_mean_3d' => $r->rolling_mean_3d !== null ? (float) $r->rolling_mean_3d : null,
                    'rolling_mean_7d' => $r->rolling_mean_7d !== null ? (float) $r->rolling_mean_7d : null,
                    'temperature' => $r->temperature !== null ? (float) $r->temperature : null,
                    'rainfall' => $r->rainfall !== null ? (float) $r->rainfall : null,
                    'day_of_week' => (int) $r->day_of_week,
                    'is_weekend' => (bool) $r->is_weekend,
                ])->values()->toArray(),
            ];
        }

        if (empty($targetIngredients)) {
            throw new \RuntimeException("Tidak ada ingredient valid di dataset.");
        }

        return [
            'training_id' => 'training-' . now()->format('YmdHis') . '-' . substr(md5(uniqid('', true)), 0, 6),
            'dataset_version' => $datasetVersion->version,
            'target_ingredients' => $targetIngredients,
            'training_config' => array_merge([
                'epochs' => 100,
                'batch_size' => 16,
                'learning_rate' => 0.001,
                'early_stopping_patience' => 10,
            ], $config),
        ];
    }

    /**
     * Persist hasil training completed ke ann_models + ann_model_ingredients.
     */
    protected function handleCompleted(AnnTrainingRun $run, array $data): void
    {
        DB::transaction(function () use ($run, $data) {
            $results = $data['results'] ?? [];
            $completedResults = array_filter($results, fn($r) => ($r['status'] ?? null) === 'completed');

            if (empty($completedResults)) {
                $run->update([
                    'training_status' => 'failed',
                    'error_message' => 'Python returned completed tapi tidak ada ingredient sukses.',
                    'training_completed_at' => now(),
                ]);
                return;
            }

            $modelName = "ANN Model — {$run->datasetVersion?->version}";

            foreach ($completedResults as $result) {
                $model = AnnModel::create([
                    'model_name' => $modelName,
                    'model_version' => $result['model_version'],
                    'algorithm' => 'MLP',
                    'architecture' => [
                        'input_features' => count($result['feature_schema']['features'] ?? []),
                        'hidden_layers' => [16, 8],
                        'output' => 1,
                        'activation' => 'relu',
                        'output_activation' => 'linear',
                    ],
                    'feature_schema' => $result['feature_schema'] ?? [],
                    'target_definition' => [
                        'target' => 'actual_demand',
                        'description' => 'Daily ingredient demand',
                    ],
                    'artifact_path' => $result['artifact_path'] ?? null,
                    'scaler_path' => $result['scaler_path'] ?? null,
                    'status' => 'candidate',
                    'dataset_version_id' => $run->dataset_version_id,
                    'created_by' => $run->created_by,
                ]);

                AnnModelIngredient::create([
                    'ann_model_id' => $model->id,
                    'ingredient_id' => $result['ingredient_id'],
                    'output_index' => 0,
                    'evaluation_metrics' => [
                        'mae' => $result['metrics']['mae'] ?? null,
                        'rmse' => $result['metrics']['rmse'] ?? null,
                        'baseline_mae' => $result['baseline']['mae'] ?? null,
                        'baseline_rmse' => $result['baseline']['rmse'] ?? null,
                        'samples' => $result['samples'] ?? null,
                    ],
                ]);

                $run->update(['ann_model_id' => $model->id]);
            }

            $run->update([
                'training_status' => 'completed',
                'training_completed_at' => now(),
                'test_metrics' => array_map(fn($r) => [
                    'ingredient_id' => $r['ingredient_id'],
                    'mae' => $r['metrics']['mae'] ?? null,
                    'rmse' => $r['metrics']['rmse'] ?? null,
                    'baseline_mae' => $r['baseline']['mae'] ?? null,
                    'baseline_rmse' => $r['baseline']['rmse'] ?? null,
                ], array_values($completedResults)),
            ]);
        });
    }
}
