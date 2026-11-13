<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\RecipeItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class RecipeSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::where('email', 'admin@seumpama.com')->value('id');
        if (! $adminId) {
            $this->command->warn('Admin user not found. Skip RecipeSeeder.');
            return;
        }

        $ingredients = Ingredient::all()->keyBy('sku');

        $recipes = [
            'PRD-001' => [
                'name' => 'Resep Espresso',
                'items' => ['ING-COF-004' => 18],
            ],
            'PRD-002' => [
                'name' => 'Resep Americano',
                'items' => ['ING-COF-004' => 18],
            ],
            'PRD-003' => [
                'name' => 'Resep Cappuccino',
                'items' => ['ING-COF-004' => 18, 'ING-MLK-001' => 150],
            ],
            'PRD-004' => [
                'name' => 'Resep Cafe Latte',
                'items' => ['ING-COF-004' => 18, 'ING-MLK-001' => 180],
            ],
            'PRD-005' => [
                'name' => 'Resep Matcha Latte',
                'items' => ['ING-TEA-001' => 5, 'ING-MLK-001' => 200],
            ],
            'PRD-007' => [
                'name' => 'Resep Seumpama Signature',
                'items' => [
                    'ING-COF-002' => 18,
                    'ING-MLK-002' => 100,
                    'ING-SYR-001' => 15,
                    'ING-SWT-001' => 10,
                ],
            ],
            // PRD-006 Chocolate, PRD-008 French Fries, PRD-009 Croissant — tidak ada resep
            // (bahan baku cocoa/kentang/croissant belum tersedia di list)
        ];

        foreach ($recipes as $sku => $recipeData) {
            $product = Product::where('sku', $sku)->first();
            if (! $product) {
                $this->command->warn("Product {$sku} not found. Skip.");
                continue;
            }

            if ($product->recipe()->exists()) {
                $this->command->info("Product {$sku} already has active recipe. Skip.");
                continue;
            }

            $recipe = Recipe::create([
                'product_id' => $product->id,
                'version' => 1,
                'name' => $recipeData['name'],
                'effective_from' => now(),
                'is_active' => true,
                'created_by' => $adminId,
            ]);

            foreach ($recipeData['items'] as $ingSku => $qty) {
                $ing = $ingredients->get($ingSku);
                if (! $ing) {
                    $this->command->warn("Ingredient {$ingSku} not found. Skip item.");
                    continue;
                }
                RecipeItem::create([
                    'recipe_id' => $recipe->id,
                    'ingredient_id' => $ing->id,
                    'quantity' => $qty,
                    'unit_id' => $ing->unit_id,
                ]);
            }
        }
    }
}
