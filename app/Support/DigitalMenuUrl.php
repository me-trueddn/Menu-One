<?php

namespace App\Support;

use App\Models\DigitalMenu;
use App\Models\Tenant;

class DigitalMenuUrl
{
    public static function forMenu(DigitalMenu $menu, ?Tenant $tenant = null): string
    {
        $tenant ??= tenant();

        return route('digital-menu.show', [
            'tenantId' => $tenant?->getTenantKey() ?? $menu->tenant_id,
            'menuPublicId' => $menu->public_id,
        ]);
    }
}
