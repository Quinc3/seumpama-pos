<?php

namespace App\Http\Controllers;

use App\Models\AnnTrainingRun;
use App\Models\DatasetVersion;
use App\Services\AnnTrainingService;
use Illuminate\Http\Request;

class AnnTrainingController extends Controller
{
    public function __construct(protected AnnTrainingService $service) {}

    public function index(Request $request)
    {
        $runs = AnnTrainingRun::with(['datasetVersion', 'model'])
            ->when($request->status, fn($q, $s) => $q->where('training_status', $s))
            ->when($request->dataset_version_id, fn($q, $d) => $q->where('dataset_version_id', $d))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $datasets = DatasetVersion::orderByDesc('id')->get();

        return view('ann.training.index', compact('runs', 'datasets'));
    }

    public function show(AnnTrainingRun $annTrainingRun)
    {
        $annTrainingRun->load(['datasetVersion', 'model']);

        $models = collect();
        if ($annTrainingRun->training_status === 'completed' && $annTrainingRun->dataset_version_id) {
            $models = \App\Models\AnnModel::where('dataset_version_id', $annTrainingRun->dataset_version_id)
                ->with('ingredients.ingredient')
                ->orderBy('id')
                ->get();
        }

        return view('ann.training.show', [
            'run' => $annTrainingRun,
            'models' => $models,
        ]);
    }

    public function train(Request $request, DatasetVersion $dataset)
    {
        if (! in_array($dataset->status, ['validated', 'approved'])) {
            return back()->with('error', 'Dataset harus berstatus validated atau approved.');
        }

        // Cegah concurrent training untuk dataset yang sama
        $existing = AnnTrainingRun::where('dataset_version_id', $dataset->id)
            ->whereIn('training_status', ['queued', 'running'])
            ->exists();
        if ($existing) {
            return back()->with('error', 'Masih ada training berjalan untuk dataset ini. Tunggu sampai selesai.');
        }

        set_time_limit(300);   // 5 menit (PHP default 30s)
        session_write_close(); // Bebaskan session lock agar tab lain tidak freeze

        $run = null;

        try {
            $run = $this->service->submitTraining($dataset);
            $run = $this->service->pollUntilDone($run, 150, 2);
        } catch (\Throwable $e) {
            $message = $run
                ? "Training {$run->training_id} gagal: " . $e->getMessage()
                : "Submit training gagal: " . $e->getMessage();

            return redirect()
                ->route('ann.training.index')
                ->with('error', $message);
        }

        if ($run->training_status === 'completed') {
            $modelCount = \App\Models\AnnModel::where('dataset_version_id', $dataset->id)->count();
            return redirect()
                ->route('ann.training.show', $run)
                ->with('success', "Training selesai. {$modelCount} model dibuat.");
        }

        return redirect()
            ->route('ann.training.show', $run)
            ->with('error', 'Training gagal: ' . ($run->error_message ?? 'unknown error'));
    }

    public function refreshStatus(AnnTrainingRun $annTrainingRun)
    {
        $result = $this->service->refreshFromPython($annTrainingRun);

        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }
}
