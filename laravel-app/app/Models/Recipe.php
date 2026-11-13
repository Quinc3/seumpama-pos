<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recipe extends Model
{
    protected $fillable = [
        'product_id',
        'version',
        'name',
        'effective_from',
        'effective_until',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'effective_from' => 'datetime',
            'effective_until' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(RecipeItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function isCurrentlyActive(): bool
    {
        return $this->is_active
            && $this->effective_from->lte(now())
            && ($this->effective_until === null || $this->effective_until->gte(now()));
    }

    public static function nextVersion(int $productId): int
    {
        return (int) static::where('product_id', $productId)->max('version') + 1;
    }
}
