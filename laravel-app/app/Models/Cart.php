<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = ['user_id', 'business_session_id', 'cart_name', 'status'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function businessSession(): BelongsTo
    {
        return $this->belongsTo(BusinessSession::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function getItemCountAttribute(): int
    {
        return $this->items->count();
    }

    public function getTotalQuantityAttribute(): float
    {
        return (float) $this->items->sum(fn($i) => (float) $i->quantity);
    }

    public function getSubtotalAttribute(): float
    {
        return (float) $this->items->sum(
            fn($i) => (float) $i->quantity * (float) ($i->product?->selling_price ?? 0)
        );
    }

    public function toCartArray(): array
    {
        return [
            'id' => $this->id,
            'items' => $this->items->map(fn($i) => [
                'id' => $i->id,
                'product_id' => $i->product_id,
                'name' => $i->product?->name ?? '—',
                'sku' => $i->product?->sku ?? '',
                'quantity' => (float) $i->quantity,
                'unit_price' => (float) ($i->product?->selling_price ?? 0),
                'subtotal' => (float) $i->quantity * (float) ($i->product?->selling_price ?? 0),
                'notes' => $i->notes,
            ])->values()->toArray(),
            'item_count' => $this->total_quantity,
            'subtotal' => $this->subtotal,
        ];
    }
}
