@extends('theme::layouts.app')

@section('title', __('menu.products'))
@section('page-title', __('menu.products'))

@section('content')
<div class="card">
    <div class="card-header">
        <div class="row g-2 align-items-center">
            <div class="col-md-4 d-flex align-items-center gap-2">
                <h5 class="mb-0">{{ __('menu.products') }}</h5>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#productFormModal">
                    <i class="bi bi-plus-lg"></i> {{ __('menu.add_product') }}
                </button>
            </div>
            <div class="col-md-8">
                <form method="GET" action="{{ route('admin.products.index') }}" class="row g-2 justify-content-end">
                    <div class="col-auto flex-grow-1" style="min-width:12rem">
                        <input type="search" name="q" value="{{ $search ?? '' }}" class="form-control form-control-sm"
                               placeholder="{{ __('menu.product_search_placeholder') }}">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-outline-primary">{{ __('menu.search') }}</button>
                    </div>
                    @if(!empty($search))
                        <div class="col-auto">
                            <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('menu.clear') }}</a>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>{{ __('menu.product_name') }}</th>
                    <th>{{ __('menu.product_code') }}</th>
                    <th>{{ __('menu.product_categories') }}</th>
                    <th>{{ __('menu.product_sale_price') }}</th>
                    <th>{{ __('menu.product_status') }}</th>
                    <th class="text-end">{{ __('menu.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $listedProduct)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($url = \App\Support\ImageStorage::url($listedProduct->image_path))
                                    <img src="{{ $url }}" alt="" class="rounded" style="width:32px;height:32px;object-fit:cover">
                                @endif
                                <span>{{ $listedProduct->name }}</span>
                            </div>
                        </td>
                        <td><code>{{ $listedProduct->code ?? '—' }}</code></td>
                        <td>{{ $listedProduct->category->name }}</td>
                        <td>{{ number_format($listedProduct->price, 2) }} ₺</td>
                        <td>
                            <span class="badge {{ $listedProduct->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ $listedProduct->is_active ? __('menu.product_active') : __('menu.product_inactive') }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.products.edit', $listedProduct) }}" class="btn btn-sm btn-outline-primary">{{ __('menu.edit') }}</a>
                            <form action="{{ route('admin.products.destroy', $listedProduct) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('{{ __('menu.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">{{ __('menu.delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            {{ !empty($search) ? __('menu.no_products_found') : __('menu.no_data') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
        <div class="card-footer">{{ $products->links() }}</div>
    @endif
</div>

@include('theme::partials.admin.product-form-modal')
@endsection
