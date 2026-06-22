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

class ProductFormTest extends TestCase
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
            'id' => 'cafe-products',
            'name' => 'Product Cafe',
            'slug' => 'product-cafe',
            'is_active' => true,
        ]);

        app(TenantLicenseService::class)->assignDefault($this->tenant);

        $this->admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->admin->assignRole('cafe_admin');

        tenancy()->initialize($this->tenant);
    }

    public function test_products_index_shows_add_product_modal_trigger(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.products.index'));

        $response->assertOk();
        $response->assertSee('id="productFormModal"', false);
        $response->assertSee(__('menu.product_tab_general'), false);
    }

    public function test_cafe_admin_can_create_product_with_extended_fields(): void
    {
        $category = Category::create([
            'name' => 'İçecekler',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'name' => 'Latte',
            'code' => 'lat-001',
            'description' => 'Sütlü kahve',
            'barcode' => '869000000001',
            'unit_type' => Product::UNIT_PIECE,
            'price' => 85.50,
            'purchase_price' => 40,
            'vat_rate' => 10,
            'is_active' => 1,
            'extras' => [
                'cooking_time' => '3 dk',
                'calories' => '120',
                'is_splittable' => 1,
            ],
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success');

        $product = Product::first();

        $this->assertNotNull($product);
        $this->assertSame('Latte', $product->name);
        $this->assertSame('lat-001', $product->code);
        $this->assertSame('3 dk', $product->extra('cooking_time'));
        $this->assertTrue($product->extra('is_splittable'));
    }

    public function test_create_route_opens_modal_on_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.products.create'));

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('open_product_modal', true);
    }

    public function test_add_product_modal_is_not_prefilled_with_last_listed_product(): void
    {
        $category = Category::create(['name' => 'Ana', 'sort_order' => 1]);

        Product::create([
            'category_id' => $category->id,
            'name' => 'İlk Ürün',
            'price' => 10,
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $category->id,
            'name' => 'Son Liste Ürünü',
            'price' => 20,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.products.index'));

        $response->assertOk();
        $response->assertSee('Son Liste Ürünü');

        $content = $response->getContent();
        $modalStart = strpos($content, 'id="productFormModal"');

        $this->assertNotFalse($modalStart);

        $modalChunk = substr($content, $modalStart, 12000);

        $this->assertStringNotContainsString('value="Son Liste Ürünü"', $modalChunk);
        $this->assertStringContainsString('id="productName"', $modalChunk);
    }

    /**
     * @return array{category: Category, baklava: Product, cay: Product}
     */
    private function createSearchableProducts(): array
    {
        $category = Category::create(['name' => 'Tatlılar', 'sort_order' => 1]);

        $baklava = Product::create([
            'category_id' => $category->id,
            'name' => 'Baklava',
            'code' => 'tat001',
            'barcode' => '8691111111111',
            'price' => 120,
            'is_active' => true,
        ]);

        $cay = Product::create([
            'category_id' => $category->id,
            'name' => 'Çay',
            'code' => 'ice-001',
            'barcode' => '8692222222222',
            'price' => 20,
            'is_active' => true,
        ]);

        return compact('category', 'baklava', 'cay');
    }

    public function test_products_index_can_search_by_name(): void
    {
        $this->createSearchableProducts();

        $this->actingAs($this->admin)
            ->get(route('admin.products.index', ['q' => 'baklava']))
            ->assertOk()
            ->assertSee('Baklava')
            ->assertDontSee('>Çay<', false);
    }

    public function test_products_index_can_search_by_code(): void
    {
        $this->createSearchableProducts();

        $this->actingAs($this->admin)
            ->get(route('admin.products.index', ['q' => 'tat001']))
            ->assertOk()
            ->assertSee('Baklava')
            ->assertDontSee('>Çay<', false);
    }

    public function test_products_index_can_search_by_category(): void
    {
        $this->createSearchableProducts();

        $this->actingAs($this->admin)
            ->get(route('admin.products.index', ['q' => 'Tatlılar']))
            ->assertOk()
            ->assertSee('Baklava')
            ->assertSee('Çay');
    }

    public function test_products_index_shows_search_field(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee(__('menu.product_search_placeholder'), false);
    }
}
