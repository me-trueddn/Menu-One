<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\DigitalMenu;
use App\Models\LicenseType;
use App\Models\Product;
use App\Models\Tenant;
use App\Services\TenantLicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicDigitalMenuTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected DigitalMenu $menu;

    protected function setUp(): void
    {
        parent::setUp();

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
            'id' => 'cafe-public',
            'name' => 'Public Cafe',
            'slug' => 'public-cafe',
            'is_active' => true,
        ]);

        app(TenantLicenseService::class)->assignDefault($this->tenant);

        tenancy()->initialize($this->tenant);

        $this->menu = DigitalMenu::create([
            'name' => 'Ana Menü',
            'is_active' => true,
        ]);
    }

    public function test_public_menu_shows_categories_and_products_in_sort_order(): void
    {
        $catB = Category::create(['name' => 'Tatlılar', 'sort_order' => 2]);
        $catA = Category::create(['name' => 'İçecekler', 'sort_order' => 1]);

        Product::create([
            'category_id' => $catA->id,
            'name' => 'Zebra Latte',
            'price' => 90,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $catA->id,
            'name' => 'Americano',
            'price' => 70,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $catB->id,
            'name' => 'Cheesecake',
            'price' => 120,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $catB->id,
            'name' => 'Pasif Ürün',
            'price' => 10,
            'sort_order' => 0,
            'is_active' => false,
        ]);

        $response = $this->get(route('digital-menu.show', [
            'tenantId' => $this->tenant->id,
            'menuPublicId' => $this->menu->public_id,
        ]));

        $response->assertOk();
        $response->assertSeeInOrder(['İçecekler', 'Americano', 'Zebra Latte', 'Tatlılar', 'Cheesecake']);
        $response->assertDontSee('Pasif Ürün');
        $response->assertSee('70.00');
        $response->assertSee('90.00');
    }

    public function test_public_menu_hides_product_image_when_not_uploaded(): void
    {
        $category = Category::create(['name' => 'Yemekler', 'sort_order' => 1]);

        Product::create([
            'category_id' => $category->id,
            'name' => 'Mercimek',
            'description' => 'Günün çorbası',
            'price' => 55,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->get(route('digital-menu.show', [
            'tenantId' => $this->tenant->id,
            'menuPublicId' => $this->menu->public_id,
        ]));

        $response->assertOk();
        $response->assertSee('Mercimek');
        $response->assertSee('Günün çorbası');
        $this->assertStringNotContainsString('class="dm-product-image"', $response->getContent());
    }

    public function test_public_menu_hides_category_without_active_products(): void
    {
        Category::create(['name' => 'Boş Kategori', 'sort_order' => 1]);

        $category = Category::create(['name' => 'Dolu Kategori', 'sort_order' => 2]);
        Product::create([
            'category_id' => $category->id,
            'name' => 'Çay',
            'price' => 25,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->get(route('digital-menu.show', [
            'tenantId' => $this->tenant->id,
            'menuPublicId' => $this->menu->public_id,
        ]));

        $response->assertOk();
        $response->assertDontSee('Boş Kategori');
        $response->assertSee('Çay');
    }
}
