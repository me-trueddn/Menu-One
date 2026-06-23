<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TableCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TableCategoryController extends Controller
{
    public function create(): View
    {
        return view('theme::pages.admin.table-categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('table_categories', 'name')->where('tenant_id', $this->tenantId()),
            ],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        TableCategory::create([
            'name' => $validated['name'],
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()
            ->route('admin.tables.index')
            ->with('success', __('menu.table_category_created'));
    }

    public function edit(TableCategory $tableCategory): View
    {
        return view('theme::pages.admin.table-categories.edit', [
            'tableCategory' => $tableCategory,
        ]);
    }

    public function update(Request $request, TableCategory $tableCategory): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('table_categories', 'name')
                    ->where('tenant_id', $this->tenantId())
                    ->ignore($tableCategory->id),
            ],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $tableCategory->update([
            'name' => $validated['name'],
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()
            ->route('admin.tables.index')
            ->with('success', __('menu.table_category_updated'));
    }

    public function destroy(TableCategory $tableCategory): RedirectResponse
    {
        $tableCategory->delete();

        return redirect()
            ->route('admin.tables.index')
            ->with('success', __('menu.table_category_deleted'));
    }
}
