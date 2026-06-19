<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TableStatus;
use App\Http\Controllers\Controller;
use App\Models\DiningTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiningTableController extends Controller
{
    public function index(): View
    {
        $tables = DiningTable::orderBy('name')->paginate(20);

        return view('theme::pages.admin.tables.index', compact('tables'));
    }

    public function create(): View
    {
        return view('theme::pages.admin.tables.create', [
            'statuses' => TableStatus::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1', 'max:50'],
            'status' => ['required', 'in:empty,occupied,reserved'],
        ]);

        DiningTable::create($validated);

        return redirect()->route('admin.tables.index')->with('success', 'Masa eklendi.');
    }

    public function edit(DiningTable $table): View
    {
        return view('theme::pages.admin.tables.edit', [
            'table' => $table,
            'statuses' => TableStatus::cases(),
        ]);
    }

    public function update(Request $request, DiningTable $table): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1', 'max:50'],
            'status' => ['required', 'in:empty,occupied,reserved'],
        ]);

        $table->update($validated);

        return redirect()->route('admin.tables.index')->with('success', 'Masa güncellendi.');
    }

    public function destroy(DiningTable $table): RedirectResponse
    {
        $table->delete();

        return redirect()->route('admin.tables.index')->with('success', 'Masa silindi.');
    }
}
