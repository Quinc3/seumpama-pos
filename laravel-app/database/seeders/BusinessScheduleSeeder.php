<?php

namespace Database\Seeders;

use App\Models\BusinessSchedule;
use Illuminate\Database\Seeder;

class BusinessScheduleSeeder extends Seeder
{
    public function run(): void
    {
        // Per DATABASE_DESIGN.md §4.7
        // 0=Sunday, 6=Saturday
        $schedules = [
            ['day_of_week' => 0, 'opening_time' => '15:00', 'closing_time' => '23:00', 'closing_day_offset' => 0], // Minggu
            ['day_of_week' => 1, 'opening_time' => '15:00', 'closing_time' => '23:00', 'closing_day_offset' => 0], // Senin
            ['day_of_week' => 2, 'opening_time' => '15:00', 'closing_time' => '23:00', 'closing_day_offset' => 0], // Selasa
            ['day_of_week' => 3, 'opening_time' => '15:00', 'closing_time' => '23:00', 'closing_day_offset' => 0], // Rabu
            ['day_of_week' => 4, 'opening_time' => '15:00', 'closing_time' => '23:00', 'closing_day_offset' => 0], // Kamis
            ['day_of_week' => 5, 'opening_time' => '15:00', 'closing_time' => '02:00', 'closing_day_offset' => 1], // Jumat
            ['day_of_week' => 6, 'opening_time' => '15:00', 'closing_time' => '02:00', 'closing_day_offset' => 1], // Sabtu
        ];

        foreach ($schedules as $row) {
            BusinessSchedule::updateOrCreate(
                ['day_of_week' => $row['day_of_week']],
                $row + ['is_open' => true]
            );
        }
    }
}
