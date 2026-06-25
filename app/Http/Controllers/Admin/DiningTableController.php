<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TableStatus;
use App\Http\Controllers\Controller;
use App\Models\DiningTable;
use App\Models\TableCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DiningTableController extends Controller
{
    public function index(): View
    {
        $categories = TableCategory::query()
            ->with(['tables' => fn ($query) => $query->where('is_virtual', false)->orderBy('name')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $uncategorizedTables = DiningTable::query()
            ->whereNull('table_category_id')
            ->where('is_virtual', false)
            ->orderBy('name')
            ->get();

        return view('theme::pages.admin.tables.index', compact('categories', 'uncategorizedTables'));
    }

    public function create(Request $request): View
    {
        return view('theme::pages.admin.tables.create', [
            'statuses' => TableStatus::cases(),
            'tableCategories' => $this->tableCategories(),
            'selectedCategoryId' => $request->query('table_category_id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->tableRules());

        DiningTable::create($validated);

        return redirect()
            ->route('admin.tables.index')
            ->with('success', __('menu.table_created'));
    }

    public function edit(DiningTable $table): View
    {
        return view('theme::pages.admin.tables.edit', [
            'table' => $table,
            'statuses' => TableStatus::cases(),
            'tableCategories' => $this->tableCategories(),
        ]);
    }

    public function update(Request $request, DiningTable $table): RedirectResponse
    {
        $table->update($request->validate($this->tableRules()));

        return redirect()
            ->route('admin.tables.index')
            ->with('success', __('menu.table_updated'));
    }

    public function destroy(DiningTable $table): RedirectResponse
    {
        $table->delete();

        return redirect()
            ->route('admin.tables.index')
            ->with('success', __('menu.table_deleted'));
    }

    /** @return array<string, mixed> */
    private function tableRules(): array
    {
        return [
            'table_category_id' => [
                'nullable',
                Rule::exists('table_categories', 'id')->where('tenant_id', $this->tenantId()),
            ],
            'name' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1', 'max:50'],
            'status' => ['required', 'in:empty,occupied,reserved'],
        ];
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, TableCategory> */
    private function tableCategories()
    {
        return TableCategory::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}
