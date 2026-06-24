<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PrepareProductionCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_prepare_production_updates_panel_url_and_clears_sessions(): void
    {
        config(['site.panel_url' => 'https://panel.example.com']);
        config(['site.main_site_url' => 'https://example.com']);

        Setting::set('panel_url', 'http://127.0.0.1:8000', 'site');

        DB::table('sessions')->insert([
            'id' => 'test-session',
            'user_id' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'payload' => 'test',
            'last_activity' => time(),
        ]);

        $this->artisan('deploy:prepare-production')
            ->assertSuccessful();

        $this->assertSame('https://panel.example.com', Setting::get('panel_url'));
        $this->assertDatabaseMissing('sessions', ['id' => 'test-session']);
    }
}
