<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            ['name' => 'Cash', 'code' => 'cash'],
            ['name' => 'QRIS', 'code' => 'qris'],
            ['name' => 'Transfer', 'code' => 'transfer'],
        ];

        foreach ($methods as $m) {
            PaymentMethod::updateOrCreate(
                ['code' => $m['code']],
                $m + ['is_active' => true]
            );
        }
    }
}
