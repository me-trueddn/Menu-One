<?php

namespace Tests\Unit;

use App\Support\ClientIp;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class ClientIpTest extends TestCase
{
    public function test_returns_public_ip_from_x_real_ip_behind_local_proxy(): void
    {
        $request = Request::create('/login', 'POST', server: [
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_X_REAL_IP' => '203.0.113.50',
        ]);

        $this->assertSame('203.0.113.50', ClientIp::resolve($request));
    }

    public function test_returns_first_public_ip_from_x_forwarded_for(): void
    {
        $request = Request::create('/login', 'POST', server: [
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_X_FORWARDED_FOR' => '198.51.100.10, 10.0.0.1',
        ]);

        $this->assertSame('198.51.100.10', ClientIp::resolve($request));
    }

    public function test_keeps_loopback_when_not_behind_proxy(): void
    {
        $request = Request::create('/login', 'POST', server: [
            'REMOTE_ADDR' => '203.0.113.50',
        ]);

        $this->assertSame('203.0.113.50', ClientIp::resolve($request));
    }

    public function test_ignores_spoofed_forwarded_header_on_direct_connection(): void
    {
        $request = Request::create('/login', 'POST', server: [
            'REMOTE_ADDR' => '203.0.113.50',
            'HTTP_X_FORWARDED_FOR' => '1.2.3.4',
        ]);

        $this->assertSame('203.0.113.50', ClientIp::resolve($request));
    }
}
