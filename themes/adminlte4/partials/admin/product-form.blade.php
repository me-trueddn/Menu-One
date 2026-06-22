@php
    $product ??= null;
    $isEdit = $product !== null;
    $extras = old('extras', $product?->extras ?? []);
    $imageUrl = $product?->image_path ? \App\Support\ImageStorage::url($product->image_path) : null;
@endphp

@push('styles')
<style>
    .mo-product-modal .nav-tabs .nav-link {
        border: none;
        border-bottom: 2px solid transparent;
        color: var(--bs-secondary-color);
        font-weight: 500;
        padding: 0.75rem 1rem;
    }
    .mo-product-modal .nav-tabs .nav-link.active {
        color: var(--bs-danger);
        border-bottom-color: var(--bs-danger);
        background: transparent;
    }
    .mo-product-image-box {
        width: 140px;
        height: 140px;
        border: 1px dashed var(--bs-border-color);
        border-radius: 0.5rem;
        background: var(--bs-tertiary-bg);
        position: relative;
        overflow: hidden;
    }
    .mo-product-image-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .mo-product-image-box .mo-upload-btn {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        border-radius: 0;
        font-size: 0.75rem;
    }
    .mo-product-image-box .mo-upload-icon {
        position: absolute;
        top: 0.35rem;
        right: 0.35rem;
        width: 1.75rem;
        height: 1.75rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--bs-success);
        color: #fff;
        font-size: 0.85rem;
        pointer-events: none;
    }
    .mo-field-label {
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--bs-secondary-color);
        margin-bottom: 0.35rem;
    }
</style>
@endpush

