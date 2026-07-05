@extends('theme::layouts.app')
@section('title', __('menu.edit_category'))
@section('page-title', __('menu.edit_category'))
@section('content')
<div class="card"><div class="card-body"><form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data">@csrf @method('PUT')
<div class="mb-3"><label class="form-label">{{ __('menu.name') }}</label><input name="name" class="form-control" value="{{ old('name', $category->name) }}" required></div>
<div class="mb-3"><label class="form-label">{{ __('menu.sort_order') }}</label><input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $category->sort_order) }}" min="0"></div>
<div class="mb-3">
    <label class="form-label">{{ __('menu.category_image') }}</label>
    @if($category->imageUrl())
        <div class="mb-2"><img src="{{ $category->imageUrl() }}" alt="" style="max-height:120px;border-radius:.5rem"></div>
        <div class="form-check mb-2">
            <input type="checkbox" name="remove_image" value="1" class="form-check-input" id="remove_image">
            <label class="form-check-label" for="remove_image">{{ __('menu.remove_image') }}</label>
        </div>
    @endif
    <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
    @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
    <div class="form-text">{{ __('menu.category_image_hint') }}</div>
</div>
<button class="btn btn-primary">{{ __('menu.update') }}</button><a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">{{ __('menu.cancel') }}</a>
</form></div></div>
@endsection
