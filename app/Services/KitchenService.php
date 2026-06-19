<?php

namespace App\Services;

use App\Enums\OrderItemStatus;
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
            ->whereHas('order', fn ($q) => $q->whereIn('status', ['open', 'sent']))
            ->orderBy('created_at')
            ->get();
    }

    public function updateItemStatus(OrderItem $item, OrderItemStatus $status): OrderItem
    {
        $item->update(['status' => $status]);

        return $item->fresh(['product', 'order.cafeTable']);
    }
}