<form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" id="productForm">
    @csrf
    @if(($formMethod ?? 'POST') !== 'POST')
        @method($formMethod)
    @endif

    <ul class="nav nav-tabs mo-product-tabs border-0 px-1" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="product-tab-general-btn" data-bs-toggle="tab"
                    data-bs-target="#product-tab-general" type="button" role="tab">
                {{ __('menu.product_tab_general') }}
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="product-tab-extra-btn" data-bs-toggle="tab"
                    data-bs-target="#product-tab-extra" type="button" role="tab">
                {{ __('menu.product_tab_extra') }}
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="product-tab-options-btn" data-bs-toggle="tab"
                    data-bs-target="#product-tab-options" type="button" role="tab">
                {{ __('menu.product_tab_options') }}
            </button>
        </li>
    </ul>

    <div class="tab-content pt-3">
        <div class="tab-pane fade show active" id="product-tab-general" role="tabpanel">
            <div class="row g-3">
                <div class="col-md-auto">
                    <div class="mo-product-image-box">
                        <span class="mo-upload-icon"><i class="bi bi-upload"></i></span>
                        @if($imageUrl)
                            <img src="{{ $imageUrl }}" alt="" id="productImagePreview">
                        @else
                            <img src="{{ \App\Support\Branding::defaultLogoUrl() }}" alt="" id="productImagePreview" class="opacity-25 p-3">
                        @endif
                        <label class="btn btn-success btn-sm mo-upload-btn mb-0">
                            {{ __('menu.product_select_image') }}
                            <input type="file" name="image" class="d-none" accept="image/*" id="productImageInput">
                        </label>
                    </div>
                </div>
                <div class="col-md">
                    <div class="mb-3">
                        <div class="mo-field-label">{{ __('menu.product_name') }} <span class="text-danger">*</span></div>
                        <input type="text" name="name" id="productName" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $product?->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-0">
                        <div class="mo-field-label">{{ __('menu.product_description') }}</div>
                        <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $product?->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-md-6">
                    <div class="mo-field-label">{{ __('menu.product_code') }}</div>
                    <div class="input-group">
                        <input type="text" name="code" id="productCode" minlength="3" maxlength="50"
                               class="form-control @error('code') is-invalid @enderror"
                               value="{{ old('code', $product?->code) }}"
                               placeholder="{{ __('menu.product_code_hint') }}">
                        <button type="button" class="btn btn-outline-secondary" id="productGenerateCode" title="{{ __('menu.product_generate_code') }}">
                            <i class="bi bi-code-slash"></i>
                        </button>
                        @error('code')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mo-field-label">{{ __('menu.product_barcode') }}</div>
                    <div class="input-group">
                        <input type="text" name="barcode" class="form-control @error('barcode') is-invalid @enderror"
                               value="{{ old('barcode', $product?->barcode) }}">
                        <select name="unit_type" class="form-select @error('unit_type') is-invalid @enderror" style="max-width:8rem">
                            @foreach(\App\Models\Product::unitTypes() as $value => $label)
                                <option value="{{ $value }}" @selected(old('unit_type', $product?->unit_type ?? 'piece') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('barcode')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mo-field-label">{{ __('menu.product_sale_price') }} <span class="text-danger">*</span></div>
                    <input type="number" step="0.01" min="0" name="price" class="form-control @error('price') is-invalid @enderror"
                           value="{{ old('price', $product?->price) }}" required>
                    @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <div class="mo-field-label">{{ __('menu.product_purchase_price') }}</div>
                    <input type="number" step="0.01" min="0" name="purchase_price" class="form-control @error('purchase_price') is-invalid @enderror"
                           value="{{ old('purchase_price', $product?->purchase_price) }}">
                    @error('purchase_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <div class="mo-field-label">{{ __('menu.product_vat') }}</div>
                    <select name="vat_rate" class="form-select @error('vat_rate') is-invalid @enderror">
                        @foreach([0, 1, 8, 10, 20] as $rate)
                            <option value="{{ $rate }}" @selected((int) old('vat_rate', $product?->vat_rate ?? 10) === $rate)>{{ $rate }}%</option>
                        @endforeach
                    </select>
                    @error('vat_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <div class="mo-field-label">{{ __('menu.product_unit_type') }}</div>
                    <select class="form-select" disabled>
                        <option>{{ __('menu.product_unit_piece') }}</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="mo-field-label">{{ __('menu.product_group') }}</div>
                    <input type="text" class="form-control" disabled placeholder="{{ __('menu.product_group_placeholder') }}">
                </div>
                <div class="col-md-4">
                    <div class="mo-field-label">{{ __('menu.product_categories') }} <span class="text-danger">*</span></div>
                    <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                        <option value="">—</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) old('category_id', $product?->category_id) === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <div class="mo-field-label">{{ __('menu.product_printers') }}</div>
                    <input type="text" class="form-control" disabled placeholder="{{ __('menu.product_printers_placeholder') }}">
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="productIsActive"
                               @checked(old('is_active', $product?->is_active ?? true))>
                        <label class="form-check-label" for="productIsActive">{{ __('menu.product_active') }}</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="product-tab-extra" role="tabpanel">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="mo-field-label">{{ __('menu.product_cooking_time') }}</div>
                    <input type="text" name="extras[cooking_time]" class="form-control"
                           value="{{ old('extras.cooking_time', $extras['cooking_time'] ?? '') }}">
                </div>
                <div class="col-md-6">
                    <div class="mo-field-label">{{ __('menu.product_calories') }}</div>
                    <input type="text" name="extras[calories]" class="form-control"
                           value="{{ old('extras.calories', $extras['calories'] ?? '') }}">
                </div>
                <div class="col-12">
                    <div class="mo-field-label">{{ __('menu.product_carbon_footprint') }}</div>
                    <input type="text" name="extras[carbon_footprint]" class="form-control"
                           value="{{ old('extras.carbon_footprint', $extras['carbon_footprint'] ?? '') }}">
                </div>
                <div class="col-12">
                    <div class="mo-field-label">{{ __('menu.product_label') }}</div>
                    <input type="text" name="extras[label]" class="form-control"
                           value="{{ old('extras.label', $extras['label'] ?? '') }}">
                </div>
                <div class="col-12">
                    <div class="mo-field-label">{{ __('menu.product_allergens') }}</div>
                    <input type="text" name="extras[allergens]" class="form-control"
                           value="{{ old('extras.allergens', $extras['allergens'] ?? '') }}">
                </div>
                <div class="col-12">
                    <div class="mo-field-label">{{ __('menu.product_application_exceptions') }}</div>
                    <input type="text" name="extras[application_exceptions]" class="form-control"
                           value="{{ old('extras.application_exceptions', $extras['application_exceptions'] ?? '') }}">
                </div>
                <div class="col-12">
                    <div class="mo-field-label">{{ __('menu.product_extra_barcodes') }}</div>
                    <input type="text" name="extras[extra_barcodes]" class="form-control"
                           value="{{ old('extras.extra_barcodes', $extras['extra_barcodes'] ?? '') }}">
                </div>
                <div class="col-12">
                    <div class="mo-field-label">{{ __('menu.product_video') }}</div>
                    <div class="input-group">
                        <input type="text" name="extras[video_url]" class="form-control"
                               value="{{ old('extras.video_url', $extras['video_url'] ?? '') }}">
                        <span class="input-group-text"><i class="bi bi-camera-video"></i></span>
                    </div>
                </div>
                <div class="col-12">
                    <div class="mo-field-label">{{ __('menu.product_extra_images') }}</div>
                    <div class="input-group">
                        <input type="text" name="extras[extra_images]" class="form-control"
                               value="{{ old('extras.extra_images', $extras['extra_images'] ?? '') }}">
                        <span class="input-group-text"><i class="bi bi-images"></i></span>
                    </div>
                </div>
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center border rounded px-3 py-2">
                        <span class="mo-field-label mb-0">{{ __('menu.product_splittable') }}</span>
                        <div class="form-check form-switch mb-0">
                            <input type="checkbox" name="extras[is_splittable]" value="1" class="form-check-input" id="productSplittable"
                                   @checked(old('extras.is_splittable', $extras['is_splittable'] ?? false))>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="product-tab-options" role="tabpanel">
            <p class="text-muted">{{ __('menu.product_options_hint') }}</p>
            <div class="mo-field-label">{{ __('menu.product_options_note') }}</div>
            <textarea name="extras[options_note]" rows="4" class="form-control">{{ old('extras.options_note', $extras['options_note'] ?? '') }}</textarea>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
        @if(!empty($showCancel) && $showCancel)
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('menu.cancel') }}</button>
        @else
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">{{ __('menu.cancel') }}</a>
        @endif
        <button type="submit" class="btn btn-success px-4">{{ $submitLabel ?? __('menu.save') }}</button>
    </div>
