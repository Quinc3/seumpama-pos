<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IngredientController extends Controller
{
    public function index(Request $request)
    {
        $query = Ingredient::with('unit')
            ->when($request->search, function ($q, $search) {
                $q->where(
                    fn($w) => $w
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                );
            })
            ->when($request->type, fn($q, $type) => $q->where('ingredient_type', $type))
            ->when($request->has('active'), fn($q) => $q->where('is_active', $request->boolean('active')));

        $ingredients = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('ingredients.index', compact('ingredients'));
    }

    public function create()
    {
        $units = Unit::where('is_active', true)->orderBy('name')->get();

        return view('ingredients.create', compact('units'));
    }

    public function store(Request $request)
    {
        $data = $this->validateIngredient($request);
        $ingredient = Ingredient::create($data);

        return redirect()
            ->route('ingredients.index')
            ->with('success', "Bahan \"{$ingredient->name}\" berhasil ditambahkan.");
    }

    public function show(Ingredient $ingredient)
    {
        $ingredient->load('unit');

        return view('ingredients.show', compact('ingredient'));
    }

    public function edit(Ingredient $ingredient)
    {
        $units = Unit::where('is_active', true)->orWhere('id', $ingredient->unit_id)
            ->orderBy('name')->get();

        return view('ingredients.edit', compact('ingredient', 'units'));
    }

    public function update(Request $request, Ingredient $ingredient)
    {
        $data = $this->validateIngredient($request, $ingredient);
        $ingredient->update($data);

        return redirect()
            ->route('ingredients.index')
            ->with('success', "Bahan \"{$ingredient->name}\" berhasil diperbarui.");
    }

    public function destroy(Ingredient $ingredient)
    {
        $name = $ingredient->name;
        $ingredient->delete();

        return redirect()
            ->route('ingredients.index')
            ->with('success', "Bahan \"{$name}\" berhasil dihapus (soft delete).");
    }

    protected function validateIngredient(Request $request, ?Ingredient $ingredient = null): array
    {
        $isEdit = $ingredient !== null;

        $rules = [
            'sku' => [
                'required',
                'string',
                'max:100',
                Rule::unique('ingredients', 'sku')->ignore($ingredient?->id),
            ],
            'name' => ['required', 'string', 'max:180'],
            'category' => ['nullable', 'string', 'max:100'],
            'unit_id' => ['required', 'exists:units,id'],
            'ingredient_type' => ['required', Rule::in(['core', 'non_core'])],
            'minimum_stock' => ['nullable', 'numeric', 'min:0'],
            'safety_stock' => ['nullable', 'numeric', 'min:0'],
            'lead_time_days' => ['nullable', 'numeric', 'min:0'],
            'is_prediction_target' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];

        if (! $isEdit) {
            $rules['current_stock'] = ['nullable', 'numeric', 'min:0'];
        }

        $data = $request->validate($rules);

        if (! $isEdit) {
            $data['current_stock'] = $data['current_stock'] ?? 0;
        }

        // If type is non_core, prediction target must be false (per business logic)
        if (($data['ingredient_type'] ?? null) === 'non_core') {
            $data['is_prediction_target'] = false;
        } else {
            $data['is_prediction_target'] = $request->boolean('is_prediction_target');
        }

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
