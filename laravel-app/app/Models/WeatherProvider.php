<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeatherProvider extends Model
{
    protected $fillable = ['name', 'provider_code', 'base_url', 'is_active', 'configuration'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'configuration' => 'array',
        ];
    }
}
