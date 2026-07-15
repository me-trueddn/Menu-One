<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\ImageStorage;
use App\Support\MediaLimits;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::orderBy('sort_order')->paginate(20);

        return view('theme::pages.admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('theme::pages.admin.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'image' => MediaLimits::imageRules(MediaLimits::CONTEXT_PRODUCT),
        ]);

        $category = Category::create([
            'name' => $validated['name'],
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        $this->storeImage($request, $category);

        return redirect()->route('admin.categories.index')->with('success', __('menu.category_created'));
    }

    public function edit(Category $category): View
    {
        return view('theme::pages.admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'image' => MediaLimits::imageRules(MediaLimits::CONTEXT_PRODUCT),
            'remove_image' => ['boolean'],
        ]);

        $category->update([
            'name' => $validated['name'],
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        if ($request->boolean('remove_image')) {
            ImageStorage::delete($category->image_path);
            $category->update(['image_path' => null]);
        }

        $this->storeImage($request, $category);

        return redirect()->route('admin.categories.index')->with('success', __('menu.category_updated'));
    }

    public function destroy(Category $category): RedirectResponse
    {
        ImageStorage::delete($category->image_path);
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', __('menu.category_deleted'));
    }

    protected function storeImage(Request $request, Category $category): void
    {
        if (! $request->hasFile('image')) {
            return;
        }

        ImageStorage::delete($category->image_path);

        $category->update([
            'image_path' => ImageStorage::storeCategoryFile($request->file('image'), (string) tenant()->getTenantKey()),
        ]);
    }
}
