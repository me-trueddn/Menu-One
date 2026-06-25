@extends('theme::layouts.app')
@section('title', __('menu.kitchen'))
@section('page-title', __('menu.kitchen'))
@section('content')
<div id="kitchen-board" class="row g-3"></div>
<p class="text-muted small mb-0">{{ __('menu.kitchen_auto_refresh') }}</p>
@endsection
@push('scripts')
<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;
const i18n = {
    empty: @json(__('menu.no_kitchen_items')),
    take: @json(__('menu.kitchen_take')),
    ready: @json(__('menu.kitchen_mark_ready')),
};

function renderItemActions(item) {
    const actions = [];
    if (item.status === 'pending') {
        actions.push(`<button type="button" class="btn btn-sm btn-warning" onclick="updateStatus(${item.id}, 'preparing')">${i18n.take}</button>`);
    }
    if (item.status !== 'ready') {
        actions.push(`<button type="button" class="btn btn-sm btn-success" onclick="updateStatus(${item.id}, 'ready')">${i18n.ready}</button>`);
    }
    return actions.join('');
}

function renderTableCard(group) {
    const itemsHtml = group.items.map(item => `
        <li class="list-group-item">
            <div class="d-flex justify-content-between align-items-start gap-2">
                <div>
                    <div class="fw-semibold">${item.product} <span class="text-muted">× ${item.qty}</span></div>
                    ${item.notes ? `<div class="small text-muted">${item.notes}</div>` : ''}
                    <span class="badge text-bg-info mt-1">${item.status_label}</span>
                    <span class="small text-muted ms-2">${item.created_at}</span>
                </div>
                <div class="d-flex flex-column gap-1">${renderItemActions(item)}</div>
            </div>
        </li>
    `).join('');

    return `
        <div class="col-md-6 col-xl-4">
            <div class="card border-warning h-100 shadow-sm">
                <div class="card-header bg-warning-subtle d-flex justify-content-between align-items-center">
                    <div>
                        <strong class="fs-5">${group.table}</strong>
                        ${group.integration_provider ? `<span class="badge text-bg-primary ms-1">${group.integration_provider}</span>` : ''}
                    </div>
                    <span class="badge text-bg-warning">${group.items.length}</span>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">${itemsHtml}</ul>
                </div>
                <div class="card-footer small text-muted">{{ __('menu.kitchen_since') }}: ${group.since}</div>
            </div>
        </div>
    `;
}

async function loadKitchen() {
    const res = await fetch(@json(route('kitchen.poll')));
    const data = await res.json();
    const board = document.getElementById('kitchen-board');

    if (!data.tables.length) {
        board.innerHTML = `<div class="col-12"><div class="alert alert-secondary mb-0">${i18n.empty}</div></div>`;
        return;
    }

    board.innerHTML = data.tables.map(renderTableCard).join('');
}

async function updateStatus(id, status) {
    await fetch(`/kitchen/items/${id}/status`, {
        method: 'PATCH',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'},
        body: JSON.stringify({status}),
    });
    loadKitchen();
}

loadKitchen();
setInterval(loadKitchen, 5000);
</script>
@endpush
