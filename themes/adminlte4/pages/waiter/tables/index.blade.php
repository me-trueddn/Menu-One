@extends('theme::layouts.app')
@section('title', 'Masalar')
@section('page-title', 'Garson - Masalar')
@section('content')
<div class="row">
@foreach($tables as $table)
    <div class="col-md-3 col-sm-6 mb-3">
        <a href="{{ route('waiter.tables.show', $table) }}" class="text-decoration-none">
            <div class="card h-100 border-{{ $table->status->value === 'occupied' ? 'danger' : ($table->status->value === 'reserved' ? 'warning' : 'success') }}">
                <div class="card-body text-center">
                    <h4 class="card-title">{{ $table->name }}</h4>
                    <p class="mb-1"><span class="badge {{ $table->status->badgeClass() }}">{{ $table->status->label() }}</span></p>
                    <small class="text-muted">{{ $table->capacity }} kişilik</small>
                </div>
            </div>
        </a>
    </div>
@endforeach
</div>
<div id="ready-alert" class="alert alert-info d-none"></div>
@endsection
@push('scripts')
<script>
setInterval(async () => {
    const res = await fetch('{{ route('waiter.ready-items.poll') }}');
    const data = await res.json();
    const el = document.getElementById('ready-alert');
    if (data.items.length) {
        el.classList.remove('d-none');
        el.textContent = 'Hazır siparişler: ' + data.items.map(i => i.table + ' - ' + i.product).join(', ');
    } else { el.classList.add('d-none'); }
}, 5000);
</script>
@endpush
