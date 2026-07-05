@extends('theme::layouts.app')
@section('title', __('menu.qr_menu').': '.$menu->name)
@section('page-title', __('menu.qr_menu').': '.$menu->name)
@section('page-actions')
    <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-box-arrow-up-right me-1"></i>{{ __('menu.qr_menu_preview') }}
    </a>
    <a href="{{ route('admin.digital-menus.qr-download', $menu) }}" class="btn btn-primary btn-sm">
        <i class="bi bi-download me-1"></i>{{ __('menu.download_qr') }}
    </a>
@endsection
@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
<div class="row g-3">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">{{ __('menu.qr_code') }}</h5></div>
            <div class="card-body text-center">
                <img src="{{ $qrDataUri }}" alt="{{ __('menu.qr_code') }}" class="img-fluid" style="max-width:280px">
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">{{ __('menu.menu_public_url') }}</h5></div>
            <div class="card-body">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="publicMenuUrl" value="{{ $publicUrl }}" readonly>
                    <button type="button" class="btn btn-outline-secondary" id="copyMenuUrl">{{ __('menu.copy') }}</button>
                </div>
                <p class="text-muted small mb-0">{{ __('menu.digital_menu_url_hint') }}</p>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-header"><h5 class="mb-0">{{ __('menu.digital_menu_info') }}</h5></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">{{ __('menu.name') }}</dt>
                    <dd class="col-sm-8">{{ $menu->name }}</dd>
                    <dt class="col-sm-4">{{ __('menu.menu_id') }}</dt>
                    <dd class="col-sm-8"><code>{{ $menu->public_id }}</code></dd>
                    <dt class="col-sm-4">{{ __('menu.status') }}</dt>
                    <dd class="col-sm-8">
                        @if($menu->is_active)
                            <span class="badge text-bg-success">{{ __('menu.active') }}</span>
                        @else
                            <span class="badge text-bg-secondary">{{ __('menu.inactive') }}</span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div>
<a href="{{ route('admin.digital-menus.index') }}" class="btn btn-secondary mt-3">← {{ __('menu.digital_menus') }}</a>
@endsection

@push('scripts')
<script>
document.getElementById('copyMenuUrl')?.addEventListener('click', function () {
    const input = document.getElementById('publicMenuUrl');
    input.select();
    navigator.clipboard?.writeText(input.value);
});
</script>
@endpush
