@extends('theme::layouts.app')

@section('title', __('menu.add_table'))
@section('page-title', __('menu.add_table'))

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.tables.store') }}">
            @csrf
            @if($tableCategories->isNotEmpty())
                <div class="mb-3">
                    <label class="form-label" for="tableCategory">{{ __('menu.table_category') }}</label>
                    <select id="tableCategory" name="table_category_id" class="form-select">
                        <option value="">{{ __('menu.no_table_category') }}</option>
                        @foreach($tableCategories as $category)
                            <option value="{{ $category->id }}" @selected((string) old('table_category_id', $selectedCategoryId) === (string) $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('table_category_id')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            @endif
            <div class="mb-3">
                <label class="form-label" for="tableName">{{ __('menu.table_name') }}</label>
                <input id="tableName" name="name" class="form-control" value="{{ old('name') }}" required>
                @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label" for="tableCapacity">{{ __('menu.table_capacity_label') }}</label>
                <input id="tableCapacity" type="number" name="capacity" class="form-control" value="{{ old('capacity', 4) }}" min="1" required>
                @error('capacity')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label" for="tableStatus">{{ __('menu.table_status') }}</label>
                <select id="tableStatus" name="status" class="form-select">
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}" @selected(old('status', 'empty') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
                @error('status')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <button class="btn btn-primary">{{ __('menu.save') }}</button>
            <a href="{{ route('admin.tables.index') }}" class="btn btn-secondary">{{ __('menu.cancel') }}</a>
        </form>
    </div>
</div>
@endsection
