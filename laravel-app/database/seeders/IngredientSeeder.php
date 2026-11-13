<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class IngredientSeeder extends Seeder
{
    public function run(): void
    {
        $unitIds = Unit::pluck('id', 'symbol');

        $ingredients = [
            // Coffee
            ['sku' => 'ING-COF-001', 'name' => 'Biji Kopi Arabica', 'category' => 'Coffee', 'unit' => 'g', 'min' => 1000, 'safety' => 500, 'lead' => 3],
            ['sku' => 'ING-COF-002', 'name' => 'Arabika Gayo', 'category' => 'Coffee', 'unit' => 'g', 'min' => 1000, 'safety' => 500, 'lead' => 5],
            ['sku' => 'ING-COF-003', 'name' => 'Robusta Temanggung', 'category' => 'Coffee', 'unit' => 'g', 'min' => 1000, 'safety' => 500, 'lead' => 5],
            ['sku' => 'ING-COF-004', 'name' => 'House Blend Espresso', 'category' => 'Coffee', 'unit' => 'g', 'min' => 1500, 'safety' => 800, 'lead' => 3],

            // Dairy
            ['sku' => 'ING-MLK-001', 'name' => 'Susu Fresh Milk', 'category' => 'Dairy', 'unit' => 'ml', 'min' => 3000, 'safety' => 1500, 'lead' => 1],
            ['sku' => 'ING-MLK-002', 'name' => 'Susu Full Cream', 'category' => 'Dairy', 'unit' => 'ml', 'min' => 2000, 'safety' => 1000, 'lead' => 1],
            ['sku' => 'ING-MLK-003', 'name' => 'Condensed Milk (SKM)', 'category' => 'Dairy', 'unit' => 'g', 'min' => 500, 'safety' => 200, 'lead' => 7],

            // Tea
            ['sku' => 'ING-TEA-001', 'name' => 'Green Tea Bubuk', 'category' => 'Tea', 'unit' => 'g', 'min' => 200, 'safety' => 100, 'lead' => 7],

            // Syrup
            ['sku' => 'ING-SYR-001', 'name' => 'Vanilla Syrup', 'category' => 'Syrup', 'unit' => 'ml', 'min' => 500, 'safety' => 200, 'lead' => 7],
            ['sku' => 'ING-SYR-002', 'name' => 'Caramel Syrup', 'category' => 'Syrup', 'unit' => 'ml', 'min' => 500, 'safety' => 200, 'lead' => 7],

            // Sweetener
            ['sku' => 'ING-SWT-001', 'name' => 'Gula Aren Cair', 'category' => 'Sweetener', 'unit' => 'ml', 'min' => 500, 'safety' => 200, 'lead' => 3],
        ];

        foreach ($ingredients as $row) {
            Ingredient::updateOrCreate(
                ['sku' => $row['sku']],
                [
                    'name' => $row['name'],
                    'category' => $row['category'],
                    'unit_id' => $unitIds[$row['unit']] ?? null,
                    'ingredient_type' => 'core',
                    'current_stock' => 10000,
                    'minimum_stock' => $row['min'],
                    'safety_stock' => $row['safety'],
                    'lead_time_days' => $row['lead'],
                    'is_prediction_target' => true,
                    'is_active' => true,
                ]
            );
        }
    }
}
