<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RbacSeeder::class,
            AdminUserSeeder::class,
            UnitSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            IngredientSeeder::class,
            RecipeSeeder::class,
            BusinessScheduleSeeder::class,
            PaymentMethodSeeder::class,
            BusinessSettingSeeder::class,
            ExpenseCategorySeeder::class,
            WeatherProviderSeeder::class,
        ]);
    }
}
