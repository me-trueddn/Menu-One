<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Support\ImageStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $query = Product::query()->with('category')->orderBy('name');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('code', 'like', '%'.$search.'%')
                    ->orWhere('barcode', 'like', '%'.$search.'%')
                    ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', '%'.$search.'%'));
            });
        }

        $products = $query->paginate(20)->withQueryString();
        $categories = Category::orderBy('sort_order')->get();

        return view('theme::pages.admin.products.index', compact('products', 'categories', 'search'));
    }

    public function create(): RedirectResponse
    {
        return redirect()
            ->route('admin.products.index')
            ->with('open_product_modal', true);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProduct($request);

        $product = Product::create($this->productAttributes($request, $validated));

        $this->storeImage($request, $product);

        return redirect()
            ->route('admin.products.index')
            ->with('success', __('menu.product_created'));
    }

    public function edit(Product $product): View
    {
        $categories = Category::orderBy('sort_order')->get();

        return view('theme::pages.admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validateProduct($request, $product);

        $product->update($this->productAttributes($request, $validated, $product));

        $this->storeImage($request, $product);

        return redirect()
            ->route('admin.products.index')
            ->with('success', __('menu.product_updated'));
    }

    public function destroy(Product $product): RedirectResponse
    {
        ImageStorage::delete($product->image_path);
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', __('menu.product_deleted'));
    }

    /** @return array<string, mixed> */
    protected function validateProduct(Request $request, ?Product $product = null): array
    {
        $tenantId = $this->tenantId();

        return $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'nullable',
                'string',
                'min:3',
                'max:50',
                Rule::unique('products', 'code')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($product?->id),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'unit_type' => ['required', 'string', Rule::in(array_keys(Product::unitTypes()))],
            'price' => ['required', 'numeric', 'min:0'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'vat_rate' => ['required', 'integer', 'min:0', 'max:100'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
            'extras' => ['nullable', 'array'],
            'extras.cooking_time' => ['nullable', 'string', 'max:100'],
            'extras.calories' => ['nullable', 'string', 'max:100'],
            'extras.carbon_footprint' => ['nullable', 'string', 'max:255'],
            'extras.label' => ['nullable', 'string', 'max:255'],
            'extras.allergens' => ['nullable', 'string', 'max:1000'],
            'extras.application_exceptions' => ['nullable', 'string', 'max:1000'],
            'extras.extra_barcodes' => ['nullable', 'string', 'max:1000'],
            'extras.video_url' => ['nullable', 'string', 'max:500'],
            'extras.extra_images' => ['nullable', 'string', 'max:1000'],
            'extras.is_splittable' => ['boolean'],
            'extras.options_note' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    /** @param  array<string, mixed>  $validated */
    protected function productAttributes(Request $request, array $validated, ?Product $product = null): array
    {
        return [
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'description' => $validated['description'] ?? null,
            'barcode' => $validated['barcode'] ?? null,
            'unit_type' => $validated['unit_type'],
            'price' => $validated['price'],
            'purchase_price' => $validated['purchase_price'] ?? null,
            'vat_rate' => $validated['vat_rate'],
            'extras' => $this->extrasFromRequest($request),
            'is_active' => $request->boolean('is_active', $product ? $product->is_active : true),
        ];
    }

    protected function extrasFromRequest(Request $request): array
    {
        $extras = $request->input('extras', []);

        return [
            'cooking_time' => $extras['cooking_time'] ?? null,
            'calories' => $extras['calories'] ?? null,
            'carbon_footprint' => $extras['carbon_footprint'] ?? null,
            'label' => $extras['label'] ?? null,
            'allergens' => $extras['allergens'] ?? null,
            'application_exceptions' => $extras['application_exceptions'] ?? null,
            'extra_barcodes' => $extras['extra_barcodes'] ?? null,
            'video_url' => $extras['video_url'] ?? null,
            'extra_images' => $extras['extra_images'] ?? null,
            'is_splittable' => $request->boolean('extras.is_splittable'),
            'options_note' => $extras['options_note'] ?? null,
        ];
    }

    protected function storeImage(Request $request, Product $product): void
    {
        if (! $request->hasFile('image')) {
            return;
        }

        ImageStorage::delete($product->image_path);

        $product->update([
            'image_path' => ImageStorage::storeProductFile($request->file('image'), $this->tenantId()),
        ]);
    }
}
