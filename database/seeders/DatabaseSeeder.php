<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['platform_admin', 'cafe_admin', 'waiter', 'kitchen', 'cashier', 'user'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        Role::query()->whereIn('name', ['platform_admin', 'cafe_admin', 'waiter', 'kitchen', 'cashier', 'user'])
            ->update(['is_system' => true]);

        $superAdminEmail = env('SUPER_ADMIN_EMAIL');

        if (filled($superAdminEmail)) {
            $superAdmin = User::updateOrCreate(
                ['email' => $superAdminEmail, 'tenant_id' => null],
                [
                    'name' => env('SUPER_ADMIN_NAME', 'Platform Admin'),
                    'password' => Hash::make((string) env('SUPER_ADMIN_PASSWORD', 'password')),
                    'email_verified_at' => now(),
                    'is_super_admin' => true,
                    'is_active' => true,
                ]
            );
            $superAdmin->assignRole('platform_admin');
        }

        User::query()->whereNull('public_id')->each(function (User $user) {
            $user->update(['public_id' => \App\Services\UserPublicIdGenerator::generate()]);
        });

        $this->call(ModulePermissionSeeder::class);
        $this->call(SettingsSeeder::class);
        $this->call(LicenseTypeSeeder::class);
    }
}
