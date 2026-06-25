<div class="modal fade mo-product-modal" id="productFormModal" tabindex="-1" aria-labelledby="productFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="productFormModalLabel">{{ __('menu.add_product') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('menu.cancel') }}"></button>
            </div>
            <div class="modal-body pt-2">
                @include('theme::partials.admin.product-form', [
                    'product' => null,
                    'formAction' => route('admin.products.store'),
                    'formMethod' => 'POST',
                    'categories' => $categories,
                    'enabledIntegrationProviders' => $enabledIntegrationProviders ?? [],
                    'submitLabel' => __('menu.save'),
                    'showCancel' => true,
                ])
            </div>
        </div>
    </div>
</div>

@if(session('open_product_modal') || ($errors->any() && !request()->routeIs('admin.products.edit')))
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modalEl = document.getElementById('productFormModal');
            if (modalEl) {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
        });
    </script>
    @endpush
@endif
