<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Support\CloudflarePolicy;
use App\Support\MediaStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CloudflareMediaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::setMany([
            'cloudflare_images_enabled' => '1',
            'cloudflare_stream_enabled' => '1',
            'cloudflare_account_id' => 'acct-123',
            'cloudflare_account_hash' => 'hash-abc',
        ], 'site');

        Setting::set('cloudflare_api_token', encrypt('cf-token-secret'), 'site');
    }

    public function test_product_image_uploads_to_cloudflare_when_configured(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension required.');
        }
        Http::fake([
            'api.cloudflare.com/client/v4/accounts/acct-123/images/v1' => Http::response([
                'success' => true,
                'result' => ['id' => 'img-product-1'],
            ], 200),
        ]);

        $file = UploadedFile::fake()->image('product.jpg', 1600, 1200);

        $path = MediaStorage::storeProductFile($file, 'tenant-1');

        $this->assertSame('cfi:img-product-1', $path);
        $this->assertSame(
            'https://imagedelivery.net/hash-abc/img-product-1/product',
            MediaStorage::url($path, 'product'),
        );
    }

    public function test_cloudflare_image_delete_calls_api(): void
    {
        Http::fake([
            'api.cloudflare.com/client/v4/accounts/acct-123/images/v1/img-del' => Http::response([
                'success' => true,
            ], 200),
        ]);

        MediaStorage::delete('cfi:img-del');

        Http::assertSent(fn ($request) => $request->method() === 'DELETE'
            && str_contains($request->url(), '/images/v1/img-del'));
    }

    public function test_product_video_uploads_to_cloudflare_stream(): void
    {
        Http::fake([
            'api.cloudflare.com/client/v4/accounts/acct-123/stream' => Http::response([
                'success' => true,
                'result' => ['uid' => 'stream-uid-9'],
            ], 200),
        ]);

        $file = UploadedFile::fake()->create('clip.mp4', 500, 'video/mp4');

        $ref = MediaStorage::storeProductVideo($file, 'tenant-1');

        $this->assertSame('cfs:stream-uid-9', $ref);
        $this->assertSame(
            'https://customer-videodelivery.net/stream-uid-9/watch',
            MediaStorage::streamPlaybackUrl($ref),
        );
    }

    public function test_local_storage_used_when_cloudflare_disabled(): void
    {
        Setting::set('cloudflare_images_enabled', '0', 'site');

        $file = UploadedFile::fake()->create('local.jpg', 100, 'image/jpeg');

        $path = MediaStorage::storeProductFile($file, 'tenant-2');

        $this->assertStringStartsWith('images/Customerimage/tenant-2/products/', $path);
        $this->assertStringStartsWith('/storage/', MediaStorage::url($path) ?? '');
    }

    public function test_cloudflare_settings_persist_from_site_form(): void
    {
        Role::firstOrCreate(['name' => 'platform_admin', 'guard_name' => 'web']);
        $admin = User::factory()->create(['is_super_admin' => true]);
        $admin->assignRole('platform_admin');

        $payload = SiteSettingsPersistenceTest::sitePayload([
            'cloudflare_images_enabled' => '1',
            'cloudflare_stream_enabled' => '1',
            'cloudflare_account_id' => 'acct-save',
            'cloudflare_account_hash' => 'hash-save',
            'cloudflare_api_token' => 'new-token-value',
            'cloudflare_stream_customer_subdomain' => 'customer.example.stream',
        ]);

        $this->actingAs($admin)->put(route('platform.settings.site.update'), $payload)->assertRedirect();

        $this->assertTrue(CloudflarePolicy::imagesEnabled());
        $this->assertTrue(CloudflarePolicy::streamEnabled());
        $this->assertSame('acct-save', CloudflarePolicy::accountId());
        $this->assertSame('hash-save', CloudflarePolicy::accountHash());
        $this->assertSame('new-token-value', CloudflarePolicy::apiToken());
        $this->assertSame('customer.example.stream', CloudflarePolicy::streamCustomerSubdomain());
    }
}
