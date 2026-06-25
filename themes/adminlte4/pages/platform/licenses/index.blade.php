@extends('theme::layouts.app')

@section('title', __('menu.licenses'))
@section('page-title', __('menu.licenses'))

@section('page-actions')
<a href="{{ route('platform.licenses.licensegate') }}" class="btn btn-outline-secondary btn-sm me-1">
    <i class="bi bi-plug"></i> LicenseGate
</a>
<a href="{{ route('platform.licenses.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> {{ __('menu.add') }}</a>
@endsection

@section('content')
<div class="card">
    <div class="card-body table-responsive p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>{{ __('menu.name') }}</th>
                    <th>{{ __('menu.duration_days') }}</th>
                    <th>{{ __('menu.status') }}</th>
                    <th class="text-end">{{ __('menu.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($licenses as $license)
                    <tr>
                        <td>
                            {{ $license->name }}
                            @if($license->is_default)<span class="badge text-bg-info ms-1">{{ __('menu.default') }}</span>@endif
                        </td>
                        <td>{{ $license->duration_days }}</td>
                        <td>
                            <span class="badge {{ $license->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ $license->is_active ? __('menu.active') : __('menu.inactive') }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('platform.licenses.edit', $license) }}" class="btn btn-sm btn-outline-primary">{{ __('menu.edit') }}</a>
                            @unless($license->is_default)
                                <form action="{{ route('platform.licenses.destroy', $license) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('menu.confirm_delete') }}')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">{{ __('menu.delete') }}</button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted">{{ __('menu.no_data') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($licenses->hasPages())<div class="card-footer">{{ $licenses->links() }}</div>@endif
</div>
@endsection
