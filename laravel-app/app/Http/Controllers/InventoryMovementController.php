<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\InventoryMovement;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InventoryMovementController extends Controller
{
    public function index(Request $request)
    {
        $movements = InventoryMovement::with(['ingredient.unit', 'createdBy'])
            ->when($request->ingredient_id, fn($q, $id) => $q->where('ingredient_id', $id))
            ->when($request->movement_type, fn($q, $type) => $q->where('movement_type', $type))
            ->when($request->from, fn($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->to, fn($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $ingredients = Ingredient::orderBy('name')->get();

        $allTypes = array_merge(
            StockService::stockInTypes(),
            StockService::stockOutTypes(),
            ['sale_out' => 'Sale (Transaksi)']
        );

        return view('inventory.movements.index', compact('movements', 'ingredients', 'allTypes'));
    }

    public function createIn()
    {
        $ingredients = Ingredient::where('is_active', true)->with('unit')->orderBy('name')->get();
        $types = StockService::stockInTypes();

        return view('inventory.movements.create', [
            'ingredients' => $ingredients,
            'types' => $types,
            'mode' => 'in',
        ]);
    }

    public function createOut()
    {
        $ingredients = Ingredient::where('is_active', true)->with('unit')->orderBy('name')->get();
        $types = StockService::stockOutTypes();

        return view('inventory.movements.create', [
            'ingredients' => $ingredients,
            'types' => $types,
            'mode' => 'out',
        ]);
    }

    public function store(Request $request, StockService $stockService)
    {
        $mode = $request->input('mode', 'in');
        $allowedTypes = $mode === 'out'
            ? array_keys(StockService::stockOutTypes())
            : array_keys(StockService::stockInTypes());

        $data = $request->validate([
            'ingredient_id' => ['required', 'exists:ingredients,id'],
            'movement_type' => ['required', Rule::in($allowedTypes)],
            'quantity' => ['required', 'numeric', 'min:0.0001'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $ingredient = Ingredient::findOrFail($data['ingredient_id']);

        $movement = $stockService->record(
            $ingredient,
            $data['movement_type'],
            (float) $data['quantity'],
            ['notes' => $data['notes'] ?? null]
        );

        return redirect()
            ->route('inventory.movements.index')
            ->with('success', "Stock movement tercatat: {$movement->movement_label} sebesar {$movement->quantity} {$ingredient->unit?->symbol} untuk {$ingredient->name}.");
    }
}
