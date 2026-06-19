@extends('theme::layouts.app')
@section('title', 'Kategori Ekle')
@section('page-title', 'Kategori Ekle')
@section('content')
<div class="card"><div class="card-body"><form method="POST" action="{{ route('admin.categories.store') }}">@csrf
<div class="mb-3"><label class="form-label">Ad</label><input name="name" class="form-control" required></div>
<div class="mb-3"><label class="form-label">Sıra</label><input type="number" name="sort_order" class="form-control" value="0"></div>
<button class="btn btn-primary">Kaydet</button><a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">İptal</a>
</form></div></div>
@endsection
