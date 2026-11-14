<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Bahan Baku', 'description' => 'Pembelian bahan baku di luar PO'],
            ['name' => 'Operasional', 'description' => 'Listrik, air, internet'],
            ['name' => 'Sewa', 'description' => 'Sewa tempat, peralatan'],
            ['name' => 'Gaji', 'description' => 'Gaji karyawan, bonus'],
            ['name' => 'Perawatan', 'description' => 'Maintenance peralatan'],
            ['name' => 'Lain-lain', 'description' => 'Pengeluaran tidak terkategori'],
        ];

        foreach ($categories as $cat) {
            ExpenseCategory::updateOrCreate(
                ['name' => $cat['name']],
                $cat + ['is_active' => true]
            );
        }
    }
}
