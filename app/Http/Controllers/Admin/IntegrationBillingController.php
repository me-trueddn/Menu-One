<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\IntegrationBillingDefaults;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IntegrationBillingController extends Controller
{
    public function edit(): View
    {
        $settings = IntegrationBillingDefaults::all();

        return view('theme::pages.admin.integrations.billing', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'integration_billing_tax_office' => ['nullable', 'string', 'max:255'],
            'integration_billing_tax_number' => ['nullable', 'string', 'max:50'],
            'integration_billing_invoice_prefix' => ['nullable', 'string', 'max:20'],
            'integration_billing_company_name' => ['nullable', 'string', 'max:255'],
            'integration_billing_company_address' => ['nullable', 'string', 'max:500'],
        ]);

        $pairs = [
            'integration_billing_e_invoice_enabled' => $request->boolean('integration_billing_e_invoice_enabled') ? '1' : '0',
            'integration_billing_e_waybill_enabled' => $request->boolean('integration_billing_e_waybill_enabled') ? '1' : '0',
            'integration_billing_tax_office' => $validated['integration_billing_tax_office'] ?? '',
            'integration_billing_tax_number' => $validated['integration_billing_tax_number'] ?? '',
            'integration_billing_invoice_prefix' => $validated['integration_billing_invoice_prefix'] ?? 'INV',
            'integration_billing_company_name' => $validated['integration_billing_company_name'] ?? '',
            'integration_billing_company_address' => $validated['integration_billing_company_address'] ?? '',
        ];

        Setting::setMany($pairs, 'integration_billing');

        return redirect()
            ->route('admin.integrations.billing.edit')
            ->with('success', __('menu.integration_billing_defaults_saved'));
    }
}

