<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOpnameItem extends Model
{
    protected $fillable = [
        'stock_opname_id',
        'ingredient_id',
        'system_quantity',
        'physical_quantity',
        'difference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'system_quantity' => 'decimal:4',
            'physical_quantity' => 'decimal:4',
            'difference' => 'decimal:4',
        ];
    }

    public function stockOpname(): BelongsTo
    {
        return $this->belongsTo(StockOpname::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
