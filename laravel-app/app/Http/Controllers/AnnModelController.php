<?php

namespace App\Http\Controllers;

use App\Models\AnnModel;
use App\Models\AnnModelIngredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnnModelController extends Controller
{
    public function index(Request $request)
    {
        $models = AnnModel::with(['datasetVersion', 'createdBy'])
            ->withCount('ingredients')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->dataset_version_id, fn($q, $d) => $q->where('dataset_version_id', $d))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $datasets = \App\Models\DatasetVersion::orderByDesc('id')->get();

        return view('ann.models.index', compact('models', 'datasets'));
    }

    public function show(AnnModel $annModel)
    {
        $annModel->load([
            'datasetVersion',
            'createdBy',
            'ingredients.ingredient.unit',
        ]);

        // Model aktif lain untuk ingredient yang sama (untuk info kalau akan activate)
        $currentActive = [];
        foreach ($annModel->ingredients as $mi) {
            $active = AnnModelIngredient::where('ingredient_id', $mi->ingredient_id)
                ->whereHas('model', fn($q) => $q->where('status', 'active'))
                ->with('model')
                ->first();
            $currentActive[$mi->ingredient_id] = $active?->model;
        }

        return view('ann.models.show', compact('annModel', 'currentActive'));
    }

    public function activate(AnnModel $annModel)
    {
        if ($annModel->status === 'archived') {
            return back()->with('error', 'Model archived tidak dapat diaktifkan.');
        }

        if ($annModel->status === 'active') {
            return back()->with('error', 'Model sudah aktif.');
        }

        DB::transaction(function () use ($annModel) {
            // Archive model aktif lain untuk ingredient yang sama
            $ingredientIds = $annModel->ingredients()->pluck('ingredient_id');

            AnnModel::where('status', 'active')
                ->whereHas('ingredients', fn($q) => $q->whereIn('ingredient_id', $ingredientIds))
                ->get()
                ->each(function ($m) use ($ingredientIds) {
                    $overlap = $m->ingredients()->whereIn('ingredient_id', $ingredientIds)->exists();
                    if ($overlap) {
                        $m->update(['status' => 'archived']);
                    }
                });

            $annModel->update(['status' => 'active']);
        });

        return back()->with('success', "Model {$annModel->model_version} diaktifkan.");
    }

    public function archive(AnnModel $annModel)
    {
        if ($annModel->status === 'active') {
            return back()->with('error', 'Model aktif tidak dapat diarsipkan. Aktifkan model lain dulu.');
        }

        $annModel->update(['status' => 'archived']);

        return back()->with('success', "Model {$annModel->model_version} diarsipkan.");
    }
}
