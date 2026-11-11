<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Coffee', 'description' => 'Espresso-based dan manual brew', 'sort_order' => 1],
            ['name' => 'Non-Coffee', 'description' => 'Minuman non-kopi', 'sort_order' => 2],
            ['name' => 'Tea', 'description' => 'Teh dan matcha', 'sort_order' => 3],
            ['name' => 'Signature Drinks', 'description' => 'Menu andalan Seumpama', 'sort_order' => 4],
            ['name' => 'Snacks', 'description' => 'Camilan ringan', 'sort_order' => 5],
            ['name' => 'Desserts', 'description' => 'Makanan penutup', 'sort_order' => 6],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                $category + ['is_active' => true]
            );
        }
    }
}
