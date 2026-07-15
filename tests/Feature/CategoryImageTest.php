<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\LicenseType;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantLicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CategoryImageTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

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
            'id' => 'cafe-cat-img',
            'name' => 'Cat Cafe',
            'slug' => 'cat-cafe',
            'is_active' => true,
        ]);

        app(TenantLicenseService::class)->assignDefault($this->tenant);

        $this->admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->admin->assignRole('cafe_admin');

        tenancy()->initialize($this->tenant);
    }

    public function test_cafe_admin_can_upload_category_image(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'name' => 'Başlangıçlar',
            'sort_order' => 1,
            'image' => UploadedFile::fake()->create('starter.jpg', 100, 'image/jpeg'),
        ]);

        $response->assertRedirect(route('admin.categories.index'));

        $category = Category::first();

        $this->assertNotNull($category);
        $this->assertNotNull($category->image_path);
        $this->assertNotNull($category->imageUrl());
    }
}
