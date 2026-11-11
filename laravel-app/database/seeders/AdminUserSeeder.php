<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'admin@seumpama.com'],
            [
                'name' => 'Administrator',
                'phone' => null,
                'is_active' => true,
                'password' => 'admin123',
            ]
        );

        $ownerRole = Role::where('name', 'Owner')->first();
        if ($ownerRole) {
            $user->assignRole($ownerRole);
        }
    }
}
