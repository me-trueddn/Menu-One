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

        if ($order->status === OrderStatus::AwaitingPayment) {
            throw new InvalidArgumentException('Ödeme bekleyen adisyona ürün eklenemez.');
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

    public function removeItem(Order $order, OrderItem $item): void
    {
        if ($order->status === OrderStatus::Closed) {
            throw new InvalidArgumentException('Kapalı adisyondan ürün çıkarılamaz.');
        }

        if ($order->status === OrderStatus::AwaitingPayment) {
            throw new InvalidArgumentException('Ödeme bekleyen adisyondan ürün çıkarılamaz.');
        }

        if ($item->order_id !== $order->id) {
            throw new InvalidArgumentException('Ürün bu adisyona ait değil.');
        }

        if ($item->status !== OrderItemStatus::Pending) {
            throw new InvalidArgumentException('Mutfağa gönderilmiş veya hazırlanan ürün çıkarılamaz.');
        }

        DB::transaction(function () use ($order, $item) {
            $item->delete();
            $order->recalculateTotal();
        });
    }

    public function sendToKitchen(Order $order): Order
    {
        if ($order->status === OrderStatus::AwaitingPayment) {
            throw new InvalidArgumentException('Ödeme bekleyen adisyona yeni sipariş gönderilemez.');
        }

        $pendingItems = $order->items()->where('status', OrderItemStatus::Pending)->count();

        if ($pendingItems === 0) {
            throw new InvalidArgumentException('Mutfağa gönderilecek bekleyen kalem yok.');
        }

        $order->update(['status' => OrderStatus::Sent]);

        return $order->fresh(['items.product', 'cafeTable']);
    }

    public function requestPayment(Order $order): Order
    {
        if ($order->status === OrderStatus::Closed) {
            throw new InvalidArgumentException('Kapalı adisyon kasiyere gönderilemez.');
        }

        if ($order->status === OrderStatus::AwaitingPayment) {
            return $order->fresh(['items.product', 'cafeTable']);
        }

        if ($order->items()->count() === 0) {
            throw new InvalidArgumentException('Boş adisyon kasiyere gönderilemez.');
        }

        $order->update(['status' => OrderStatus::AwaitingPayment]);

        return $order->fresh(['items.product', 'cafeTable']);
    }

    public function voidEmptyOrder(Order $order): Order
    {
        if ($order->status === OrderStatus::Closed) {
            throw new InvalidArgumentException('Adisyon zaten kapalı.');
        }

        if ($order->status === OrderStatus::AwaitingPayment) {
            throw new InvalidArgumentException('Ödeme bekleyen adisyon bu şekilde kapatılamaz.');
        }

        if ($order->items()->exists()) {
            throw new InvalidArgumentException('Ürün bulunan adisyon doğrudan kapatılamaz.');
        }

        return DB::transaction(function () use ($order) {
            $closedAt = now();

            $order->update([
                'status' => OrderStatus::Closed,
                'total' => 0,
                'closed_at' => $closedAt,
            ]);

            $table = $order->cafeTable;
            app(ReservationService::class)->finalizeCheckoutForTable($table, $closedAt, $order->created_at);
            $table->update(['status' => TableStatus::Empty]);

            return $order->fresh();
        });
    }

    public function closeOrder(Order $order, ?string $paymentMethod = null, int $splitCount = 1): Order
    {
        return DB::transaction(function () use ($order, $paymentMethod, $splitCount) {
            $closedAt = now();
            $order->update([
                'status' => OrderStatus::Closed,
                'payment_method' => $paymentMethod,
                'split_count' => max(0, $splitCount),
                'closed_at' => $closedAt,
            ]);
            $table = $order->cafeTable;
            app(ReservationService::class)->finalizeCheckoutForTable($table, $closedAt, $order->created_at);
            $table->update(['status' => TableStatus::Empty]);

            return $order->fresh();
        });
    }
}
