@extends('theme::layouts.app')
@section('title', __('menu.add_category'))
@section('page-title', __('menu.add_category'))
@section('content')
<div class="card"><div class="card-body"><form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data">@csrf
<div class="mb-3"><label class="form-label">{{ __('menu.name') }}</label><input name="name" class="form-control" value="{{ old('name') }}" required></div>
<div class="mb-3"><label class="form-label">{{ __('menu.sort_order') }}</label><input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}" min="0"></div>
<div class="mb-3">
    <label class="form-label">{{ __('menu.category_image') }}</label>
    <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
    @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
    <div class="form-text">{{ __('menu.category_image_hint') }}</div>
</div>
<button class="btn btn-primary">{{ __('menu.save') }}</button><a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">{{ __('menu.cancel') }}</a>
</form></div></div>
@endsection
