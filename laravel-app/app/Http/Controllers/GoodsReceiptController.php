<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseOrder;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GoodsReceiptController extends Controller
{
    public function create(PurchaseOrder $purchaseOrder)
    {
        if (! $purchaseOrder->canReceive()) {
            return redirect()
                ->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'PO ini tidak dalam status yang bisa menerima barang.');
        }

        $purchaseOrder->load(['items.ingredient.unit', 'items.unit', 'supplier']);

        $receivableItems = $purchaseOrder->items
            ->filter(fn($it) => ! $it->isFullyReceived());

        if ($receivableItems->isEmpty()) {
            return redirect()
                ->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'Semua item PO sudah diterima penuh.');
        }

        return view('goods-receipts.create', [
            'po' => $purchaseOrder,
            'receivableItems' => $receivableItems,
        ]);
    }

    public function store(Request $request, PurchaseOrder $purchaseOrder, StockService $stockService)
    {
        if (! $purchaseOrder->canReceive()) {
            return back()->with('error', 'PO tidak dalam status bisa menerima barang.');
        }

        $data = $request->validate([
            'received_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.purchase_order_item_id' => ['required', 'exists:purchase_order_items,id'],
            'items.*.received_quantity' => ['required', 'numeric', 'min:0'],
        ]);

        $poItems = $purchaseOrder->items()->get()->keyBy('id');

        // Validasi: qty > 0, tidak melebihi outstanding
        $hasAny = false;
        foreach ($data['items'] as $row) {
            $poItem = $poItems->get($row['purchase_order_item_id']);
            if (! $poItem) {
                throw ValidationException::withMessages(['items' => 'Item PO tidak valid.']);
            }
            $qty = (float) $row['received_quantity'];
            if ($qty < 0) {
                throw ValidationException::withMessages(['items' => 'Quantity tidak boleh negatif.']);
            }
            if ($qty > (float) $poItem->outstanding_quantity) {
                throw ValidationException::withMessages([
                    'items' => "Qty diterima untuk {$poItem->ingredient->name} melebihi outstanding (max: {$poItem->outstanding_quantity}).",
                ]);
            }
            if ($qty > 0) {
                $hasAny = true;
            }
        }

        if (! $hasAny) {
            throw ValidationException::withMessages(['items' => 'Minimal 1 item harus diterima (qty > 0).']);
        }

        $gr = DB::transaction(function () use ($data, $purchaseOrder, $poItems, $stockService) {
            $gr = GoodsReceipt::create([
                'receipt_number' => GoodsReceipt::generateReceiptNumber(),
                'purchase_order_id' => $purchaseOrder->id,
                'received_by' => auth()->id(),
                'received_at' => $data['received_at'],
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $row) {
                $qty = (float) $row['received_quantity'];
                if ($qty <= 0) {
                    continue;
                }

                $poItem = $poItems->get($row['purchase_order_item_id']);

                GoodsReceiptItem::create([
                    'goods_receipt_id' => $gr->id,
                    'purchase_order_item_id' => $poItem->id,
                    'ingredient_id' => $poItem->ingredient_id,
                    'received_quantity' => $qty,
                    'unit_id' => $poItem->unit_id,
                ]);

                // Update cache received_quantity di PO item
                $poItem->increment('received_quantity', $qty);

                // Catat stock movement (purchase_in)
                $stockService->record(
                    $poItem->ingredient,
                    'purchase_in',
                    $qty,
                    [
                        'notes' => "Penerimaan barang {$gr->receipt_number} (PO {$purchaseOrder->purchase_number})",
                        'reference_type' => 'goods_receipt',
                        'reference_id' => $gr->id,
                    ]
                );
            }

            // Update PO status
            $purchaseOrder->refresh();
            $allReceived = $purchaseOrder->items()->whereColumn('received_quantity', '<', 'quantity')->count() === 0;
            $purchaseOrder->update([
                'status' => $allReceived ? 'received' : 'partially_received',
            ]);

            return $gr;
        });

        return redirect()
            ->route('goods-receipts.show', $gr)
            ->with('success', "Penerimaan barang {$gr->receipt_number} berhasil dicatat.");
    }

    public function show(GoodsReceipt $goodsReceipt)
    {
        $goodsReceipt->load([
            'items.ingredient.unit',
            'items.unit',
            'purchaseOrder.supplier',
            'receivedBy',
        ]);

        return view('goods-receipts.show', ['gr' => $goodsReceipt]);
    }
}
