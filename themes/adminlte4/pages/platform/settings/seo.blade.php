@extends('theme::layouts.app')

@section('title', __('menu.seo_settings'))
@section('page-title', __('menu.seo_settings'))

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">{{ __('menu.seo_tools_title') }}</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('platform.settings.seo.update') }}" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="alert alert-light border mb-4">
                <div class="fw-semibold mb-1">{{ __('menu.seo_public_urls') }}</div>
                <div><code>{{ $settings['sitemap_url'] }}</code></div>
                <div class="mt-1"><code>{{ $settings['robots_url'] }}</code></div>
            </div>

            <div class="mb-4">
                <input type="hidden" name="seo_enabled" value="0">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="seo_enabled" id="seo_enabled" value="1"
                           @checked(old('seo_enabled', $settings['seo_enabled']))>
                    <label class="form-check-label" for="seo_enabled">{{ __('menu.seo_enabled') }}</label>
                </div>
                <div class="form-text">{{ __('menu.seo_enabled_hint') }}</div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <input type="hidden" name="seo_index_public" value="0">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="seo_index_public" id="seo_index_public" value="1"
                               @checked(old('seo_index_public', $settings['seo_index_public']))>
                        <label class="form-check-label" for="seo_index_public">{{ __('menu.seo_index_public') }}</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <input type="hidden" name="seo_track_authenticated" value="0">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="seo_track_authenticated" id="seo_track_authenticated" value="1"
                               @checked(old('seo_track_authenticated', $settings['seo_track_authenticated']))>
                        <label class="form-check-label" for="seo_track_authenticated">{{ __('menu.seo_track_authenticated') }}</label>
                    </div>
                </div>
            </div>

            <h6 class="border-bottom pb-2 mb-3">{{ __('menu.seo_meta_section') }}</h6>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="seo_meta_title">{{ __('menu.seo_meta_title') }}</label>
                    <input type="text" name="seo_meta_title" id="seo_meta_title" class="form-control"
                           value="{{ old('seo_meta_title', $settings['seo_meta_title']) }}" maxlength="120">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="seo_organization_name">{{ __('menu.seo_organization_name') }}</label>
                    <input type="text" name="seo_organization_name" id="seo_organization_name" class="form-control"
                           value="{{ old('seo_organization_name', $settings['seo_organization_name']) }}" maxlength="120">
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label" for="seo_meta_description">{{ __('menu.seo_meta_description') }}</label>
                    <textarea name="seo_meta_description" id="seo_meta_description" class="form-control" rows="3"
                              maxlength="320">{{ old('seo_meta_description', $settings['seo_meta_description']) }}</textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="seo_meta_keywords">{{ __('menu.seo_meta_keywords') }}</label>
                    <input type="text" name="seo_meta_keywords" id="seo_meta_keywords" class="form-control"
                           value="{{ old('seo_meta_keywords', $settings['seo_meta_keywords']) }}" maxlength="255">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="seo_organization_url">{{ __('menu.seo_organization_url') }}</label>
                    <input type="url" name="seo_organization_url" id="seo_organization_url" class="form-control"
                           value="{{ old('seo_organization_url', $settings['seo_organization_url']) }}" maxlength="255">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="seo_og_image">{{ __('menu.seo_og_image') }}</label>
                    <input type="file" name="seo_og_image" id="seo_og_image" class="form-control" accept="image/*">
                    @if($settings['seo_og_image_url'])
                        <img src="{{ $settings['seo_og_image_url'] }}" alt="" class="img-thumbnail mt-2" style="max-height:120px">
                    @endif
                </div>
            </div>

            <h6 class="border-bottom pb-2 mb-3 mt-2">{{ __('menu.seo_google_section') }}</h6>

            <h6 class="mb-3 text-muted">{{ __('menu.seo_google_tag_manager') }}</h6>
            <div class="mb-3">
                <label class="form-label" for="google_tag_manager_id">{{ __('menu.seo_gtm_id') }}</label>
                <input type="text" name="google_tag_manager_id" id="google_tag_manager_id" class="form-control"
                       value="{{ old('google_tag_manager_id', $settings['google_tag_manager_id']) }}"
                       placeholder="GTM-XXXXXXX">
                <div class="form-text">{{ __('menu.seo_gtm_hint') }}</div>
                @error('google_tag_manager_id')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <h6 class="border-bottom pb-2 mb-3">{{ __('menu.seo_google_analytics') }}</h6>
            <div class="mb-3">
                <label class="form-label" for="google_analytics_id">{{ __('menu.seo_ga4_id') }}</label>
                <input type="text" name="google_analytics_id" id="google_analytics_id" class="form-control"
                       value="{{ old('google_analytics_id', $settings['google_analytics_id']) }}"
                       placeholder="G-XXXXXXXXXX">
                <div class="form-text">{{ __('menu.seo_ga4_hint') }}</div>
                @error('google_analytics_id')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <h6 class="border-bottom pb-2 mb-3">{{ __('menu.seo_google_search_console') }}</h6>
            <div class="mb-3">
                <label class="form-label" for="google_search_console_verification">{{ __('menu.seo_gsc_verification') }}</label>
                <input type="text" name="google_search_console_verification" id="google_search_console_verification" class="form-control"
                       value="{{ old('google_search_console_verification', $settings['google_search_console_verification']) }}">
                <div class="form-text">{{ __('menu.seo_gsc_hint') }}</div>
                @error('google_search_console_verification')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <h6 class="border-bottom pb-2 mb-3">{{ __('menu.seo_google_ads') }}</h6>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="google_ads_id">{{ __('menu.seo_ads_id') }}</label>
                    <input type="text" name="google_ads_id" id="google_ads_id" class="form-control"
                           value="{{ old('google_ads_id', $settings['google_ads_id']) }}"
                           placeholder="AW-XXXXXXXXX">
                    @error('google_ads_id')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="google_ads_conversion_label">{{ __('menu.seo_ads_conversion_label') }}</label>
                    <input type="text" name="google_ads_conversion_label" id="google_ads_conversion_label" class="form-control"
                           value="{{ old('google_ads_conversion_label', $settings['google_ads_conversion_label']) }}">
                    <div class="form-text">{{ __('menu.seo_ads_hint') }}</div>
                </div>
            </div>

            <h6 class="border-bottom pb-2 mb-3 mt-4">{{ __('menu.seo_yandex_section') }}</h6>

            <div class="mb-3">
                <label class="form-label" for="yandex_webmaster_verification">{{ __('menu.seo_yandex_webmaster_verification') }}</label>
                <input type="text" name="yandex_webmaster_verification" id="yandex_webmaster_verification" class="form-control"
                       value="{{ old('yandex_webmaster_verification', $settings['yandex_webmaster_verification']) }}">
                <div class="form-text">{{ __('menu.seo_yandex_webmaster_hint') }}</div>
                @error('yandex_webmaster_verification')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="yandex_metrika_id">{{ __('menu.seo_yandex_metrika_id') }}</label>
                <input type="text" name="yandex_metrika_id" id="yandex_metrika_id" class="form-control"
                       value="{{ old('yandex_metrika_id', $settings['yandex_metrika_id']) }}"
                       placeholder="12345678" inputmode="numeric" pattern="\d{5,12}">
                <div class="form-text">{{ __('menu.seo_yandex_metrika_hint') }}</div>
                @error('yandex_metrika_id')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <button type="submit" class="btn btn-primary">{{ __('menu.save') }}</button>
        </form>
    </div>
</div>
@endsection
