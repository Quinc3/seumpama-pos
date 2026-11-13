<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockOpnameController extends Controller
{
    public function index(Request $request)
    {
        $opnames = StockOpname::with(['createdBy', 'approvedBy'])
            ->withCount('items')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->from, fn($q, $d) => $q->whereDate('opname_date', '>=', $d))
            ->when($request->to, fn($q, $d) => $q->whereDate('opname_date', '<=', $d))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('inventory.opnames.index', compact('opnames'));
    }

    public function create()
    {
        $ingredients = Ingredient::where('is_active', true)->with('unit')->orderBy('name')->get();

        return view('inventory.opnames.create', compact('ingredients'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'opname_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.ingredient_id' => ['required', 'exists:ingredients,id'],
            'items.*.physical_quantity' => ['required', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
        ]);

        // Validasi duplikat ingredient
        $ids = collect($data['items'])->pluck('ingredient_id');
        if ($ids->count() !== $ids->unique()->count()) {
            throw ValidationException::withMessages(['items' => 'Tidak boleh ada bahan yang sama dalam satu opname.']);
        }

        $opname = DB::transaction(function () use ($data) {
            $opname = StockOpname::create([
                'reference_number' => StockOpname::generateReferenceNumber(),
                'status' => 'draft',
                'opname_date' => $data['opname_date'],
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($data['items'] as $item) {
                $ing = Ingredient::findOrFail($item['ingredient_id']);
                $systemQty = (float) $ing->current_stock;
                $physicalQty = (float) $item['physical_quantity'];
                $diff = $physicalQty - $systemQty;

                StockOpnameItem::create([
                    'stock_opname_id' => $opname->id,
                    'ingredient_id' => $ing->id,
                    'system_quantity' => $systemQty,
                    'physical_quantity' => $physicalQty,
                    'difference' => $diff,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return $opname;
        });

        return redirect()
            ->route('stock-opnames.show', $opname)
            ->with('success', "Stock opname {$opname->reference_number} dibuat sebagai draft. Review dan approve untuk menerapkan adjustment.");
    }

    public function show(StockOpname $stockOpname)
    {
        $stockOpname->load(['items.ingredient.unit', 'createdBy', 'approvedBy']);

        return view('inventory.opnames.show', ['opname' => $stockOpname]);
    }

    public function approve(StockOpname $stockOpname, StockService $stockService)
    {
        if (! $stockOpname->isDraft()) {
            return back()->with('error', 'Hanya opname berstatus draft yang bisa di-approve.');
        }

        if ($stockOpname->items->isEmpty()) {
            return back()->with('error', 'Opname tidak memiliki item.');
        }

        DB::transaction(function () use ($stockOpname, $stockService) {
            foreach ($stockOpname->items as $item) {
                $diff = (float) $item->difference;
                if ($diff == 0) {
                    continue;
                }

                $movementType = $diff > 0 ? 'adjustment_in' : 'adjustment_out';
                $quantity = abs($diff);

                $stockService->record(
                    $item->ingredient,
                    $movementType,
                    $quantity,
                    [
                        'notes' => "Stock opname {$stockOpname->reference_number}",
                        'reference_type' => 'stock_opname',
                        'reference_id' => $stockOpname->id,
                    ]
                );
            }

            $stockOpname->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        });

        return redirect()
            ->route('stock-opnames.show', $stockOpname)
            ->with('success', "Stock opname {$stockOpname->reference_number} telah di-approve. Adjustment movement telah dibuat.");
    }

    public function cancel(StockOpname $stockOpname)
    {
        if (! $stockOpname->isDraft()) {
            return back()->with('error', 'Hanya opname berstatus draft yang bisa dibatalkan.');
        }

        $stockOpname->update(['status' => 'cancelled']);

        return redirect()
            ->route('stock-opnames.index')
            ->with('success', "Stock opname {$stockOpname->reference_number} telah dibatalkan.");
    }
}
