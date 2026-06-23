@extends('theme::layouts.app')
@section('title', __('menu.tables'))
@section('page-title', __('menu.waiter').' — '.__('menu.tables'))
@section('content')
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('reservations.index') }}" class="btn btn-outline-primary btn-sm">{{ __('menu.reservations') }}</a>
</div>

@if($categories->isEmpty() && $uncategorizedTables->isEmpty())
    <p class="text-muted">{{ __('menu.no_tables') }}</p>
@else
    @foreach($categories as $category)
        @if($category->tables->isNotEmpty())
            <h5 class="mb-3 mt-2">{{ $category->name }}</h5>
            <div class="row mb-4">
                @foreach($category->tables as $table)
                    @include('theme::partials.table-card', ['table' => $table, 'readyCountsByTable' => $readyCountsByTable])
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
                @include('theme::partials.table-card', ['table' => $table, 'readyCountsByTable' => $readyCountsByTable])
            @endforeach
        </div>
    @endif
@endif

<div id="ready-alert" class="alert alert-warning d-none"></div>
@endsection
@push('scripts')
<script>
const readyCountMany = @json(trans_choice('menu.table_ready_items_count', 2, ['count' => ':count']));

const formatReadyCount = (count) => readyCountMany.replace(':count', count);

const updateTableCards = (tables) => {
    document.querySelectorAll('[data-waiter-table-card]').forEach((wrapper) => {
        const tableId = wrapper.dataset.tableId;
        const count = Number(tables[tableId] || 0);
        const card = wrapper.querySelector('[data-table-card]');
        const banner = wrapper.querySelector('[data-ready-banner]');
        const countEl = wrapper.querySelector('[data-ready-count]');
        const status = wrapper.dataset.tableStatus;

        card.classList.remove('border-warning', 'border-danger', 'border-success', 'bg-warning-subtle');

        if (count > 0) {
            card.classList.add('border-warning', 'bg-warning-subtle');
            banner.classList.remove('d-none');
            countEl.textContent = formatReadyCount(count);
            return;
        }

        banner.classList.add('d-none');
        countEl.textContent = '';

        const borderClass = status === 'occupied' ? 'border-danger' : (status === 'reserved' ? 'border-warning' : 'border-success');
        card.classList.add(borderClass);
    });
};

const pollReadyItems = async () => {
    const res = await fetch('{{ route('waiter.ready-items.poll') }}');
    const data = await res.json();

    updateTableCards(data.tables || {});

    const el = document.getElementById('ready-alert');
    if (data.items.length) {
        el.classList.remove('d-none');
        el.textContent = '{{ __('menu.ready_orders') }}: ' + data.items.map(i => i.table + ' - ' + i.product).join(', ');
    } else {
        el.classList.add('d-none');
    }
};

pollReadyItems();
setInterval(pollReadyItems, 5000);
</script>
@endpush
