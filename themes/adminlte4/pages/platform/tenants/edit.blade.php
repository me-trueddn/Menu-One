@extends('theme::layouts.app')

@section('title', __('menu.edit_cafe'))
@section('page-title', __('menu.edit_cafe'))

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('platform.tenants.update', $tenant) }}" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('menu.cafe_name') }}</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $tenant->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('menu.slug') }}</label>
                            <input type="text" name="slug" class="form-control" value="{{ old('slug', $tenant->slug) }}" required>
                        </div>
                        @include('theme::partials.company-fields', ['values' => $tenant->only(['company_name','company_tax_number','company_phone','company_email','company_address'])])
                        <div class="col-md-6">
                            <label class="form-label">{{ __('menu.cafe_logo') }}</label>
                            <input type="file" name="logo" class="form-control" accept="image/*">
                            <img src="{{ \App\Support\Branding::cafeLogoUrl($tenant) }}" alt="" class="mt-2" style="height:48px">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('menu.assign_license') }}</label>
                            <select name="license_type_id" class="form-select">
                                <option value="">{{ __('menu.keep_current_license') }}</option>
                                @foreach($licenseTypes as $licenseType)
                                    <option value="{{ $licenseType->id }}" @selected(old('license_type_id') == $licenseType->id)>
                                        {{ $licenseType->name }} ({{ $licenseType->duration_days }} {{ __('menu.duration_days') }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">{{ __('menu.assign_license_hint') }}</div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">{{ __('menu.update') }}</button>
                        <a href="{{ route('platform.tenants.index') }}" class="btn btn-secondary">{{ __('menu.cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-body">
                <h6>{{ __('menu.cafe_info') }}</h6>
                <p class="mb-1 small text-muted">{{ __('menu.created_at') }}</p>
                <p>{{ $tenant->created_at?->format('d.m.Y H:i') ?? '—' }}</p>
                @if($tenant->currentLicense)
                    <p class="mb-1 small text-muted">{{ __('menu.current_license') }}</p>
                    <p>{{ $tenant->currentLicense->licenseType?->name ?? '—' }}</p>
                    <p class="mb-1 small text-muted">{{ __('menu.license_expires') }}</p>
                    <p>{{ $tenant->currentLicense->expires_at->format('d.m.Y H:i') }}</p>
                @endif
                @if($tenant->isStopped())
                    <div class="alert alert-warning small">
                        <strong>{{ __('menu.cafe_stopped') }}</strong><br>
                        {{ $tenant->stop_note }}<br>
                        <span class="text-muted">{{ $tenant->stoppedBy?->name }} · {{ $tenant->stoppedBy?->email }}</span>
                    </div>
                    <form method="POST" action="{{ route('platform.tenants.resume', $tenant) }}">@csrf
                        <button class="btn btn-sm btn-success">{{ __('menu.cafe_resume') }}</button>
                    </form>
                @else
                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#stopCafeModal">{{ __('menu.cafe_stop') }}</button>
                @endif
            </div>
        </div>
    </div>
</div>

@include('theme::partials.tenant-staff-panel', ['tenant' => $tenant])

<div class="modal fade" id="stopCafeModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('platform.tenants.stop', $tenant) }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">{{ __('menu.cafe_stop') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="form-label">{{ __('menu.stop_note') }}</label>
                <textarea name="stop_note" class="form-control" rows="4" required></textarea>
            </div>
            <div class="modal-footer"><button class="btn btn-danger">{{ __('menu.cafe_stop') }}</button></div>
        </form>
    </div>
</div>
@endsection
