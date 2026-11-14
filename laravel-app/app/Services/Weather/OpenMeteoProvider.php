<?php

namespace App\Services\Weather;

use Illuminate\Support\Facades\Http;

class OpenMeteoProvider implements WeatherProviderInterface
{
    public function fetchRecentDays(int $days): array
    {
        if ($days < 1 || $days > 92) {
            throw new \InvalidArgumentException('Days harus antara 1 dan 92 (Open-Meteo limit).');
        }

        $config = config('services.open_meteo');

        $response = Http::timeout($config['timeout'])
            ->get($config['base_url'], [
                'latitude' => $config['latitude'],
                'longitude' => $config['longitude'],
                'hourly' => 'temperature_2m,precipitation,weathercode',
                'timezone' => 'Asia/Jakarta',
                'past_days' => $days,
                'forecast_days' => 1,
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException("Open-Meteo HTTP {$response->status()}: " . substr($response->body(), 0, 200));
        }

        $data = $response->json();
        if (! isset($data['hourly']['time']) || ! is_array($data['hourly']['time'])) {
            throw new \RuntimeException('Open-Meteo response tidak valid.');
        }

        $times = $data['hourly']['time'];
        $temps = $data['hourly']['temperature_2m'] ?? [];
        $rains = $data['hourly']['precipitation'] ?? [];
        $codes = $data['hourly']['weathercode'] ?? [];

        $hourly = [];
        foreach ($times as $i => $time) {
            $hourly[] = [
                'time' => $time,
                'temperature' => isset($temps[$i]) ? (float) $temps[$i] : null,
                'precipitation' => isset($rains[$i]) ? (float) $rains[$i] : null,
                'condition' => $this->codeToCondition((int) ($codes[$i] ?? -1)),
            ];
        }

        return [
            'latitude' => (float) ($data['latitude'] ?? $config['latitude']),
            'longitude' => (float) ($data['longitude'] ?? $config['longitude']),
            'hourly' => $hourly,
        ];
    }

    protected function codeToCondition(int $code): string
    {
        return match (true) {
            $code === 0 => 'Clear',
            in_array($code, [1, 2, 3]) => 'Partly cloudy',
            in_array($code, [45, 48]) => 'Fog',
            in_array($code, [51, 53, 55, 56, 57]) => 'Drizzle',
            in_array($code, [61, 63, 65, 66, 67]) => 'Rain',
            in_array($code, [71, 73, 75, 77]) => 'Snow',
            in_array($code, [80, 81, 82]) => 'Showers',
            in_array($code, [85, 86]) => 'Snow showers',
            in_array($code, [95, 96, 99]) => 'Thunderstorm',
            default => 'Unknown',
        };
    }
}