</form>

@once
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const nameInput = document.getElementById('productName');
            const codeInput = document.getElementById('productCode');
            const generateBtn = document.getElementById('productGenerateCode');
            const imageInput = document.getElementById('productImageInput');
            const imagePreview = document.getElementById('productImagePreview');

            generateBtn?.addEventListener('click', function () {
                const name = (nameInput?.value || '').trim();
                if (!name) return;
                const slug = name
                    .toLocaleLowerCase('tr-TR')
                    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '')
                    .slice(0, 20);
                const suffix = String(Date.now()).slice(-4);
                codeInput.value = (slug || 'prd') + '-' + suffix;
            });

            imageInput?.addEventListener('change', function () {
                const file = this.files?.[0];
                if (!file || !imagePreview) return;
                imagePreview.src = URL.createObjectURL(file);
                imagePreview.classList.remove('opacity-25', 'p-3');
            });

            @if($errors->any())
                const errorTab = @json(
                    $errors->has('category_id') || $errors->has('name') || $errors->has('price') || $errors->has('code')
                        ? 'product-tab-general-btn'
                        : ($errors->has('extras') ? 'product-tab-extra-btn' : 'product-tab-general-btn')
                );
                document.getElementById(errorTab)?.click();
            @endif
        });
    </script>
    @endpush
@endonce
