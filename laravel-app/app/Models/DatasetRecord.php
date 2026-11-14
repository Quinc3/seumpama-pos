<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatasetRecord extends Model
{
    protected $fillable = [
        'dataset_version_id',
        'ingredient_id',
        'business_date',
        'actual_demand',
        'lag_d1',
        'lag_d2',
        'lag_d3',
        'lag_d7',
        'rolling_mean_3d',
        'rolling_mean_7d',
        'temperature',
        'rainfall',
        'day_of_week',
        'is_weekend',
        'data_quality',
    ];

    protected function casts(): array
    {
        return [
            'business_date' => 'date',
            'actual_demand' => 'decimal:4',
            'lag_d1' => 'decimal:4',
            'lag_d2' => 'decimal:4',
            'lag_d3' => 'decimal:4',
            'lag_d7' => 'decimal:4',
            'rolling_mean_3d' => 'decimal:4',
            'rolling_mean_7d' => 'decimal:4',
            'temperature' => 'decimal:3',
            'rainfall' => 'decimal:3',
            'day_of_week' => 'integer',
            'is_weekend' => 'boolean',
        ];
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(DatasetVersion::class, 'dataset_version_id');
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
