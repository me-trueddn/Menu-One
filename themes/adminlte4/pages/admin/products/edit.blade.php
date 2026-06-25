@extends('theme::layouts.app')

@section('title', __('menu.edit_product'))
@section('page-title', __('menu.edit_product'))

@section('content')
<div class="card mo-product-modal">
    <div class="card-header">
        <h5 class="mb-0">{{ __('menu.edit_product') }}: {{ $product->name }}</h5>
    </div>
    <div class="card-body">
        @include('theme::partials.admin.product-form', [
            'product' => $product,
            'formAction' => route('admin.products.update', $product),
            'formMethod' => 'PUT',
            'categories' => $categories,
            'enabledIntegrationProviders' => $enabledIntegrationProviders ?? [],
            'submitLabel' => __('menu.update'),
            'showCancel' => false,
        ])
    </div>
</div>
@endsection
