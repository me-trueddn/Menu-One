@extends('theme::layouts.app')
@section('title', 'Kategori Düzenle')
@section('page-title', 'Kategori Düzenle')
@section('content')
<div class="card"><div class="card-body"><form method="POST" action="{{ route('admin.categories.update', $category) }}">@csrf @method('PUT')
<div class="mb-3"><label class="form-label">Ad</label><input name="name" class="form-control" value="{{ old('name', $category->name) }}" required></div>
<div class="mb-3"><label class="form-label">Sıra</label><input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $category->sort_order) }}"></div>
<button class="btn btn-primary">Güncelle</button><a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">İptal</a>
</form></div></div>
@endsection
