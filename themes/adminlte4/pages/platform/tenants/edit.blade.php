@extends('theme::layouts.app')

@section('title', 'Cafe Düzenle')
@section('page-title', 'Cafe Düzenle')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('platform.tenants.update', $tenant) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Cafe Adı</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $tenant->name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" class="form-control" value="{{ old('slug', $tenant->slug) }}" required>
                </div>
                <div class="col-md-6">
                    <div class="form-check mt-4">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ old('is_active', $tenant->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Aktif</label>
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Güncelle</button>
                <a href="{{ route('platform.tenants.index') }}" class="btn btn-secondary">İptal</a>
            </div>
        </form>
    </div>
</div>
@endsection
