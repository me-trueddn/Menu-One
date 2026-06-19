<?php

namespace Database\Seeders;

use App\Support\PlatformModules;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class ModulePermissionSeeder extends Seeder
{
    public function run(): void
    {
        PlatformModules::syncPermissions();

        $platformAdmin = Role::query()->where('name', 'platform_admin')->first();

        if ($platformAdmin) {
            $platformAdmin->syncPermissions(PlatformModules::allPermissionNames());
        }
    }
}
