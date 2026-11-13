<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleIngredientUsage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_item_id',
        'ingredient_id',
        'recipe_id',
        'quantity_per_unit',
        'sold_quantity',
        'total_quantity',
        'unit_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity_per_unit' => 'decimal:4',
            'sold_quantity' => 'decimal:4',
            'total_quantity' => 'decimal:4',
            'created_at' => 'datetime',
        ];
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
