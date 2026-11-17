<?php

namespace App\Services\Forecast;

use App\Models\AnnModel;
use App\Models\AnnPrediction;
use App\Models\Ingredient;
use App\Models\WeatherDailySummary;
use App\Services\AnnServiceClient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PredictionService
{
    public function __construct(
        protected AnnServiceClient $client
    ) {}

    /**
     * Predict all target ingredients untuk target_date.
     * Return summary + per-ingredient results.
     */
    public function predictAll(?Carbon $targetDate = null): array
    {
        $targetDate = $targetDate ?: now()->addDay()->startOfDay();

        $ingredients = Ingredient::where('is_active', true)
            ->where('is_prediction_target', true)
            ->with('unit')
            ->orderBy('name')
            ->get();

        $success = 0;
        $failed = 0;
        $results = [];

        foreach ($ingredients as $ingredient) {
            $model = $this->findModelForIngredient($ingredient);
            if (! $model) {
                $failed++;
                $results[] = [
                    'ingredient' => $ingredient,
                    'status' => 'no_model',
                    'message' => 'Tidak ada model aktif/candidate untuk ingredient ini.',
                ];
                continue;
            }

            $features = $this->buildFeatures($ingredient, $targetDate);
            if ($features === null) {
                $failed++;
                $results[] = [
                    'ingredient' => $ingredient,
                    'status' => 'no_data',
                    'message' => 'Tidak ada historical data untuk membangun features.',
                ];
                continue;
            }

            try {
                $response = $this->client->predict([
                    'predictions' => [[
                        'ingredient_id' => $ingredient->id,
                        'ingredient_code' => $ingredient->sku,
                        'model_version' => $model->model_version,
                        'prediction_date' => $targetDate->format('Y-m-d'),
                        'features' => $features,
                    ]],
                ]);

                $predictionData = $response['data']['predictions'][0] ?? null;

                if (! $predictionData || ($predictionData['status'] ?? null) !== 'valid') {
                    $failed++;
                    $results[] = [
                        'ingredient' => $ingredient,
                        'status' => 'api_failed',
                        'message' => $predictionData['error'] ?? 'Invalid response dari Python.',
                    ];
                    continue;
                }

                $predictedValue = (float) $predictionData['predicted_demand'];

                AnnPrediction::updateOrCreate(
                    [
                        'ann_model_id' => $model->id,
                        'ingredient_id' => $ingredient->id,
                        'prediction_date' => $targetDate->toDateString(),
                    ],
                    [
                        'dataset_version_id' => $model->dataset_version_id,
                        'prediction_generated_at' => now(),
                        'predicted_demand' => $predictedValue,
                        'actual_demand' => null,
                        'mae' => null,
                        'rmse' => null,
                        'status' => 'generated',
                    ]
                );

                $success++;
                $results[] = [
                    'ingredient' => $ingredient,
                    'status' => 'success',
                    'predicted' => $predictedValue,
                    'model_version' => $model->model_version,
                ];
            } catch (\Throwable $e) {
                $failed++;
                $results[] = [
                    'ingredient' => $ingredient,
                    'status' => 'exception',
                    'message' => $e->getMessage(),
                ];
            }
        }

        return [
            'target_date' => $targetDate,
            'success' => $success,
            'failed' => $failed,
            'total' => $ingredients->count(),
            'results' => $results,
        ];
    }

    /**
     * Find active model untuk ingredient; fallback ke candidate terbaru.
     */
    public function findModelForIngredient(Ingredient $ingredient): ?AnnModel
    {
        $active = AnnModel::where('status', 'active')
            ->whereHas('ingredients', fn($q) => $q->where('ingredient_id', $ingredient->id))
            ->first();

        if ($active) {
            return $active;
        }

        return AnnModel::where('status', 'candidate')
            ->whereHas('ingredients', fn($q) => $q->where('ingredient_id', $ingredient->id))
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Build features untuk target_date berdasarkan historical actual demand.
     * Lags: pakai actual per business_date sebelum target_date.
     * Weather: pakai last known summary (bukan real forecast — catatan untuk Phase 6/8 iterasi).
     */
    public function buildFeatures(Ingredient $ingredient, Carbon $targetDate): ?array
    {
        $usageByDate = DB::table('sale_ingredient_usages as siu')
            ->join('order_items as oi', 'oi.id', '=', 'siu.order_item_id')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('business_sessions as bs', 'bs.id', '=', 'o.business_session_id')
            ->where('o.status', 'paid')
            ->where('siu.ingredient_id', $ingredient->id)
            ->where('bs.business_date', '<', $targetDate->toDateString())
            ->select('bs.business_date', DB::raw('SUM(siu.total_quantity) as qty'))
            ->groupBy('bs.business_date')
            ->pluck('qty', 'business_date')
            ->map(fn($v) => (float) $v)
            ->all();

        // Kalau tidak ada data sama sekali, tetap bisa build dengan 0-fill
        // (model trained dengan lag=null di dropna; di inference kita pakai 0 untuk keep shape valid)
        $get = fn(Carbon $d) => (float) ($usageByDate[$d->toDateString()] ?? 0.0);

        $lag1 = $get($targetDate->copy()->subDay());
        $lag2 = $get($targetDate->copy()->subDays(2));
        $lag3 = $get($targetDate->copy()->subDays(3));
        $lag7 = $get($targetDate->copy()->subDays(7));

        $rolling3 = ($lag1 + $lag2 + $lag3) / 3;

        $rolling7 = 0.0;
        for ($d = 1; $d <= 7; $d++) {
            $rolling7 += $get($targetDate->copy()->subDays($d));
        }
        $rolling7 /= 7;

        $weather = WeatherDailySummary::orderByDesc('business_date')->first();

        return [
            'lag_d1' => round($lag1, 4),
            'lag_d2' => round($lag2, 4),
            'lag_d3' => round($lag3, 4),
            'lag_d7' => round($lag7, 4),
            'rolling_mean_3d' => round($rolling3, 4),
            'rolling_mean_7d' => round($rolling7, 4),
            'temperature' => (float) ($weather?->average_temperature ?? 28.0),
            'rainfall' => (float) ($weather?->total_rainfall ?? 0.0),
            'day_of_week' => (int) $targetDate->dayOfWeek,
            'is_weekend' => in_array($targetDate->dayOfWeek, [5, 6]),
        ];
    }
}
