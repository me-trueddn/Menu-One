<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Collection;

class DigitalMenuCatalogService
{
    /**
     * @return Collection<int, Category>
     */
    public function categoriesForPublicMenu(): Collection
    {
        return Category::query()
            ->with(['products' => fn ($query) => $query
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->filter(fn (Category $category) => $category->products->isNotEmpty())
            ->values();
    }
}
