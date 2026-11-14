<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeatherObservation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'provider_id',
        'observed_at',
        'latitude',
        'longitude',
        'temperature',
        'rainfall',
        'weather_condition',
    ];

    protected function casts(): array
    {
        return [
            'observed_at' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'temperature' => 'decimal:3',
            'rainfall' => 'decimal:3',
            'created_at' => 'datetime',
        ];
    }

    public function provider()
    {
        return $this->belongsTo(WeatherProvider::class, 'provider_id');
    }
}
