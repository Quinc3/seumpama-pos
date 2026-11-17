<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockForecast extends Model
{
    protected $fillable = [
        'ingredient_id',
        'ann_prediction_id',
        'forecast_date',
        'current_stock',
        'predicted_demand',
        'average_daily_demand',
        'demand_std_dev',
        'lead_time_days',
        'service_level',
        'z_score',
        'safety_stock',
        'reorder_point',
        'estimated_coverage_days',
        'recommended_quantity',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'forecast_date' => 'date',
            'current_stock' => 'decimal:4',
            'predicted_demand' => 'decimal:4',
            'average_daily_demand' => 'decimal:4',
            'demand_std_dev' => 'decimal:4',
            'lead_time_days' => 'decimal:2',
            'service_level' => 'decimal:2',
            'z_score' => 'decimal:4',
            'safety_stock' => 'decimal:4',
            'reorder_point' => 'decimal:4',
            'estimated_coverage_days' => 'decimal:2',
            'recommended_quantity' => 'decimal:4',
        ];
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function annPrediction(): BelongsTo
    {
        return $this->belongsTo(AnnPrediction::class, 'ann_prediction_id');
    }
}
