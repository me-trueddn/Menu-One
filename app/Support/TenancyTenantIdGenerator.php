<?php

namespace App\Support;

use Stancl\Tenancy\Contracts\UniqueIdentifierGenerator;

class TenancyTenantIdGenerator implements UniqueIdentifierGenerator
{
    public static function generate($resource): string
    {
        return TenantIdGenerator::generate();
    }
}
