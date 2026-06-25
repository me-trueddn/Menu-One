<?php

namespace App\Support;

use App\Models\Setting;

class IntegrationBillingDefaults
{
    /** @return array<string, string> */
    public static function defaults(): array
    {
        return [
            'integration_billing_e_invoice_enabled' => '0',
            'integration_billing_e_waybill_enabled' => '0',
            'integration_billing_tax_office' => '',
            'integration_billing_tax_number' => '',
            'integration_billing_invoice_prefix' => 'INV',
            'integration_billing_company_name' => '',
            'integration_billing_company_address' => '',
        ];
    }

    /** @return array<string, string> */
    public static function all(): array
    {
        return Setting::mergedGroup('integration_billing', static::defaults());
    }

    public static function bool(string $key): bool
    {
        $all = static::all();

        return filter_var($all[$key] ?? '0', FILTER_VALIDATE_BOOLEAN);
    }
}

