@extends('theme::layouts.app')

@section('title', __('menu.ticket_categories'))
@section('page-title', __('menu.ticket_categories'))

@section('page-actions')
<a href="{{ route('platform.ticket-categories.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> {{ __('menu.add') }}</a>
@endsection

@section('content')
@include('theme::partials.platform.ticket-nav')

<div class="card">
    <div class="card-body table-responsive p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>{{ __('menu.name') }}</th>
                    <th>{{ __('menu.slug') }}</th>
                    <th>{{ __('menu.status') }}</th>
                    <th>{{ __('menu.sort_order') }}</th>
                    <th class="text-end">{{ __('menu.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td>{{ $category->name }}</td>
                        <td><code>{{ $category->slug }}</code></td>
                        <td><span class="badge {{ $category->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $category->is_active ? __('menu.active') : __('menu.inactive') }}</span></td>
                        <td>{{ $category->sort_order }}</td>
                        <td class="text-end">
                            <a href="{{ route('platform.ticket-categories.edit', $category) }}" class="btn btn-sm btn-outline-primary">{{ __('menu.edit') }}</a>
                            <form action="{{ route('platform.ticket-categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('menu.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">{{ __('menu.delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted">{{ __('menu.no_data') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($categories->hasPages())<div class="card-footer">{{ $categories->links() }}</div>@endif
</div>
@endsection
