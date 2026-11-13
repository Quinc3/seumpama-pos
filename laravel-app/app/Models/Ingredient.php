<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\InventoryMovement;

class Ingredient extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sku',
        'name',
        'category',
        'unit_id',
        'ingredient_type',
        'current_stock',
        'minimum_stock',
        'safety_stock',
        'lead_time_days',
        'is_prediction_target',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'current_stock' => 'decimal:4',
            'minimum_stock' => 'decimal:4',
            'safety_stock' => 'decimal:4',
            'lead_time_days' => 'decimal:2',
            'is_prediction_target' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function stockStatus(): string
    {
        if ($this->current_stock <= 0) {
            return 'out';
        }
        if ($this->minimum_stock !== null && $this->current_stock <= $this->minimum_stock) {
            return 'critical';
        }
        if ($this->minimum_stock !== null && $this->current_stock <= $this->minimum_stock * 1.5) {
            return 'warning';
        }
        return 'normal';
    }

    public function movements()
    {
        return $this->hasMany(InventoryMovement::class)->latest('id');
    }
}
