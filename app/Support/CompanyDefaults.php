<?php

namespace App\Support;

use App\Models\Setting;

class CompanyDefaults
{
    /** @return array<string, string> */
    public static function all(): array
    {
        return [
            'company_name' => Setting::get('default_company_name', ''),
            'company_tax_number' => Setting::get('default_company_tax_number', ''),
            'company_phone' => Setting::get('default_company_phone', ''),
            'company_email' => Setting::get('default_company_email', Setting::get('support_email', '')),
            'company_address' => Setting::get('default_company_address', ''),
        ];
    }
}
