<?php

namespace App\Support;

use App\Models\DiningTable;
use App\Models\TableCategory;
use Illuminate\Support\Collection;

class TableGrouping
{
    /**
     * @param  Collection<int, DiningTable>  $tables
     * @return array{
     *     categories: Collection<int, TableCategory>,
     *     uncategorized: Collection<int, DiningTable>
     * }
     */
    public static function forTables(Collection $tables): array
    {
        $categories = TableCategory::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $uncategorized = $tables->whereNull('table_category_id')->values();

        return compact('categories', 'uncategorized');
    }

    /**
     * @param  Collection<int, DiningTable>  $tables
     * @return Collection<int, DiningTable>
     */
    public static function tablesForCategory(Collection $tables, TableCategory $category): Collection
    {
        return $tables->where('table_category_id', $category->id)->values();
    }
}
