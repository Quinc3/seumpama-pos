<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockService
{
    protected const INCOMING_TYPES = ['purchase_in', 'adjustment_in', 'return_in'];
    protected const OUTGOING_TYPES = ['sale_out', 'adjustment_out', 'waste', 'return_out'];

    public function record(
        Ingredient $ingredient,
        string $movementType,
        float $quantity,
        array $options = []
    ): InventoryMovement {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Quantity harus lebih besar dari 0.');
        }

        $allTypes = array_merge(self::INCOMING_TYPES, self::OUTGOING_TYPES);
        if (! in_array($movementType, $allTypes)) {
            throw new InvalidArgumentException("Movement type tidak valid: {$movementType}");
        }

        return DB::transaction(function () use ($ingredient, $movementType, $quantity, $options) {
            $locked = Ingredient::whereKey($ingredient->id)->lockForUpdate()->first();
            if (! $locked) {
                throw new InvalidArgumentException('Ingredient tidak ditemukan.');
            }

            $stockBefore = (float) $locked->current_stock;
            $incoming = in_array($movementType, self::INCOMING_TYPES);
            $stockAfter = $incoming ? $stockBefore + $quantity : $stockBefore - $quantity;

            $locked->update(['current_stock' => $stockAfter]);

            return InventoryMovement::create([
                'ingredient_id' => $locked->id,
                'business_session_id' => $options['business_session_id'] ?? null,
                'movement_type' => $movementType,
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reference_type' => $options['reference_type'] ?? null,
                'reference_id' => $options['reference_id'] ?? null,
                'notes' => $options['notes'] ?? null,
                'created_by' => auth()->id() ?? throw new InvalidArgumentException('User tidak terautentikasi.'),
            ]);
        });
    }

    public static function stockInTypes(): array
    {
        return [
            'purchase_in' => 'Pembelian (Purchase In)',
            'adjustment_in' => 'Adjustment Tambah',
            'return_in' => 'Retur Masuk',
        ];
    }

    public static function stockOutTypes(): array
    {
        return [
            'adjustment_out' => 'Adjustment Kurang',
            'waste' => 'Waste (Rusak/Buang)',
            'return_out' => 'Retur Keluar',
        ];
    }
}
