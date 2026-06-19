@extends('theme::layouts.app')
@section('title', 'Raporlar')
@section('page-title', 'Raporlar')
@section('content')
<div class="card mb-3"><div class="card-body">
<form method="GET" class="row g-2 align-items-end">
    <div class="col-auto"><label class="form-label">Tarih</label><input type="date" name="date" class="form-control" value="{{ $date->format('Y-m-d') }}"></div>
    <div class="col-auto"><button class="btn btn-primary">Filtrele</button></div>
</form></div></div>
<div class="row mb-3">
    <div class="col-md-4"><div class="small-box text-bg-success"><div class="inner"><h3>{{ number_format($dailyRevenue, 2) }} ₺</h3><p>Seçili Gün Ciro</p></div></div></div>
    <div class="col-md-4"><div class="small-box text-bg-warning"><div class="inner"><h3>{{ $openOrders }}</h3><p>Açık Adisyon</p></div></div></div>
</div>
<div class="card"><div class="card-header"><h5 class="mb-0">En Çok Satan Ürünler (Bu Ay)</h5></div>
<div class="card-body table-responsive p-0"><table class="table mb-0"><thead><tr><th>Ürün</th><th>Adet</th><th>Ciro</th></tr></thead>
<tbody>@forelse($topProducts as $row)<tr><td>{{ $row->product?->name }}</td><td>{{ $row->total_qty }}</td><td>{{ number_format($row->total_revenue, 2) }} ₺</td></tr>@empty<tr><td colspan="3" class="text-center text-muted">Veri yok</td></tr>@endforelse</tbody></table></div></div>
@endsection
