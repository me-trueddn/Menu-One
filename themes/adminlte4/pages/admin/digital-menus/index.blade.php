@extends('theme::layouts.app')
@section('title', __('menu.digital_menus'))
@section('page-title', __('menu.digital_menus'))
@section('page-actions')
    <a href="{{ route('admin.digital-menus.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-qr-code me-1"></i>{{ __('menu.create_qr_menu') }}
    </a>
@endsection
@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
<div class="card">
    <div class="card-body table-responsive p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>{{ __('menu.name') }}</th>
                    <th>{{ __('menu.menu_public_url') }}</th>
                    <th>{{ __('menu.status') }}</th>
                    <th class="text-end">{{ __('menu.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($menus as $menu)
                    @php($publicUrl = \App\Support\DigitalMenuUrl::forMenu($menu))
                    <tr>
                        <td>{{ $menu->name }}</td>
                        <td class="text-break"><code class="small">{{ $publicUrl }}</code></td>
                        <td>
                            @if($menu->is_active)
                                <span class="badge text-bg-success">{{ __('menu.active') }}</span>
                            @else
                                <span class="badge text-bg-secondary">{{ __('menu.inactive') }}</span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            <form action="{{ route('admin.digital-menus.toggle-active', $menu) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $menu->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                    {{ $menu->is_active ? __('menu.deactivate') : __('menu.activate') }}
                                </button>
                            </form>
                            <a href="{{ route('admin.digital-menus.show', $menu) }}" class="btn btn-sm btn-outline-primary">
                                {{ __('menu.qr_menu') }}
                            </a>
                            <form action="{{ route('admin.digital-menus.destroy', $menu) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('{{ __('menu.confirm_delete_digital_menu') }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('menu.delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-muted text-center py-4">{{ __('menu.digital_menus_empty') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($menus->hasPages())
        <div class="card-footer">{{ $menus->links() }}</div>
    @endif
</div>
@endsection
