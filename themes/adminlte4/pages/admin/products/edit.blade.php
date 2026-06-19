@extends('theme::layouts.app')
@section('title', 'Ürün Düzenle')
@section('page-title', 'Ürün Düzenle')
@section('content')
<div class="card"><div class="card-body"><form method="POST" action="{{ route('admin.products.update', $product) }}">@csrf @method('PUT')
<div class="mb-3"><label class="form-label">Kategori</label><select name="category_id" class="form-select" required>@foreach($categories as $c)<option value="{{ $c->id }}" @selected($product->category_id==$c->id)>{{ $c->name }}</option>@endforeach</select></div>
<div class="mb-3"><label class="form-label">Ad</label><input name="name" class="form-control" value="{{ old('name', $product->name) }}" required></div>
<div class="mb-3"><label class="form-label">Fiyat (₺)</label><input type="number" step="0.01" name="price" class="form-control" value="{{ old('price', $product->price) }}" required></div>
<div class="form-check mb-3"><input type="checkbox" name="is_active" value="1" class="form-check-input" id="active" @checked(old('is_active', $product->is_active))><label for="active" class="form-check-label">Aktif</label></div>
<button class="btn btn-primary">Güncelle</button><a href="{{ route('admin.products.index') }}" class="btn btn-secondary">İptal</a>
</form></div></div>
@endsection
