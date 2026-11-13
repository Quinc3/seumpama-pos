<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'sku',
        'name',
        'description',
        'selling_price',
        'image_path',
        'is_available',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'selling_price' => 'decimal:2',
            'is_available' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function recipe()
    {
        return $this->hasOne(Recipe::class)
            ->where('is_active', true)
            ->latest('version');
    }

    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }

    public function hasActiveRecipe(): bool
    {
        return $this->recipe()->exists();
    }
}
