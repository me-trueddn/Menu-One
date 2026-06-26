<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\TicketCategory;
use App\Support\TicketSettings;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Teknik Destek', 'slug' => 'teknik-destek', 'sort_order' => 1],
            ['name' => 'Lisans / Faturalama', 'slug' => 'lisans-faturalama', 'sort_order' => 2],
            ['name' => 'Genel Soru', 'slug' => 'genel-soru', 'sort_order' => 3],
        ] as $category) {
            TicketCategory::firstOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'is_active' => true,
                    'sort_order' => $category['sort_order'],
                ],
            );
        }

        Setting::setIfMissing(TicketSettings::EXTENSIONS_KEY, 'jpg,jpeg,png,doc,docx,xls,xlsx', 'tickets');
        Setting::setIfMissing(TicketSettings::MAX_SIZE_MB_KEY, '10', 'tickets');
    }
}
