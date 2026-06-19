<?php

namespace Database\Seeders;

use App\Enums\TableStatus;
use App\Models\Category;
use App\Models\DiningTable;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['platform_admin', 'cafe_admin', 'waiter', 'kitchen', 'user'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        Role::query()->whereIn('name', ['platform_admin', 'cafe_admin', 'waiter', 'kitchen', 'user'])
            ->update(['is_system' => true]);

        $platformAdmin = User::firstOrCreate(
            ['email' => 'admin@menu-one.test'],
            [
                'tenant_id' => null,
                'name' => 'Platform Admin',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );
        $platformAdmin->assignRole('platform_admin');

        $farukAdmin = User::updateOrCreate(
            ['email' => 'faruk.altun@trueddn.com.tr', 'tenant_id' => null],
            [
                'name' => 'Faruk Altun',
                'password' => '12345',
                'email_verified_at' => now(),
                'is_super_admin' => true,
                'is_active' => true,
            ]
        );
        $farukAdmin->assignRole('platform_admin');

        $tenant = Tenant::firstOrCreate(
            ['slug' => 'demo-cafe'],
            [
                'id' => '100-001',
                'name' => 'Demo Cafe',
                'is_active' => true,
            ]
        );

        tenancy()->initialize($tenant);

        $cafeAdmin = User::firstOrCreate(
            ['tenant_id' => $tenant->id, 'email' => 'admin@demo-cafe.test'],
            [
                'name' => 'Cafe Admin',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );
        $cafeAdmin->assignRole('cafe_admin');

        if (! $tenant->owner_user_id) {
            $tenant->update(['owner_user_id' => $cafeAdmin->id]);
        }

        $waiter = User::firstOrCreate(
            ['tenant_id' => $tenant->id, 'email' => 'waiter@demo-cafe.test'],
            [
                'name' => 'Demo Garson',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );
        $waiter->assignRole('waiter');

        $kitchen = User::firstOrCreate(
            ['tenant_id' => $tenant->id, 'email' => 'kitchen@demo-cafe.test'],
            [
                'name' => 'Demo Mutfak',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );
        $kitchen->assignRole('kitchen');

        if (DiningTable::count() === 0) {
            foreach (range(1, 10) as $i) {
                DiningTable::create([
                    'name' => 'Masa '.$i,
                    'capacity' => 4,
                    'status' => TableStatus::Empty,
                ]);
            }
        }

        $categories = [
            ['name' => 'Sıcak İçecekler', 'sort_order' => 1, 'products' => [
                ['name' => 'Türk Kahvesi', 'price' => 45.00],
                ['name' => 'Latte', 'price' => 65.00],
                ['name' => 'Çay', 'price' => 25.00],
            ]],
            ['name' => 'Yiyecekler', 'sort_order' => 2, 'products' => [
                ['name' => 'Tost', 'price' => 85.00],
                ['name' => 'Hamburger', 'price' => 150.00],
                ['name' => 'Salata', 'price' => 120.00],
            ]],
            ['name' => 'Tatlılar', 'sort_order' => 3, 'products' => [
                ['name' => 'Cheesecake', 'price' => 95.00],
                ['name' => 'Brownie', 'price' => 75.00],
            ]],
        ];

        foreach ($categories as $catData) {
            $category = Category::firstOrCreate(
                ['name' => $catData['name']],
                ['sort_order' => $catData['sort_order']]
            );

            foreach ($catData['products'] as $productData) {
                Product::firstOrCreate(
                    ['name' => $productData['name']],
                    [
                        'category_id' => $category->id,
                        'price' => $productData['price'],
                        'is_active' => true,
                    ]
                );
            }
        }

        tenancy()->end();

        User::query()->whereNull('public_id')->each(function (User $user) {
            $user->update(['public_id' => \App\Services\UserPublicIdGenerator::generate()]);
        });

        $this->call(ModulePermissionSeeder::class);
        $this->call(SettingsSeeder::class);
    }
}
