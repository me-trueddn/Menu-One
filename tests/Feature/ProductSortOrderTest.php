<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\LicenseType;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantLicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSortOrderTest extends TestCase
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
            'id' => 'cafe-sort',
            'name' => 'Sort Cafe',
            'slug' => 'sort-cafe',
            'is_active' => true,
        ]);

        app(TenantLicenseService::class)->assignDefault($this->tenant);

        $this->admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->admin->assignRole('cafe_admin');

        tenancy()->initialize($this->tenant);
    }

    public function test_product_sort_order_is_saved(): void
    {
        $category = Category::create(['name' => 'Ana', 'sort_order' => 1]);

        $response = $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'name' => 'Sıralı Ürün',
            'unit_type' => Product::UNIT_PIECE,
            'price' => 45,
            'vat_rate' => 10,
            'sort_order' => 7,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $product = Product::first();

        $this->assertNotNull($product);
        $this->assertSame(7, $product->sort_order);
    }
}
