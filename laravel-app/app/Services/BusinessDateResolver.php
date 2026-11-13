<?php

namespace App\Services;

use App\Models\BusinessSchedule;
use Carbon\Carbon;

class BusinessDateResolver
{
    /**
     * Resolve business_date untuk datetime tertentu.
     *
     * Logic:
     * 1. Cek apakah datetime saat ini > opening_time schedule hari ini → business_date = hari ini
     * 2. Kalau tidak, cek schedule kemarin (dengan closing_day_offset = 1):
     *    - Kalau datetime < (closing_time kemarin + offset), masih di business_date kemarin
     * 3. Fallback: business_date = hari ini
     */
    public function resolve(?Carbon $datetime = null): Carbon
    {
        $datetime = $datetime ?: now();

        // Cek schedule hari ini
        $todaySchedule = BusinessSchedule::forDay($datetime->dayOfWeek);
        if ($todaySchedule && $todaySchedule->is_open) {
            $opening = $this->todayTime($datetime, $todaySchedule->opening_time);
            if ($datetime->gte($opening)) {
                return $datetime->copy()->startOfDay();
            }
        }

        // Cek schedule kemarin (dengan closing_day_offset)
        $yesterday = $datetime->copy()->subDay();
        $yesterdaySchedule = BusinessSchedule::forDay($yesterday->dayOfWeek);
        if ($yesterdaySchedule && $yesterdaySchedule->is_open && $yesterdaySchedule->closing_day_offset > 0) {
            $closing = $yesterday->copy()->startOfDay()
                ->addDays($yesterdaySchedule->closing_day_offset)
                ->setTimeFromTimeString($yesterdaySchedule->closing_time);
            if ($datetime->lt($closing)) {
                return $yesterday->copy()->startOfDay();
            }
        }

        // Fallback: hari ini
        return $datetime->copy()->startOfDay();
    }

    protected function todayTime(Carbon $datetime, string $time): Carbon
    {
        return $datetime->copy()->startOfDay()->setTimeFromTimeString($time);
    }

    /**
     * Ambil atau buat business session yang open untuk business_date terkait.
     */
    public function getOrOpenSession(?Carbon $datetime = null, ?int $userId = null): \App\Models\BusinessSession
    {
        $businessDate = $this->resolve($datetime);
        $userId = $userId ?: auth()->id();

        if (! $userId) {
            throw new \InvalidArgumentException('User ID tidak tersedia untuk membuka business session.');
        }

        return \App\Models\BusinessSession::firstOrCreate(
            ['business_date' => $businessDate->format('Y-m-d')],
            [
                'opened_at' => now(),
                'status' => 'open',
                'opened_by' => $userId,
            ]
        );
    }
}
