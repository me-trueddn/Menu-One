@extends('theme::layouts.app')

@section('title', __('menu.edit_table_category'))
@section('page-title', __('menu.edit_table_category'))

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.table-categories.update', $tableCategory) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label" for="categoryName">{{ __('menu.table_category_name') }}</label>
                <input id="categoryName" name="name" class="form-control" value="{{ old('name', $tableCategory->name) }}" required>
                @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label" for="sortOrder">{{ __('menu.sort_order') }}</label>
                <input id="sortOrder" type="number" name="sort_order" class="form-control"
                       value="{{ old('sort_order', $tableCategory->sort_order) }}" min="0">
                @error('sort_order')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <button class="btn btn-primary">{{ __('menu.save') }}</button>
            <a href="{{ route('admin.tables.index') }}" class="btn btn-secondary">{{ __('menu.cancel') }}</a>
        </form>
    </div>
</div>
@endsection
