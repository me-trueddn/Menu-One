@extends('theme::layouts.app')

@section('title', $provider->label())
@section('page-title', __('menu.integrations').' — '.$provider->label())

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.integrations.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> {{ __('menu.back') }}
    </a>
</div>

<form method="POST" action="{{ route('admin.integrations.update', $provider->slug()) }}">
    @csrf @method('PUT')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi {{ $provider->icon() }}"></i> {{ $provider->label() }}</h5>
            <div class="form-check form-switch mb-0">
                <input type="hidden" name="is_enabled" value="0">
                <input class="form-check-input" type="checkbox" name="is_enabled" id="is_enabled" value="1"
                       @checked(old('is_enabled', $integration?->is_enabled))>
                <label class="form-check-label" for="is_enabled">{{ __('menu.integration_enabled') }}</label>
            </div>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">{{ __('menu.integration_webhook_url') }}</label>
                <input type="text" class="form-control" value="{{ $webhook_url }}" readonly>
                <div class="form-text">{{ __('menu.integration_webhook_url_hint') }}</div>
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('menu.integration_webhook_secret') }}</label>
                <input type="password" name="webhook_secret" class="form-control" autocomplete="new-password"
                       placeholder="{{ $has_webhook_secret ? '••••••••' : '' }}">
                @if($has_webhook_secret)
                    <div class="form-text">{{ __('menu.secret_configured', ['value' => '••••••••']) }}</div>
                @endif
            </div>

            @foreach($fields ?? ($schema['fields'] ?? []) as $field)
                <div class="mb-3">
                    <label class="form-label">{{ $field['label'] }}</label>
                    @if(($field['type'] ?? 'text') === 'checkbox')
                        <input type="hidden" name="{{ $field['key'] }}" value="0">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="{{ $field['key'] }}" name="{{ $field['key'] }}" value="1"
                                   @checked((bool) old($field['key'], $config[$field['key']] ?? false))>
                        </div>
                    @else
                        <input type="{{ $field['type'] ?? 'text' }}" name="{{ $field['key'] }}" class="form-control"
                               value="{{ old($field['key'], $config[$field['key']] ?? '') }}"
                               autocomplete="off">
                    @endif
                </div>
            @endforeach

            @if(!empty($schema['docs_url']))
                <p class="small text-muted mb-0">
                    <a href="{{ $schema['docs_url'] }}" target="_blank" rel="noopener">{{ __('menu.integration_docs') }}</a>
                </p>
            @endif
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">{{ __('menu.save') }}</button>
            <a href="{{ route('admin.integrations.mappings', $provider->slug()) }}" class="btn btn-outline-secondary">
                {{ __('menu.integration_product_mappings') }}
            </a>
        </div>
    </div>
</form>
@endsection
