<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\LicenseGateService;
use App\Support\LicenseGateSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;

class LicenseGateSettingsController extends Controller
{
    public function edit(): View
    {
        $settings = LicenseGateSettings::all();

        return view('theme::pages.platform.licenses.licensegate', [
            'settings' => $settings,
            'hasAdminToken' => (bool) Setting::get('licensegate_admin_token'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'licensegate_enabled' => ['nullable', 'boolean'],
            'licensegate_user_id' => ['nullable', 'string', 'max:120'],
            'licensegate_base_url' => ['nullable', 'url', 'max:255'],
            'licensegate_admin_token' => ['nullable', 'string', 'max:500'],
            'licensegate_verify_on_access' => ['nullable', 'boolean'],
            'licensegate_strict_mode' => ['nullable', 'boolean'],
        ]);

        $pairs = [
            'licensegate_enabled' => $request->boolean('licensegate_enabled') ? '1' : '0',
            'licensegate_user_id' => trim((string) ($validated['licensegate_user_id'] ?? '')),
            'licensegate_base_url' => rtrim(trim((string) ($validated['licensegate_base_url'] ?? LicenseGateSettings::baseUrl())), '/'),
            'licensegate_verify_on_access' => $request->boolean('licensegate_verify_on_access') ? '1' : '0',
            'licensegate_strict_mode' => $request->boolean('licensegate_strict_mode') ? '1' : '0',
        ];

        if (! empty($validated['licensegate_admin_token'])) {
            $pairs['licensegate_admin_token'] = Crypt::encryptString($validated['licensegate_admin_token']);
        }

        Setting::setMany($pairs, 'licensegate');

        return redirect()
            ->route('platform.licenses.licensegate')
            ->with('success', __('menu.messages.updated'));
    }

    public function test(LicenseGateService $licenseGate): RedirectResponse
    {
        $result = $licenseGate->testConnection();

        return redirect()
            ->route('platform.licenses.licensegate')
            ->with($result['ok'] ? 'success' : 'error', $result['message']);
    }
}
