<?php

namespace App\Http\Controllers;

use App\Models\DatasetRecord;
use App\Models\DatasetVersion;
use App\Services\Dataset\DatasetGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DatasetController extends Controller
{
    public function index()
    {
        $versions = DatasetVersion::with('createdBy')
            ->orderByDesc('id')
            ->paginate(20);

        return view('datasets.index', compact('versions'));
    }

    public function create()
    {
        return view('datasets.create');
    }

    public function store(Request $request, DatasetGeneratorService $service)
    {
        $data = $request->validate([
            'dataset_name' => ['required', 'string', 'max:150'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        try {
            $version = $service->generate(
                $data['dataset_name'],
                Carbon::parse($data['start_date']),
                Carbon::parse($data['end_date'])
            );

            return redirect()
                ->route('datasets.show', $version)
                ->with('success', "Dataset {$version->version} berhasil dibuat ({$version->total_records} records).");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membuat dataset: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Request $request, DatasetVersion $dataset)
    {
        $ingredientId = $request->input('ingredient_id');

        $records = DatasetRecord::with('ingredient')
            ->where('dataset_version_id', $dataset->id)
            ->when($ingredientId, fn($q, $id) => $q->where('ingredient_id', $id))
            ->orderBy('business_date')
            ->orderBy('ingredient_id')
            ->paginate(50)
            ->withQueryString();

        $ingredients = $dataset->records()
            ->join('ingredients', 'ingredients.id', '=', 'dataset_records.ingredient_id')
            ->select('ingredients.id', 'ingredients.name', 'ingredients.sku')
            ->distinct()
            ->orderBy('ingredients.name')
            ->get();

        return view('datasets.show', compact('dataset', 'records', 'ingredients'));
    }

    public function archive(DatasetVersion $dataset)
    {
        if ($dataset->status === 'archived') {
            return back()->with('error', 'Dataset sudah diarsipkan.');
        }

        $dataset->update(['status' => 'archived']);

        return back()->with('success', 'Dataset berhasil diarsipkan.');
    }

    public function destroy(DatasetVersion $dataset)
    {
        if ($dataset->status !== 'draft') {
            return back()->with('error', 'Hanya dataset berstatus draft yang dapat dihapus.');
        }

        $dataset->delete();

        return redirect()->route('datasets.index')
            ->with('success', 'Dataset berhasil dihapus.');
    }
}
