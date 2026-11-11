<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Gram', 'symbol' => 'g', 'unit_type' => 'weight'],
            ['name' => 'Kilogram', 'symbol' => 'kg', 'unit_type' => 'weight'],
            ['name' => 'Milliliter', 'symbol' => 'ml', 'unit_type' => 'volume'],
            ['name' => 'Liter', 'symbol' => 'L', 'unit_type' => 'volume'],
            ['name' => 'Pieces', 'symbol' => 'pcs', 'unit_type' => 'count'],
            ['name' => 'Pack', 'symbol' => 'pack', 'unit_type' => 'count'],
        ];

        foreach ($units as $unit) {
            Unit::updateOrCreate(
                ['symbol' => $unit['symbol']],
                $unit + ['is_active' => true]
            );
        }
    }
}
