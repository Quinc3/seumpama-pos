<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Promotion extends Model
{
    protected $fillable = [
        'name',
        'code',
        'discount_type',
        'discount_value',
        'start_at',
        'end_at',
        'minimum_purchase',
        'maximum_discount',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'minimum_purchase' => 'decimal:2',
            'maximum_discount' => 'decimal:2',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'promotion_products');
    }

    public function scopeActive($query)
    {
        $now = now();
        return $query->where('is_active', true)
            ->where('start_at', '<=', $now)
            ->where('end_at', '>=', $now);
    }

    public function isCurrentlyActive(): bool
    {
        $now = now();
        return $this->is_active
            && $this->start_at <= $now
            && $this->end_at >= $now;
    }

    /**
     * Hitung nominal diskon untuk subtotal tertentu.
     */
    public function calculateDiscount(float $subtotal): float
    {
        if ($this->minimum_purchase !== null && $subtotal < (float) $this->minimum_purchase) {
            return 0.0;
        }

        if ($this->discount_type === 'percentage') {
            $discount = $subtotal * ((float) $this->discount_value / 100);
            if ($this->maximum_discount !== null) {
                $discount = min($discount, (float) $this->maximum_discount);
            }
        } else {
            $discount = (float) $this->discount_value;
        }

        // Jangan melebihi subtotal
        return min($discount, $subtotal);
    }
}
