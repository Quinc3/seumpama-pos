<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'purchase_number',
        'supplier_id',
        'status',
        'purchase_date',
        'expected_date',
        'total_amount',
        'created_by',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'expected_date' => 'date',
            'approved_at' => 'datetime',
            'total_amount' => 'decimal:2',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function canBeEdited(): bool
    {
        return $this->status === 'draft';
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['draft', 'approved']) && ! $this->receipts()->exists();
    }

    public function canReceive(): bool
    {
        return in_array($this->status, ['approved', 'partially_received']);
    }

    public function hasOutstanding(): bool
    {
        return $this->items()->whereColumn('received_quantity', '<', 'quantity')->exists();
    }

    public static function generatePurchaseNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = "PO-{$date}-";
        $last = static::where('purchase_number', 'like', "{$prefix}%")
            ->orderByDesc('purchase_number')->value('purchase_number');
        $seq = $last ? ((int) substr($last, -3)) + 1 : 1;

        return $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }
}
