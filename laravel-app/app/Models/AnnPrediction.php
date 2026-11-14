<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnPrediction extends Model
{
    protected $fillable = [
        'ann_model_id',
        'dataset_version_id',
        'ingredient_id',
        'prediction_date',
        'prediction_generated_at',
        'predicted_demand',
        'actual_demand',
        'mae',
        'rmse',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'prediction_date' => 'date',
            'prediction_generated_at' => 'datetime',
            'predicted_demand' => 'decimal:4',
            'actual_demand' => 'decimal:4',
            'mae' => 'decimal:4',
            'rmse' => 'decimal:4',
        ];
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(AnnModel::class, 'ann_model_id');
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function datasetVersion(): BelongsTo
    {
        return $this->belongsTo(DatasetVersion::class);
    }
}
