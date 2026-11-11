<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'pos.access' => 'Akses halaman POS',
            'pos.create_transaction' => 'Buat transaksi POS',
            'pos.void_transaction' => 'Batalkan transaksi sebelum bayar',
            'pos.refund' => 'Proses refund setelah bayar',
            'inventory.view' => 'Lihat stok dan pergerakan',
            'inventory.manage' => 'Kelola stok, adjustment, opname',
            'products.view' => 'Lihat produk dan kategori',
            'products.manage' => 'Kelola produk dan kategori',
            'ann.view' => 'Lihat hasil prediksi ANN',
            'ann.train' => 'Jalankan training ANN',
            'reports.view' => 'Lihat laporan operasional',
            'users.manage' => 'Kelola pengguna dan role',
            'settings.manage' => 'Kelola konfigurasi sistem',
        ];

        foreach ($permissions as $name => $desc) {
            Permission::updateOrCreate(['name' => $name], ['description' => $desc]);
        }

        $matrix = [
            'Owner' => [
                'description' => 'Pemilik bisnis; akses penuh',
                'permissions' => array_keys($permissions),
            ],
            'Admin' => [
                'description' => 'Administrator operasional',
                'permissions' => [
                    'pos.access',
                    'pos.create_transaction',
                    'pos.void_transaction',
                    'pos.refund',
                    'inventory.view',
                    'inventory.manage',
                    'products.view',
                    'products.manage',
                    'ann.view',
                    'ann.train',
                    'reports.view',
                ],
            ],
            'Cashier' => [
                'description' => 'Kasir transaksi',
                'permissions' => ['pos.access', 'pos.create_transaction'],
            ],
            'Barista' => [
                'description' => 'Staf produksi dan penyajian',
                'permissions' => ['pos.access'],
            ],
        ];

        foreach ($matrix as $name => $config) {
            $role = Role::updateOrCreate(
                ['name' => $name],
                ['description' => $config['description']]
            );
            $role->permissions()->sync(
                Permission::whereIn('name', $config['permissions'])->pluck('id')
            );
        }
    }
}
