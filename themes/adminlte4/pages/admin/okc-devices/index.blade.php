@extends('theme::layouts.app')

@section('title', __('menu.okc_devices'))
@section('page-title', __('menu.okc_devices'))

@section('content')
<div class="card mb-3">
    <div class="card-header"><h6 class="mb-0">{{ __('menu.okc_device_add') }}</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.okc-devices.store') }}" class="row g-2">
            @csrf
            <div class="col-md-3">
                <input name="name" class="form-control" placeholder="{{ __('menu.name') }}" value="{{ old('name') }}" required>
            </div>
            <div class="col-md-2">
                <select name="device_type" class="form-select" required>
                    @foreach($deviceTypes as $type)
                        <option value="{{ $type->value }}" @selected(old('device_type', 'pos') === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input name="brand" class="form-control" placeholder="{{ __('menu.brand') }}" value="{{ old('brand') }}" list="okcBrandSuggestions">
                <datalist id="okcBrandSuggestions">
                    @foreach($deviceTypes as $type)
                        @foreach($type->suggestedBrands() as $brand)
                            <option value="{{ $brand }}">
                        @endforeach
                    @endforeach
                </datalist>
                <div class="form-text">{{ __('menu.okc_device_brand_hint') }}</div>
            </div>
            <div class="col-md-2"><input name="model" class="form-control" placeholder="{{ __('menu.model') }}" value="{{ old('model') }}"></div>
            <div class="col-md-3"><input name="endpoint" class="form-control" placeholder="https://device-endpoint.local/api" value="{{ old('endpoint') }}"></div>
            <div class="col-md-2"><input name="api_key" class="form-control" placeholder="API Key" value="{{ old('api_key') }}"></div>
            <div class="col-12 d-flex justify-content-between align-items-center">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" checked id="okcActiveCreate">
                    <label class="form-check-label" for="okcActiveCreate">{{ __('menu.active') }}</label>
                </div>
                <button class="btn btn-primary btn-sm">{{ __('menu.save') }}</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body table-responsive p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>{{ __('menu.name') }}</th>
                    <th>{{ __('menu.okc_device_type') }}</th>
                    <th>{{ __('menu.brand') }}</th>
                    <th>{{ __('menu.model') }}</th>
                    <th>Endpoint</th>
                    <th>{{ __('menu.status') }}</th>
                    <th class="text-end">{{ __('menu.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($devices as $device)
                    <tr>
                        <td>{{ $device->name }}</td>
                        <td>
                            <span class="badge text-bg-light border">{{ $device->device_type?->label() ?? '—' }}</span>
                        </td>
                        <td>{{ $device->brand ?: '—' }}</td>
                        <td>{{ $device->model ?: '—' }}</td>
                        <td class="small text-muted">{{ $device->endpoint ?: '—' }}</td>
                        <td>
                            <span class="badge {{ $device->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ $device->is_active ? __('menu.active') : __('menu.inactive') }}
                            </span>
                        </td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('admin.okc-devices.destroy', $device) }}" class="d-inline" onsubmit="return confirm('{{ __('menu.confirm_delete') }}')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">{{ __('menu.delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-3">{{ __('menu.no_records') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
