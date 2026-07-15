@extends('theme::layouts.digital-menu')

@section('title', ($tenant?->name ?? config('app.name')).' — '.__('menu.digital_menu'))

@push('styles')
<style>
    .digital-menu-body {
        margin: 0;
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        color: #1a1a1a;
        background: #f8f9fa;
    }
    .dm-header {
        background: #fff;
        border-bottom: 1px solid #e9ecef;
        padding: 1rem 1.25rem;
        text-align: center;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .dm-logo {
        max-height: 56px;
        max-width: 180px;
        object-fit: contain;
        margin-bottom: 0.35rem;
    }
    .dm-cafe-name {
        font-size: 1.25rem;
        font-weight: 700;
        margin: 0;
    }
    .dm-content {
        max-width: 640px;
        margin: 0 auto;
        padding: 1rem 1rem 2.5rem;
    }
    .dm-category {
        margin-bottom: 1.75rem;
    }
    .dm-category-title {
        font-size: 1.1rem;
        font-weight: 700;
        margin: 0 0 0.75rem;
        padding-bottom: 0.35rem;
        border-bottom: 2px solid #dee2e6;
    }
    .dm-category-image {
        width: 100%;
        max-height: 180px;
        object-fit: cover;
        border-radius: 0.5rem;
        margin-bottom: 0.75rem;
    }
    .dm-product {
        display: flex;
        gap: 0.85rem;
        background: #fff;
        border-radius: 0.5rem;
        padding: 0.85rem;
        margin-bottom: 0.65rem;
        box-shadow: 0 1px 3px rgba(0,0,0,.06);
    }
    .dm-product-image {
        width: 72px;
        height: 72px;
        object-fit: cover;
        border-radius: 0.4rem;
        flex-shrink: 0;
        background: #f1f3f5;
    }
    .dm-product-body {
        flex: 1;
        min-width: 0;
    }
    .dm-product-name {
        font-weight: 600;
        font-size: 1rem;
        margin: 0 0 0.2rem;
    }
    .dm-product-price {
        font-weight: 700;
        color: #c0392b;
        font-size: 0.95rem;
        margin-bottom: 0.25rem;
    }
    .dm-product-desc {
        font-size: 0.85rem;
        color: #6c757d;
        margin: 0;
        line-height: 1.4;
    }
    .dm-empty {
        text-align: center;
        color: #6c757d;
        padding: 3rem 1rem;
    }
</style>
@endpush

@section('content')
<header class="dm-header">
    @if($logoUrl)
        <img src="{{ $logoUrl }}" alt="{{ $tenant?->name }}" class="dm-logo">
    @endif
    <h1 class="dm-cafe-name">{{ $tenant?->name ?? config('app.name') }}</h1>
</header>

<main class="dm-content">
    @forelse($categories as $category)
        <section class="dm-category">
            @if($category->imageUrl())
                <img src="{{ $category->imageUrl() }}" alt="{{ $category->name }}" class="dm-category-image">
            @endif
            <h2 class="dm-category-title">{{ $category->name }}</h2>

            @foreach($category->products as $product)
                <article class="dm-product">
                    @if($product->menuImageUrl())
                        <img src="{{ $product->menuImageUrl() }}" alt="{{ $product->name }}" class="dm-product-image">
                    @endif
                    <div class="dm-product-body">
                        <h3 class="dm-product-name">{{ $product->name }}</h3>
                        <div class="dm-product-price">{{ number_format($product->price, 2) }} ₺</div>
                        @if(filled($product->description))
                            <p class="dm-product-desc">{{ $product->description }}</p>
                        @endif
                    </div>
                </article>
            @endforeach
        </section>
    @empty
        <p class="dm-empty">{{ __('menu.digital_menu_empty') }}</p>
    @endforelse
</main>
@endsection
