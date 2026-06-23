@extends('theme::layouts.app')

@section('title', __('menu.cashier'))

@section('page-title', __('menu.cashier_tables'))

@section('content')

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('cashier.tables.index') }}" class="row g-2 align-items-center">
            <div class="col-md-6">
                <input type="search" name="q" value="{{ $search }}" class="form-control"
                       placeholder="{{ __('menu.search_table') }}" autofocus>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary">{{ __('menu.search') }}</button>
                @if($search)
                    <a href="{{ route('cashier.tables.index') }}" class="btn btn-outline-secondary">{{ __('menu.clear') }}</a>
                @endif
            </div>
        </form>
    </div>
</div>

@if($tables->isEmpty())
    <p class="text-muted">{{ __('menu.no_tables_found') }}</p>
@else
    @foreach($categories as $category)
        @php($categoryTables = \App\Support\TableGrouping::tablesForCategory($tables, $category))
        @if($categoryTables->isNotEmpty())
            <h5 class="mb-3 mt-2">{{ $category->name }}</h5>
            <div class="row mb-4">
                @foreach($categoryTables as $table)
                    @include('theme::partials.cashier-table-card', ['table' => $table])
                @endforeach
            </div>
        @endif
    @endforeach

    @if($uncategorizedTables->isNotEmpty())
        @if($categories->isNotEmpty())
            <h5 class="mb-3 mt-2">{{ __('menu.uncategorized_tables') }}</h5>
        @endif
        <div class="row">
            @foreach($uncategorizedTables as $table)
                @include('theme::partials.cashier-table-card', ['table' => $table])
            @endforeach
        </div>
    @endif
@endif

@endsection
