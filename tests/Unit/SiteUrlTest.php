<?php

namespace Tests\Unit;

use App\Support\SiteUrl;
use Tests\TestCase;

class SiteUrlTest extends TestCase
{
    public function test_normalize_rejects_local_urls(): void
    {
        $this->assertNull(SiteUrl::normalize('http://127.0.0.1:8000'));
        $this->assertSame('https://panel.example.com', SiteUrl::normalize('https://panel.example.com'));
    }

    public function test_first_usable_skips_invalid_database_panel_url(): void
    {
        $this->assertSame(
            'https://panel.example.com',
            SiteUrl::firstUsable('http://127.0.0.1:8000', 'https://panel.example.com')
        );
    }
}
