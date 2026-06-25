@extends('theme::layouts.app')

@section('title', __('menu.integration_product_mappings'))
@section('page-title', $providerEnum->label().' — '.__('menu.integration_product_mappings'))

@section('content')
<div class="mb-3 d-flex gap-2">
    <a href="{{ route('admin.integrations.edit', $providerEnum->slug()) }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> {{ __('menu.settings') }}
    </a>
    <a href="{{ route('admin.integrations.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('menu.integrations') }}</a>
</div>

<div class="card mb-3">
    <div class="card-header"><h6 class="mb-0">{{ __('menu.add_integration_mapping') }}</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.integrations.mappings.store', $providerEnum->slug()) }}" class="row g-2">
            @csrf
            <div class="col-md-3">
                <input name="external_id" class="form-control" placeholder="{{ __('menu.integration_external_id') }}" required>
            </div>
            <div class="col-md-3">
                <input name="external_name" class="form-control" placeholder="{{ __('menu.integration_external_name') }}" required>
            </div>
            <div class="col-md-4">
                <select name="product_id" class="form-select">
                    <option value="">{{ __('menu.integration_select_product') }}</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">{{ __('menu.add') }}</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body table-responsive p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>{{ __('menu.integration_external_id') }}</th>
                    <th>{{ __('menu.integration_external_name') }}</th>
                    <th>{{ __('menu.product_name') }}</th>
                    <th class="text-end">{{ __('menu.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mappings as $mapping)
                    <tr>
                        <td><code>{{ $mapping->external_id }}</code></td>
                        <td>{{ $mapping->external_name }}</td>
                        <td>{{ $mapping->product?->name ?? '—' }}</td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('admin.integrations.mappings.destroy', [$providerEnum->slug(), $mapping]) }}"
                                  onsubmit="return confirm('{{ __('menu.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('menu.delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">{{ __('menu.no_integration_mappings') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
