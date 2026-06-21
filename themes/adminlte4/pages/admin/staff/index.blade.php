@extends('theme::layouts.app')
@section('title', __('menu.staff'))
@section('page-title', __('menu.staff'))
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">{{ __('menu.staff') }}</h5>
        <a href="{{ route('admin.staff.create') }}" class="btn btn-primary btn-sm">{{ __('menu.add_staff') }}</a>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>{{ __('menu.full_name') }}</th>
                    <th>{{ __('menu.email') }}</th>
                    <th>{{ __('menu.role') }}</th>
                    <th class="text-end">{{ __('menu.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($staff as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ __('menu.role_'.$user->getRoleNames()->first()) }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.staff.edit', $user) }}" class="btn btn-sm btn-outline-primary">{{ __('menu.edit') }}</a>
                            @if($user->id !== auth()->id())
                                <form action="{{ route('admin.staff.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('menu.confirm_remove_staff') }}')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">{{ __('menu.remove') }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">{{ __('menu.no_staff') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($staff->hasPages())<div class="card-footer">{{ $staff->links() }}</div>@endif
</div>
@endsection
