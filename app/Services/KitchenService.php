<?php

namespace App\Services;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Models\OrderItem;
use Illuminate\Support\Collection;

class KitchenService
{
    public function pendingItems(): Collection
    {
        return OrderItem::query()
            ->with(['product', 'order.cafeTable'])
            ->whereIn('status', [
                OrderItemStatus::Pending,
                OrderItemStatus::Preparing,
            ])
            ->whereHas('order', fn ($q) => $q
                ->whereIn('status', OrderStatus::payableValues())
                ->whereDate('created_at', today()))
            ->orderBy('created_at')
            ->get();
    }

    public function readyItems(): Collection
    {
        return OrderItem::query()
            ->with(['product', 'order.cafeTable'])
            ->where('status', OrderItemStatus::Ready)
            ->whereHas('order', fn ($q) => $q
                ->whereIn('status', OrderStatus::payableValues())
                ->whereDate('created_at', today()))
            ->orderByDesc('updated_at')
            ->limit(20)
            ->get();
    }

    public function pendingGroupedByTable(): Collection
    {
        return $this->groupItemsByTable($this->pendingItems());
    }

    public function readyGroupedByTable(): Collection
    {
        return $this->groupItemsByTable($this->readyItems());
    }

    public function updateItemStatus(OrderItem $item, OrderItemStatus $status): OrderItem
    {
        $item->update(['status' => $status]);

        return $item->fresh(['product', 'order.cafeTable']);
    }

    /** @return Collection<int, array{table_id: int, table: string, since: \Illuminate\Support\Carbon, items: Collection<int, OrderItem>}> */
    protected function groupItemsByTable(Collection $items): Collection
    {
        return $items
            ->groupBy(fn (OrderItem $item) => (string) $item->order->cafe_table_id)
            ->map(function (Collection $tableItems, string $tableId) {
                $table = $tableItems->first()->order->cafeTable;

                return [
                    'table_id' => (int) $tableId,
                    'table' => $table?->name ?? '—',
                    'since' => $tableItems->min('created_at'),
                    'items' => $tableItems->sortBy('created_at')->values(),
                ];
            })
            ->sortBy('since')
            ->values();
    }
}
