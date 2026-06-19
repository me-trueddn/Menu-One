@extends('theme::layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="small-box text-bg-success">
            <div class="inner">
                <h3>{{ number_format($dailyRevenue, 2) }} ₺</h3>
                <p>Bugünkü Ciro</p>
            </div>
            <i class="small-box-icon bi bi-currency-exchange"></i>
        </div>
    </div>
    <div class="col-md-4">
        <div class="small-box text-bg-warning">
            <div class="inner">
                <h3>{{ $openOrders }}</h3>
                <p>Açık Adisyon</p>
            </div>
            <i class="small-box-icon bi bi-receipt"></i>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h5 class="mb-0">En Çok Satan Ürünler</h5></div>
    <div class="card-body table-responsive p-0">
        <table class="table mb-0">
            <thead><tr><th>Ürün</th><th>Adet</th><th>Ciro</th></tr></thead>
            <tbody>
                @forelse($topProducts as $row)
                    <tr>
                        <td>{{ $row->product?->name ?? '-' }}</td>
                        <td>{{ $row->total_qty }}</td>
                        <td>{{ number_format($row->total_revenue, 2) }} ₺</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-muted text-center">Veri yok</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
