@extends('theme::layouts.app')

@section('title', __('menu.new_license'))
@section('page-title', __('menu.new_license'))

@section('content')
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('platform.licenses.store') }}">@csrf
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">{{ __('menu.name') }}</label><input name="name" class="form-control" required></div>
    <div class="col-md-6"><label class="form-label">Slug</label><input name="slug" class="form-control" required></div>
    <div class="col-md-4"><label class="form-label">{{ __('menu.duration_days') }}</label><input type="number" name="duration_days" class="form-control" value="30" min="1" required></div>
    <div class="col-md-8"><label class="form-label">{{ __('menu.description') }}</label><input name="description" class="form-control"></div>
    <div class="col-md-6"><div class="form-check"><input type="checkbox" name="is_default" value="1" class="form-check-input" id="is_default"><label for="is_default" class="form-check-label">{{ __('menu.default_license') }}</label></div></div>
    <div class="col-md-6"><div class="form-check"><input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" checked><label for="is_active" class="form-check-label">{{ __('menu.active') }}</label></div></div>
</div>
<div class="mt-3"><button class="btn btn-primary">{{ __('menu.save') }}</button> <a href="{{ route('platform.licenses.index') }}" class="btn btn-secondary">{{ __('menu.cancel') }}</a></div>
</form></div></div>
@endsection
