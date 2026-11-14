<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeatherDailySummary extends Model
{
    protected $fillable = [
        'business_date',
        'average_temperature',
        'minimum_temperature',
        'maximum_temperature',
        'total_rainfall',
        'dominant_condition',
        'observation_count',
        'data_completeness',
        'source_status',
    ];

    protected function casts(): array
    {
        return [
            'business_date' => 'date',
            'average_temperature' => 'decimal:3',
            'minimum_temperature' => 'decimal:3',
            'maximum_temperature' => 'decimal:3',
            'total_rainfall' => 'decimal:3',
            'observation_count' => 'integer',
            'data_completeness' => 'decimal:2',
        ];
    }
}
