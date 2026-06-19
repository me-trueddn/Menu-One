<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class FarukAdminSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'platform_admin', 'guard_name' => 'web']);

        $user = User::updateOrCreate(
            ['email' => 'faruk.altun@trueddn.com.tr', 'tenant_id' => null],
            [
                'name' => 'Faruk Altun',
                'password' => '12345',
                'email_verified_at' => now(),
                'is_super_admin' => true,
                'is_active' => true,
            ]
        );

        $user->syncRoles(['platform_admin']);
    }
}
