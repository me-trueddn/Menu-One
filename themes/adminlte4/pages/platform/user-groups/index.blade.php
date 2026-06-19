@extends('theme::layouts.app')

@section('title', __('menu.user_groups'))
@section('page-title', __('menu.user_groups'))

@section('page-actions')
@if($currentUser->canPlatformModule('users', 'edit'))
    <a href="{{ route('platform.user-groups.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> {{ __('menu.create_group') }}
    </a>
@endif
@endsection

@section('content')
<div class="card">
    <div class="card-body table-responsive p-0">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>{{ __('menu.group_name') }}</th>
                    <th>{{ __('menu.group_description') }}</th>
                    <th>{{ __('menu.module_permissions') }}</th>
                    <th>{{ __('menu.type') }}</th>
                    <th class="text-end">{{ __('menu.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($groups as $group)
                    @php($matrix = \App\Support\PlatformModules::permissionsForRole($group))
                    <tr>
                        <td>{{ $group->name }}</td>
                        <td>{{ $group->description ?? '—' }}</td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($matrix as $moduleKey => $perm)
                                    @if($perm['view'] || $perm['edit'])
                                        <span class="badge text-bg-secondary">
                                            {{ __(config('platform_modules.modules.'.$moduleKey.'.label')) }}
                                            @if($perm['edit']) ({{ __('menu.edit') }}) @else ({{ __('menu.permission_view') }}) @endif
                                        </span>
                                    @endif
                                @endforeach
                                @if(collect($matrix)->every(fn ($p) => ! $p['view'] && ! $p['edit']))
                                    <span class="text-muted small">{{ __('menu.all_modules_inactive') }}</span>
                                @endif
                            </div>
                        </td>
                        <td>{{ $group->is_system ? __('menu.system') : __('menu.custom') }}</td>
                        <td class="text-end">
                            @unless($group->is_system)
                                @if($currentUser->canPlatformModule('users', 'view'))
                                    <a href="{{ route('platform.user-groups.edit', $group) }}" class="btn btn-sm btn-outline-primary">{{ __('menu.edit') }}</a>
                                @endif
                                @if($currentUser->canPlatformModule('users', 'edit'))
                                    <form action="{{ route('platform.user-groups.destroy', $group) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('menu.confirm_delete') }}')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">{{ __('menu.delete') }}</button>
                                    </form>
                                @endif
                            @else
                                <span class="badge text-bg-secondary">{{ __('menu.system') }}</span>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted">{{ __('menu.no_data') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($groups->hasPages())
        <div class="card-footer">{{ $groups->links() }}</div>
    @endif
</div>
@endsection
