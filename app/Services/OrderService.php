<?php

namespace App\Services;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Enums\TableStatus;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OrderService
{
    public function openOrder(DiningTable $table, User $user): Order
    {
        if ($table->activeOrder()) {
            throw new InvalidArgumentException('Bu masada zaten açık bir adisyon var.');
        }

        return DB::transaction(function () use ($table, $user) {
            $order = Order::create([
                'cafe_table_id' => $table->id,
                'user_id' => $user->id,
                'status' => OrderStatus::Open,
                'total' => 0,
            ]);

            $table->update(['status' => TableStatus::Occupied]);

            return $order;
        });
    }

    public function addItem(Order $order, Product $product, int $qty = 1, ?string $notes = null): OrderItem
    {
        if ($order->status === OrderStatus::Closed) {
            throw new InvalidArgumentException('Kapalı adisyona ürün eklenemez.');
        }

        if (! $product->is_active) {
            throw new InvalidArgumentException('Bu ürün aktif değil.');
        }

        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'qty' => $qty,
            'unit_price' => $product->price,
            'status' => OrderItemStatus::Pending,
            'notes' => $notes,
        ]);

        $order->recalculateTotal();

        return $item;
    }

    public function sendToKitchen(Order $order): Order
    {
        $pendingItems = $order->items()->where('status', OrderItemStatus::Pending)->count();

        if ($pendingItems === 0) {
            throw new InvalidArgumentException('Mutfağa gönderilecek bekleyen kalem yok.');
        }

        $order->update(['status' => OrderStatus::Sent]);

        return $order->fresh(['items.product', 'cafeTable']);
    }

    public function closeOrder(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            $order->update(['status' => OrderStatus::Closed]);
            $order->cafeTable->update(['status' => TableStatus::Empty]);

            return $order->fresh();
        });
    }
}
