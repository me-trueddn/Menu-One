<?php

namespace Tests\Feature;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TenantVirtualColumnTest extends TestCase
{
    use RefreshDatabase;

    public function test_data_json_does_not_overwrite_custom_columns_on_retrieve(): void
    {
        DB::table('tenants')->insert([
            'id' => '619-718',
            'name' => 'Seyyit Cafe',
            'slug' => 'seyyit-cafe',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
            'data' => json_encode([
                'id' => '619',
                'name' => 'Wrong Name',
                'slug' => 'wrong-slug',
            ]),
        ]);

        $tenant = Tenant::query()->find('619-718');

        $this->assertNotNull($tenant);
        $this->assertSame('619-718', $tenant->id);
        $this->assertSame('Seyyit Cafe', $tenant->name);
        $this->assertSame('seyyit-cafe', $tenant->slug);
    }
}
