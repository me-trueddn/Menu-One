@extends('theme::layouts.app')

@section('title', __('menu.edit_license'))
@section('page-title', __('menu.edit_license'))

@section('content')
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('platform.licenses.update', $license) }}">@csrf @method('PUT')
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">{{ __('menu.name') }}</label><input name="name" class="form-control" value="{{ old('name', $license->name) }}" required></div>
    <div class="col-md-6"><label class="form-label">Slug</label><input name="slug" class="form-control" value="{{ old('slug', $license->slug) }}" required></div>
    <div class="col-md-4"><label class="form-label">{{ __('menu.duration_days') }}</label><input type="number" name="duration_days" class="form-control" value="{{ old('duration_days', $license->duration_days) }}" min="1" required></div>
    <div class="col-md-8"><label class="form-label">{{ __('menu.description') }}</label><input name="description" class="form-control" value="{{ old('description', $license->description) }}"></div>
    <div class="col-md-6"><div class="form-check"><input type="checkbox" name="is_default" value="1" class="form-check-input" id="is_default" @checked(old('is_default', $license->is_default))><label for="is_default" class="form-check-label">{{ __('menu.default_license') }}</label></div></div>
    <div class="col-md-6"><div class="form-check"><input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" @checked(old('is_active', $license->is_active))><label for="is_active" class="form-check-label">{{ __('menu.active') }}</label></div></div>
</div>
<div class="mt-3"><button class="btn btn-primary">{{ __('menu.update') }}</button> <a href="{{ route('platform.licenses.index') }}" class="btn btn-secondary">{{ __('menu.cancel') }}</a></div>
</form></div></div>
@endsection
