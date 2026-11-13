<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessSetting extends Model
{
    protected $fillable = [
        'setting_key',
        'setting_value',
        'value_type',
        'description',
        'updated_by',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('setting_key', $key)->first();
        if (! $setting) {
            return $default;
        }

        return match ($setting->value_type) {
            'int' => (int) $setting->setting_value,
            'float' => (float) $setting->setting_value,
            'bool' => filter_var($setting->setting_value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($setting->setting_value, true),
            default => $setting->setting_value,
        };
    }

    public static function set(string $key, mixed $value, string $type = 'string', ?string $description = null): void
    {
        $stringValue = is_array($value) || is_object($value)
            ? json_encode($value)
            : (is_bool($value) ? ($value ? '1' : '0') : (string) $value);

        static::updateOrCreate(
            ['setting_key' => $key],
            [
                'setting_value' => $stringValue,
                'value_type' => $type,
                'description' => $description,
                'updated_by' => auth()->id(),
            ]
        );
    }
}
