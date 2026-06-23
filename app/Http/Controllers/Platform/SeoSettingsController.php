<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\ImageStorage;
use App\Support\SeoPolicy;
use App\Support\SettingPersistence;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SeoSettingsController extends Controller
{
    public function edit(): View
    {
        SeoPolicy::ensureDefaults();

        $seo = Setting::mergedGroup('seo', SeoPolicy::defaults());

        return view('theme::pages.platform.settings.seo', [
            'settings' => [
                'seo_enabled' => filter_var($seo['seo_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'seo_index_public' => filter_var($seo['seo_index_public'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'seo_track_authenticated' => filter_var($seo['seo_track_authenticated'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'google_tag_manager_id' => $seo['google_tag_manager_id'] ?? '',
                'google_analytics_id' => $seo['google_analytics_id'] ?? '',
                'google_search_console_verification' => $seo['google_search_console_verification'] ?? '',
                'google_ads_id' => $seo['google_ads_id'] ?? '',
                'google_ads_conversion_label' => $seo['google_ads_conversion_label'] ?? '',
                'yandex_webmaster_verification' => $seo['yandex_webmaster_verification'] ?? '',
                'yandex_metrika_id' => $seo['yandex_metrika_id'] ?? '',
                'seo_meta_title' => $seo['seo_meta_title'] ?? '',
                'seo_meta_description' => $seo['seo_meta_description'] ?? '',
                'seo_meta_keywords' => $seo['seo_meta_keywords'] ?? '',
                'seo_organization_name' => $seo['seo_organization_name'] ?? '',
                'seo_organization_url' => $seo['seo_organization_url'] ?? '',
                'seo_og_image_path' => $seo['seo_og_image_path'] ?? '',
                'seo_og_image_url' => ($seo['seo_og_image_path'] ?? '') ? ImageStorage::url($seo['seo_og_image_path']) : null,
                'sitemap_url' => route('seo.sitemap'),
                'robots_url' => route('seo.robots'),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'seo_enabled' => ['nullable', Rule::in(['0', '1'])],
            'seo_index_public' => ['nullable', Rule::in(['0', '1'])],
            'seo_track_authenticated' => ['nullable', Rule::in(['0', '1'])],
            'google_tag_manager_id' => ['nullable', 'string', 'max:20', 'regex:/^(|GTM-[A-Z0-9]+)$/i'],
            'google_analytics_id' => ['nullable', 'string', 'max:20', 'regex:/^(|G-[A-Z0-9]+)$/i'],
            'google_search_console_verification' => ['nullable', 'string', 'max:120'],
            'google_ads_id' => ['nullable', 'string', 'max:20', 'regex:/^(|AW-[0-9]+)$/i'],
            'google_ads_conversion_label' => ['nullable', 'string', 'max:120'],
            'yandex_webmaster_verification' => ['nullable', 'string', 'max:120'],
            'yandex_metrika_id' => ['nullable', 'string', 'max:20', 'regex:/^(|\d{5,12})$/'],
            'seo_meta_title' => ['nullable', 'string', 'max:120'],
            'seo_meta_description' => ['nullable', 'string', 'max:320'],
            'seo_meta_keywords' => ['nullable', 'string', 'max:255'],
            'seo_organization_name' => ['nullable', 'string', 'max:120'],
            'seo_organization_url' => ['nullable', 'url', 'max:255'],
            'seo_og_image' => ['nullable', 'image', 'max:2048'],
        ]);

        Setting::setMany([
            'seo_enabled' => $request->input('seo_enabled', '0') === '1' ? '1' : '0',
            'seo_index_public' => $request->input('seo_index_public', '0') === '1' ? '1' : '0',
            'seo_track_authenticated' => $request->input('seo_track_authenticated', '0') === '1' ? '1' : '0',
            'google_tag_manager_id' => strtoupper(trim((string) ($validated['google_tag_manager_id'] ?? ''))),
            'google_analytics_id' => strtoupper(trim((string) ($validated['google_analytics_id'] ?? ''))),
            'google_search_console_verification' => trim((string) ($validated['google_search_console_verification'] ?? '')),
            'google_ads_id' => strtoupper(trim((string) ($validated['google_ads_id'] ?? ''))),
            'google_ads_conversion_label' => trim((string) ($validated['google_ads_conversion_label'] ?? '')),
            'yandex_webmaster_verification' => trim((string) ($validated['yandex_webmaster_verification'] ?? '')),
            'yandex_metrika_id' => trim((string) ($validated['yandex_metrika_id'] ?? '')),
            'seo_meta_title' => SettingPersistence::keepOrApply('seo_meta_title', $validated['seo_meta_title'] ?? null),
            'seo_meta_description' => SettingPersistence::keepOrApply('seo_meta_description', $validated['seo_meta_description'] ?? null),
            'seo_meta_keywords' => SettingPersistence::keepOrApply('seo_meta_keywords', $validated['seo_meta_keywords'] ?? null),
            'seo_organization_name' => SettingPersistence::keepOrApply('seo_organization_name', $validated['seo_organization_name'] ?? null),
            'seo_organization_url' => SettingPersistence::keepOrApply('seo_organization_url', $validated['seo_organization_url'] ?? null),
        ], 'seo');

        if ($request->hasFile('seo_og_image')) {
            ImageStorage::delete(Setting::get('seo_og_image_path'));
            Setting::set('seo_og_image_path', ImageStorage::storeSiteFile($request->file('seo_og_image')), 'seo');
        }

        return redirect()
            ->route('platform.settings.seo')
            ->with('success', __('menu.messages.updated'));
    }
}
