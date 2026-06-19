<?php

namespace Database\Seeders;

use App\Models\LicenseType;
use Illuminate\Database\Seeder;

class LicenseTypeSeeder extends Seeder
{
    public function run(): void
    {
        LicenseType::firstOrCreate(
            ['slug' => 'trial-30'],
            [
                'name' => 'Deneme (30 Gün)',
                'duration_days' => 30,
                'is_default' => true,
                'is_active' => true,
                'description' => 'Yeni cafeler için varsayılan 30 günlük deneme lisansı.',
                'sort_order' => 1,
            ]
        );
    }
}
