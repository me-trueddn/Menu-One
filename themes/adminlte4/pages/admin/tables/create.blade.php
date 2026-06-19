@extends('theme::layouts.app')

@section('title', 'Masa Ekle')
@section('page-title', 'Masa Ekle')

@section('content')
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('admin.tables.store') }}">@csrf
    <div class="mb-3"><label class="form-label">Masa Adı</label><input name="name" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">Kapasite</label><input type="number" name="capacity" class="form-control" value="4" min="1" required></div>
    <div class="mb-3"><label class="form-label">Durum</label>
        <select name="status" class="form-select">@foreach($statuses as $s)<option value="{{ $s->value }}">{{ $s->label() }}</option>@endforeach</select>
    </div>
    <button class="btn btn-primary">Kaydet</button>
    <a href="{{ route('admin.tables.index') }}" class="btn btn-secondary">İptal</a>
</form></div></div>
@endsection
