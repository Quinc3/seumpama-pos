<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessSchedule extends Model
{
    protected $fillable = [
        'day_of_week',
        'opening_time',
        'closing_time',
        'closing_day_offset',
        'is_open',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'closing_day_offset' => 'integer',
            'is_open' => 'boolean',
        ];
    }

    public static function forDay(int $dayOfWeek): ?self
    {
        return static::where('day_of_week', $dayOfWeek)->first();
    }
}
