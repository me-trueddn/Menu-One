@extends('theme::layouts.app')
@section('title', $table->name)
@section('page-title', 'Masa: '.$table->name)
@section('content')
<div class="row">
    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Adisyon</h5>
                @if(!$activeOrder)
                    <form method="POST" action="{{ route('waiter.orders.create', $table) }}">@csrf
                        <button class="btn btn-success btn-sm">Adisyon Aç</button>
                    </form>
                @endif
            </div>
            @if($activeOrder)
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead><tr><th>Ürün</th><th>Adet</th><th>Durum</th><th>Tutar</th></tr></thead>
                    <tbody>
                    @foreach($activeOrder->items as $item)
                        <tr>
                            <td>{{ $item->product->name }}</td>
                            <td>{{ $item->qty }}</td>
                            <td><span class="badge text-bg-secondary">{{ $item->status->label() }}</span>
                                @if($item->status->value === 'ready')
                                <form method="POST" action="{{ route('waiter.orders.items.served', [$activeOrder, $item]) }}" class="d-inline">@csrf
                                    <button class="btn btn-xs btn-sm btn-outline-success">Servis</button>
                                </form>
                                @endif
                            </td>
                            <td>{{ number_format($item->lineTotal(), 2) }} ₺</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot><tr><th colspan="3">Toplam</th><th>{{ number_format($activeOrder->total, 2) }} ₺</th></tr></tfoot>
                </table>
            </div>
            <div class="card-footer d-flex gap-2">
                <form method="POST" action="{{ route('waiter.orders.send', $activeOrder) }}">@csrf
                    <button class="btn btn-warning">Mutfağa Gönder</button>
                </form>
                <form method="POST" action="{{ route('waiter.orders.close', $activeOrder) }}" onsubmit="return confirm('Adisyon kapatılsın mı?')">@csrf
                    <button class="btn btn-primary">Ödeme Al / Kapat</button>
                </form>
            </div>
            @else
            <div class="card-body text-muted">Bu masada açık adisyon yok.</div>
            @endif
        </div>
    </div>
    @if($activeOrder)
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Ürün Ekle</h5></div>
            <div class="card-body">
                @foreach(\App\Models\Category::with(['products'=>fn($q)=>$q->where('is_active',true)])->orderBy('sort_order')->get() as $category)
                    @if($category->products->count())
                    <h6>{{ $category->name }}</h6>
                    <div class="row mb-3">
                    @foreach($category->products as $product)
                        <div class="col-md-6 mb-2">
                            <form method="POST" action="{{ route('waiter.orders.items.store', $activeOrder) }}" class="border rounded p-2">@csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div><strong>{{ $product->name }}</strong><br><small>{{ number_format($product->price, 2) }} ₺</small></div>
                                    <div class="d-flex gap-1">
                                        <input type="number" name="qty" value="1" min="1" max="99" class="form-control form-control-sm" style="width:60px">
                                        <button class="btn btn-sm btn-primary">+</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endforeach
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
<a href="{{ route('waiter.tables.index') }}" class="btn btn-secondary mt-2">← Masalara Dön</a>
@endsection
