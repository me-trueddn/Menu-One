@extends('theme::layouts.app')

@section('title', 'Masa Düzenle')
@section('page-title', 'Masa Düzenle')

@section('content')
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('admin.tables.update', $table) }}">@csrf @method('PUT')
    <div class="mb-3"><label class="form-label">Masa Adı</label><input name="name" class="form-control" value="{{ old('name', $table->name) }}" required></div>
    <div class="mb-3"><label class="form-label">Kapasite</label><input type="number" name="capacity" class="form-control" value="{{ old('capacity', $table->capacity) }}" min="1" required></div>
    <div class="mb-3"><label class="form-label">Durum</label>
        <select name="status" class="form-select">@foreach($statuses as $s)<option value="{{ $s->value }}" @selected(old('status', $table->status->value)===$s->value)>{{ $s->label() }}</option>@endforeach</select>
    </div>
    <button class="btn btn-primary">Güncelle</button>
    <a href="{{ route('admin.tables.index') }}" class="btn btn-secondary">İptal</a>
</form></div></div>
@endsection
