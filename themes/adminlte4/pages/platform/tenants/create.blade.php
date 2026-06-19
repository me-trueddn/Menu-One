@extends('theme::layouts.app')

@section('title', __('menu.new_cafe'))
@section('page-title', __('menu.new_cafe')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('platform.tenants.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('menu.cafe_name') }}</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('menu.slug') }}</label>
                    <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}" required>
                    @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                @include('theme::partials.company-fields', ['values' => $companyDefaults])
                <div class="col-md-6">
                    <label class="form-label">{{ __('menu.cafe_logo') }}</label>
                    <input type="file" name="logo" class="form-control" accept="image/*">
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">{{ __('menu.save') }}</button>
                <a href="{{ route('platform.tenants.index') }}" class="btn btn-secondary">{{ __('menu.cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection
