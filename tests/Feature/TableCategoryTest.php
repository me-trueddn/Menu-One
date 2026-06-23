<?php

namespace Tests\Feature;

use App\Models\DiningTable;
use App\Models\LicenseType;
use App\Models\TableCategory;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantLicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TableCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'cafe_admin', 'guard_name' => 'web']);

        LicenseType::firstOrCreate(
            ['slug' => 'trial-30'],
            [
                'name' => 'Trial',
                'duration_days' => 30,
                'is_default' => true,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $this->tenant = Tenant::create([
            'id' => 'table-cat-cafe',
            'name' => 'Table Category Cafe',
            'slug' => 'table-cat-cafe',
            'is_active' => true,
        ]);

        app(TenantLicenseService::class)->assignDefault($this->tenant);

        $this->admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->admin->assignRole('cafe_admin');

        tenancy()->initialize($this->tenant);
    }

    public function test_tables_index_starts_empty(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.tables.index'));

        $response->assertOk();
        $response->assertSee(__('menu.no_tables_setup'), false);
    }

    public function test_cafe_admin_can_create_table_category(): void
    {
        $this->actingAs($this->admin)->post(route('admin.table-categories.store'), [
            'name' => 'Bahçe',
            'sort_order' => 1,
        ])->assertRedirect(route('admin.tables.index'));

        $category = TableCategory::first();
        $this->assertNotNull($category);
        $this->assertSame('Bahçe', $category->name);
    }

    public function test_cafe_admin_can_create_table_in_category(): void
    {
        $category = TableCategory::create(['name' => 'Bahçe', 'sort_order' => 1]);

        $this->actingAs($this->admin)->post(route('admin.tables.store'), [
            'table_category_id' => $category->id,
            'name' => 'Masa 1',
            'capacity' => 4,
            'status' => 'empty',
        ])->assertRedirect(route('admin.tables.index'));

        $table = DiningTable::first();
        $this->assertNotNull($table);
        $this->assertSame($category->id, $table->table_category_id);
        $this->assertSame('Masa 1', $table->name);
    }

    public function test_cafe_admin_can_move_table_to_another_category(): void
    {
        $garden = TableCategory::create(['name' => 'Bahçe', 'sort_order' => 1]);
        $floor = TableCategory::create(['name' => '1. Kat', 'sort_order' => 2]);

        $table = DiningTable::create([
            'name' => 'Masa 5',
            'capacity' => 4,
            'status' => 'empty',
            'table_category_id' => $garden->id,
        ]);

        $this->actingAs($this->admin)->put(route('admin.tables.update', $table), [
            'table_category_id' => $floor->id,
            'name' => 'Masa 5',
            'capacity' => 4,
            'status' => 'empty',
        ])->assertRedirect(route('admin.tables.index'));

        $this->assertSame($floor->id, $table->fresh()->table_category_id);
    }

    public function test_deleting_category_moves_tables_to_uncategorized(): void
    {
        $category = TableCategory::create(['name' => '2. Kat', 'sort_order' => 1]);

        $table = DiningTable::create([
            'name' => 'Masa 9',
            'capacity' => 2,
            'status' => 'empty',
            'table_category_id' => $category->id,
        ]);

        $this->actingAs($this->admin)->delete(route('admin.table-categories.destroy', $category))
            ->assertRedirect(route('admin.tables.index'));

        $this->assertNull($table->fresh()->table_category_id);
        $this->assertDatabaseMissing('table_categories', ['id' => $category->id]);
    }
}
