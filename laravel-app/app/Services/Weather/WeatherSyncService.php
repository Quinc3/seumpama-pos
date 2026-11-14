<?php

namespace App\Services\Weather;

use App\Models\BusinessSchedule;
use App\Models\WeatherDailySummary;
use App\Models\WeatherObservation;
use App\Models\WeatherProvider;
use App\Services\BusinessDateResolver;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class WeatherSyncService
{
    public function __construct(
        protected WeatherProviderInterface $provider,
        protected BusinessDateResolver $resolver
    ) {}

    /**
     * Fetch recent days from provider, store observations, aggregate per business_date.
     *
     * @return array{observations: int, summaries: int, provider: string}
     * @throws \RuntimeException on API failure
     */
    public function syncRecentDays(int $days): array
    {
        $provider = WeatherProvider::where('provider_code', 'open-meteo')->where('is_active', true)->first();
        if (! $provider) {
            throw new \RuntimeException('Weather provider "open-meteo" tidak aktif. Jalankan seeder.');
        }

        // Snapshot SEBELUM
        $beforeObs = WeatherObservation::where('provider_id', $provider->id)->count();
        $beforeSummaries = WeatherDailySummary::count();

        // Fetch (may throw on network error)
        $result = $this->provider->fetchRecentDays($days);

        $processedObs = 0;
        $businessDatesTouched = [];

        DB::transaction(function () use ($result, $provider, &$processedObs, &$businessDatesTouched) {
            foreach ($result['hourly'] as $row) {
                $observedAt = Carbon::parse($row['time']);
                $businessDate = $this->resolver->resolve($observedAt);

                WeatherObservation::updateOrCreate(
                    [
                        'provider_id' => $provider->id,
                        'observed_at' => $observedAt,
                    ],
                    [
                        'latitude' => $result['latitude'],
                        'longitude' => $result['longitude'],
                        'temperature' => $row['temperature'],
                        'rainfall' => $row['precipitation'],
                        'weather_condition' => $row['condition'],
                    ]
                );
                $processedObs++;
                $businessDatesTouched[$businessDate->format('Y-m-d')] = $businessDate;
            }

            foreach ($businessDatesTouched as $businessDate) {
                $this->aggregateForDate($provider, $businessDate);
            }
        });

        // Snapshot SESUDAH
        $afterObs = WeatherObservation::where('provider_id', $provider->id)->count();
        $afterSummaries = WeatherDailySummary::count();

        $newObs = max(0, $afterObs - $beforeObs);
        $updatedObs = max(0, $processedObs - $newObs);
        $newSummaries = max(0, $afterSummaries - $beforeSummaries);
        $updatedSummaries = max(0, count($businessDatesTouched) - $newSummaries);

        return [
            'provider' => $provider->name,
            'days' => $days,
            'processed_observations' => $processedObs,
            'new_observations' => $newObs,
            'updated_observations' => $updatedObs,
            'touched_summaries' => count($businessDatesTouched),
            'new_summaries' => $newSummaries,
            'updated_summaries' => $updatedSummaries,
        ];
    }

    protected function aggregateForDate(WeatherProvider $provider, Carbon $businessDate): void
    {
        $schedule = BusinessSchedule::forDay($businessDate->dayOfWeek);
        if (! $schedule || ! $schedule->is_open) {
            return;
        }

        $opening = $businessDate->copy()->setTimeFromTimeString($schedule->opening_time);
        $closing = $businessDate->copy()
            ->addDays($schedule->closing_day_offset)
            ->setTimeFromTimeString($schedule->closing_time);

        $observations = WeatherObservation::where('provider_id', $provider->id)
            ->where('observed_at', '>=', $opening)
            ->where('observed_at', '<', $closing)
            ->get();

        if ($observations->isEmpty()) {
            return;
        }

        $expectedHours = max(1, (int) $opening->diffInHours($closing));
        $count = $observations->count();
        $completeness = min(100, round(($count / $expectedHours) * 100, 2));

        $dominantCondition = $observations
            ->groupBy('weather_condition')
            ->map(fn($g) => $g->count())
            ->sortDesc()
            ->keys()
            ->first();

        WeatherDailySummary::updateOrCreate(
            ['business_date' => $businessDate->format('Y-m-d')],
            [
                'average_temperature' => round((float) $observations->avg('temperature'), 3),
                'minimum_temperature' => round((float) $observations->min('temperature'), 3),
                'maximum_temperature' => round((float) $observations->max('temperature'), 3),
                'total_rainfall' => round((float) $observations->sum('rainfall'), 3),
                'dominant_condition' => $dominantCondition,
                'observation_count' => $count,
                'data_completeness' => $completeness,
                'source_status' => $completeness >= 95 ? 'complete' : 'partial',
            ]
        );
    }
}
