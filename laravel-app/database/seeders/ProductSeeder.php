<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categoryIds = Category::pluck('id', 'name');

        $products = [
            ['sku' => 'PRD-001', 'name' => 'Espresso', 'category' => 'Coffee', 'selling_price' => 18000, 'description' => 'Single shot espresso'],
            ['sku' => 'PRD-002', 'name' => 'Americano', 'category' => 'Coffee', 'selling_price' => 22000, 'description' => 'Espresso + air'],
            ['sku' => 'PRD-003', 'name' => 'Cappuccino', 'category' => 'Coffee', 'selling_price' => 28000, 'description' => 'Espresso + steamed milk + foam'],
            ['sku' => 'PRD-004', 'name' => 'Cafe Latte', 'category' => 'Coffee', 'selling_price' => 28000, 'description' => 'Espresso + milk'],
            ['sku' => 'PRD-005', 'name' => 'Matcha Latte', 'category' => 'Tea', 'selling_price' => 30000, 'description' => 'Matcha + milk'],
            ['sku' => 'PRD-006', 'name' => 'Chocolate', 'category' => 'Non-Coffee', 'selling_price' => 25000, 'description' => 'Chocolate + milk'],
            ['sku' => 'PRD-007', 'name' => 'Seumpama Signature', 'category' => 'Signature Drinks', 'selling_price' => 32000, 'description' => 'Signature house blend'],
            ['sku' => 'PRD-008', 'name' => 'French Fries', 'category' => 'Snacks', 'selling_price' => 20000, 'description' => 'Crispy fries'],
            ['sku' => 'PRD-009', 'name' => 'Croissant', 'category' => 'Desserts', 'selling_price' => 22000, 'description' => 'Butter croissant'],
        ];

        foreach ($products as $product) {
            $categoryId = $categoryIds[$product['category']] ?? null;
            if (! $categoryId) {
                continue;
            }

            Product::updateOrCreate(
                ['sku' => $product['sku']],
                [
                    'category_id' => $categoryId,
                    'name' => $product['name'],
                    'description' => $product['description'],
                    'selling_price' => $product['selling_price'],
                    'is_available' => true,
                    'is_active' => true,
                ]
            );
        }
    }
}
