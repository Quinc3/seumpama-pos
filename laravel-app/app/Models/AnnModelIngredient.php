<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnModelIngredient extends Model
{
    protected $fillable = ['ann_model_id', 'ingredient_id', 'output_index', 'evaluation_metrics'];

    protected function casts(): array
    {
        return ['evaluation_metrics' => 'array'];
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(AnnModel::class, 'ann_model_id');
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
