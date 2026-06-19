@extends('theme::layouts.app')
@section('title', 'Ürünler')
@section('page-title', 'Ürünler')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between"><h5 class="mb-0">Ürünler</h5><a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">Ekle</a></div>
    <div class="card-body table-responsive p-0">
        <table class="table mb-0"><thead><tr><th>Ad</th><th>Kategori</th><th>Fiyat</th><th>Durum</th><th class="text-end">İşlem</th></tr></thead>
        <tbody>@foreach($products as $p)<tr>
            <td>{{ $p->name }}</td><td>{{ $p->category->name }}</td><td>{{ number_format($p->price, 2) }} ₺</td>
            <td><span class="badge {{ $p->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $p->is_active ? 'Aktif' : 'Pasif' }}</span></td>
            <td class="text-end"><a href="{{ route('admin.products.edit', $p) }}" class="btn btn-sm btn-outline-primary">Düzenle</a>
            <form action="{{ route('admin.products.destroy', $p) }}" method="POST" class="d-inline" onsubmit="return confirm('Silinsin mi?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Sil</button></form></td>
        </tr>@endforeach</tbody></table>
    </div>
    @if($products->hasPages())<div class="card-footer">{{ $products->links() }}</div>@endif
</div>
@endsection
