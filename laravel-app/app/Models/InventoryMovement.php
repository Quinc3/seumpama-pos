<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'ingredient_id',
        'business_session_id',
        'movement_type',
        'quantity',
        'stock_before',
        'stock_after',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'stock_before' => 'decimal:4',
            'stock_after' => 'decimal:4',
            'created_at' => 'datetime',
        ];
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isIncoming(): bool
    {
        return in_array($this->movement_type, ['purchase_in', 'adjustment_in', 'return_in']);
    }

    public function getMovementLabelAttribute(): string
    {
        return match ($this->movement_type) {
            'purchase_in' => 'Stock In (Pembelian)',
            'sale_out' => 'Sale (Transaksi)',
            'adjustment_in' => 'Adjustment (Tambah)',
            'adjustment_out' => 'Adjustment (Kurang)',
            'waste' => 'Waste (Rusak/Buang)',
            'return_in' => 'Retur Masuk',
            'return_out' => 'Retur Keluar',
            default => $this->movement_type,
        };
    }
}
