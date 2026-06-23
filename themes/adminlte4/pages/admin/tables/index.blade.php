@extends('theme::layouts.app')

@section('title', __('menu.tables'))
@section('page-title', __('menu.tables'))

@section('content')
<div class="card mb-3">
    <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <h5 class="mb-0">{{ __('menu.tables') }}</h5>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.table-categories.create') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-folder-plus"></i> {{ __('menu.add_table_category') }}
            </a>
            <a href="{{ route('admin.tables.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> {{ __('menu.add_table') }}
            </a>
        </div>
    </div>
</div>

@if($categories->isEmpty() && $uncategorizedTables->isEmpty())
    <div class="card">
        <div class="card-body text-center text-muted py-5">
            <p class="mb-3">{{ __('menu.no_tables_setup') }}</p>
            <a href="{{ route('admin.table-categories.create') }}" class="btn btn-outline-primary btn-sm me-2">{{ __('menu.add_table_category') }}</a>
            <a href="{{ route('admin.tables.create') }}" class="btn btn-primary btn-sm">{{ __('menu.add_table') }}</a>
        </div>
    </div>
@else
    @foreach($categories as $category)
        <div class="card mb-3">
            <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">{{ $category->name }}</h5>
                    <small class="text-muted">{{ trans_choice('menu.table_count', $category->tables->count(), ['count' => $category->tables->count()]) }}</small>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.tables.create', ['table_category_id' => $category->id]) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-plus-lg"></i> {{ __('menu.add_table') }}
                    </a>
                    <a href="{{ route('admin.table-categories.edit', $category) }}" class="btn btn-sm btn-outline-secondary">{{ __('menu.edit') }}</a>
                    <form action="{{ route('admin.table-categories.destroy', $category) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('{{ __('menu.confirm_delete_table_category') }}')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">{{ __('menu.delete') }}</button>
                    </form>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                @if($category->tables->isEmpty())
                    <p class="text-muted mb-0 p-3">{{ __('menu.no_tables_in_category') }}</p>
                @else
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('menu.table_name') }}</th>
                                <th>{{ __('menu.table_capacity_label') }}</th>
                                <th>{{ __('menu.table_status') }}</th>
                                <th class="text-end">{{ __('menu.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($category->tables as $table)
                                @include('theme::partials.admin.table-row', ['table' => $table])
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    @endforeach

    @if($uncategorizedTables->isNotEmpty())
        <div class="card mb-3">
            <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">{{ __('menu.uncategorized_tables') }}</h5>
                    <small class="text-muted">{{ trans_choice('menu.table_count', $uncategorizedTables->count(), ['count' => $uncategorizedTables->count()]) }}</small>
                </div>
                <a href="{{ route('admin.tables.create') }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-plus-lg"></i> {{ __('menu.add_table') }}
                </a>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('menu.table_name') }}</th>
                            <th>{{ __('menu.table_capacity_label') }}</th>
                            <th>{{ __('menu.table_status') }}</th>
                            <th class="text-end">{{ __('menu.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($uncategorizedTables as $table)
                            @include('theme::partials.admin.table-row', ['table' => $table])
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endif
@endsection
