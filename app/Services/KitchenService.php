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
            ->whereHas('order', fn ($q) => $q->whereIn('status', OrderStatus::payableValues()))
            ->orderBy('created_at')
            ->get();
    }

    public function readyItems(): Collection
    {
        return OrderItem::query()
            ->with(['product', 'order.cafeTable'])
            ->where('status', OrderItemStatus::Ready)
            ->whereHas('order', fn ($q) => $q->whereIn('status', OrderStatus::payableValues()))
            ->orderByDesc('updated_at')
            ->limit(20)
            ->get();
    }

    public function updateItemStatus(OrderItem $item, OrderItemStatus $status): OrderItem
    {
        $item->update(['status' => $status]);

        return $item->fresh(['product', 'order.cafeTable']);
    }
}
