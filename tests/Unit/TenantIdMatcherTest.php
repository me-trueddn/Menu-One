<?php

namespace Tests\Unit;

use App\Support\TenantIdMatcher;
use PHPUnit\Framework\TestCase;

class TenantIdMatcherTest extends TestCase
{
    public function test_matches_truncated_prefix(): void
    {
        $this->assertTrue(TenantIdMatcher::matches('619', '619-718'));
        $this->assertFalse(TenantIdMatcher::matches('619', '746-518'));
        $this->assertTrue(TenantIdMatcher::matches('619-718', '619-718'));
    }
}
