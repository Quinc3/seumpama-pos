<?php

namespace App\Services\Weather;

interface WeatherProviderInterface
{
    /**
     * Fetch recent days of hourly weather data.
     *
     * @param int $days Number of past days to fetch (1-92)
     * @return array{latitude: float, longitude: float, hourly: array<int, array{time: string, temperature: ?float, precipitation: ?float, condition: string}>}
     * @throws \RuntimeException on API failure
     */
    public function fetchRecentDays(int $days): array;
}
