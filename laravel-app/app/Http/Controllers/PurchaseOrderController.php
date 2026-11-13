<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = PurchaseOrder::with(['supplier', 'createdBy'])
            ->withCount('items')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->supplier_id, fn($q, $id) => $q->where('supplier_id', $id))
            ->when($request->from, fn($q, $d) => $q->whereDate('purchase_date', '>=', $d))
            ->when($request->to, fn($q, $d) => $q->whereDate('purchase_date', '<=', $d))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $suppliers = Supplier::orderBy('name')->get();

        return view('purchase-orders.index', compact('orders', 'suppliers'));
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $ingredients = Ingredient::where('is_active', true)->with('unit')->orderBy('name')->get();

        return view('purchase-orders.create', compact('suppliers', 'ingredients'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'purchase_date' => ['required', 'date'],
            'expected_date' => ['nullable', 'date', 'after_or_equal:purchase_date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.ingredient_id' => ['required', 'exists:ingredients,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $ids = collect($data['items'])->pluck('ingredient_id');
        if ($ids->count() !== $ids->unique()->count()) {
            throw ValidationException::withMessages(['items' => 'Tidak boleh ada bahan duplikat.']);
        }

        $po = DB::transaction(function () use ($data) {
            $po = PurchaseOrder::create([
                'purchase_number' => PurchaseOrder::generatePurchaseNumber(),
                'supplier_id' => $data['supplier_id'],
                'status' => 'draft',
                'purchase_date' => $data['purchase_date'],
                'expected_date' => $data['expected_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
                'total_amount' => 0,
            ]);

            $total = 0;
            foreach ($data['items'] as $row) {
                $ing = Ingredient::findOrFail($row['ingredient_id']);
                $subtotal = (float) $row['quantity'] * (float) $row['unit_price'];
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'ingredient_id' => $ing->id,
                    'quantity' => $row['quantity'],
                    'received_quantity' => 0,
                    'unit_id' => $ing->unit_id,
                    'unit_price' => $row['unit_price'],
                    'subtotal' => $subtotal,
                ]);
                $total += $subtotal;
            }

            $po->update(['total_amount' => $total]);

            return $po;
        });

        return redirect()
            ->route('purchase-orders.show', $po)
            ->with('success', "Purchase Order {$po->purchase_number} dibuat sebagai draft.");
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'items.ingredient.unit',
            'items.unit',
            'supplier',
            'createdBy',
            'approvedBy',
            'receipts.receivedBy',
        ]);

        return view('purchase-orders.show', ['po' => $purchaseOrder]);
    }

    public function approve(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') {
            return back()->with('error', 'Hanya PO berstatus draft yang bisa di-approve.');
        }

        if ($purchaseOrder->items->isEmpty()) {
            return back()->with('error', 'PO tidak memiliki item.');
        }

        $purchaseOrder->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()
            ->route('purchase-orders.show', $purchaseOrder)
            ->with('success', "PO {$purchaseOrder->purchase_number} telah di-approve. Siap menerima barang.");
    }

    public function cancel(PurchaseOrder $purchaseOrder)
    {
        if (! $purchaseOrder->canBeCancelled()) {
            return back()->with('error', 'PO tidak dapat dibatalkan (sudah ada penerimaan barang atau status final).');
        }

        $purchaseOrder->update(['status' => 'cancelled']);

        return redirect()
            ->route('purchase-orders.index')
            ->with('success', "PO {$purchaseOrder->purchase_number} telah dibatalkan.");
    }
}
