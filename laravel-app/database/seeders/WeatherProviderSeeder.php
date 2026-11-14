<?php

namespace Database\Seeders;

use App\Models\WeatherProvider;
use Illuminate\Database\Seeder;

class WeatherProviderSeeder extends Seeder
{
    public function run(): void
    {
        WeatherProvider::updateOrCreate(
            ['provider_code' => 'open-meteo'],
            [
                'name' => 'Open-Meteo',
                'base_url' => config('services.open_meteo.base_url'),
                'is_active' => true,
                'configuration' => [
                    'latitude' => config('services.open_meteo.latitude'),
                    'longitude' => config('services.open_meteo.longitude'),
                    'timezone' => 'Asia/Jakarta',
                ],
            ]
        );
    }
}
