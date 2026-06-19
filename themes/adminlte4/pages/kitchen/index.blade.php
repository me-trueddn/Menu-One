@extends('theme::layouts.app')
@section('title', 'Mutfak')
@section('page-title', 'Mutfak Ekranı')
@section('content')
<div id="kitchen-board" class="row"></div>
<p class="text-muted small">Otomatik yenileme: 5 saniye</p>
@endsection
@push('scripts')
<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;
async function loadKitchen() {
    const res = await fetch('{{ route('kitchen.poll') }}');
    const data = await res.json();
    const board = document.getElementById('kitchen-board');
    if (!data.items.length) {
        board.innerHTML = '<div class="col-12"><div class="alert alert-secondary">Bekleyen sipariş yok.</div></div>';
        return;
    }
    board.innerHTML = data.items.map(item => `
        <div class="col-md-4 mb-3">
            <div class="card border-warning">
                <div class="card-header"><strong>${item.table}</strong> <span class="float-end">${item.created_at}</span></div>
                <div class="card-body">
                    <h5>${item.product} x ${item.qty}</h5>
                    ${item.notes ? '<p class="text-muted">'+item.notes+'</p>' : ''}
                    <span class="badge text-bg-info">${item.status_label}</span>
                    <div class="mt-2 d-flex gap-2">
                        ${item.status === 'pending' ? `<button class="btn btn-sm btn-warning" onclick="updateStatus(${item.id}, 'preparing')">Al</button>` : ''}
                        ${item.status !== 'ready' ? `<button class="btn btn-sm btn-success" onclick="updateStatus(${item.id}, 'ready')">Hazır</button>` : ''}
                    </div>
                </div>
            </div>
        </div>`).join('');
}
async function updateStatus(id, status) {
    await fetch(`/kitchen/items/${id}/status`, {
        method: 'PATCH',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
        body: JSON.stringify({status})
    });
    loadKitchen();
}
loadKitchen();
setInterval(loadKitchen, 5000);
</script>
@endpush
