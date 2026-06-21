<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\SecurityPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserSecuritySettingsController extends Controller
{
    public function edit(): View
    {
        $settings = array_merge(SecurityPolicy::defaults(), Setting::query()
            ->where('group', 'security')
            ->pluck('value', 'key')
            ->all());

        foreach (array_keys(SecurityPolicy::defaults()) as $key) {
            if (str_starts_with($key, 'security_') && (str_contains($key, 'require') || str_contains($key, '2fa'))) {
                $settings[$key] = filter_var($settings[$key] ?? '0', FILTER_VALIDATE_BOOLEAN);
            }
        }

        return view('theme::pages.platform.users.security', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'security_password_min_length' => ['required', 'integer', 'min:4', 'max:64'],
            'security_password_expiry_days' => ['required', 'integer', 'min:0', 'max:365'],
            'security_password_min_age_days' => ['required', 'integer', 'min:0', 'max:30'],
            'security_password_history_count' => ['required', 'integer', 'min:0', 'max:24'],
            'security_lockout_attempts' => ['required', 'integer', 'min:3', 'max:20'],
            'security_lockout_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'security_session_idle_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'security_reset_link_minutes' => ['required', 'integer', 'min:5', 'max:1440'],
            'security_inactive_account_days' => ['required', 'integer', 'min:0', 'max:365'],
            'security_2fa_enabled_globally' => ['nullable', 'boolean'],
        ]);

        $pairs = collect($validated)->map(fn ($v) => (string) $v)->all();

        foreach ([
            'security_password_require_uppercase',
            'security_password_require_lowercase',
            'security_password_require_number',
            'security_password_require_symbol',
            'security_2fa_enabled_globally',
        ] as $flag) {
            $pairs[$flag] = $request->boolean($flag) ? '1' : '0';
        }

        Setting::setMany($pairs, 'security');

        return redirect()
            ->route('platform.users.security')
            ->with('success', __('menu.messages.updated'));
    }

    public function enforceTwoFactor(): RedirectResponse
    {
        Setting::set('security_2fa_enabled_globally', '1', 'security');
        Setting::set('security_2fa_required', '1', 'security');

        return redirect()
            ->route('platform.users.security')
            ->with('success', __('menu.two_factor_enforced'));
    }
}
