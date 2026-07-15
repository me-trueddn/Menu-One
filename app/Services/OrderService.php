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
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OrderService
{
    public function __construct(private AuditLogService $auditLogs) {}

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

            $this->logCafe($order, $user, 'order.opened', __('menu.log_order_opened', [
                'table' => $table->name,
                'id' => $order->id,
            ]));

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

        $this->logCafe($order, $this->actor(), 'order.item_added', __('menu.log_order_item_added', [
            'product' => $product->name,
            'qty' => $qty,
            'order_id' => $order->id,
        ]), ['product_id' => $product->id, 'qty' => $qty]);

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

        $this->logCafe($order, $this->actor(), 'order.item_removed', __('menu.log_order_item_removed', [
            'order_id' => $order->id,
        ]));
    }

    public function updateItemQty(Order $order, OrderItem $item, int $qty): OrderItem
    {
        if ($order->status === OrderStatus::Closed) {
            throw new InvalidArgumentException('Kapalı adisyonda ürün adedi güncellenemez.');
        }

        if ($order->status === OrderStatus::AwaitingPayment) {
            throw new InvalidArgumentException('Ödeme bekleyen adisyonda ürün adedi güncellenemez.');
        }

        if ($item->order_id !== $order->id) {
            throw new InvalidArgumentException('Ürün bu adisyona ait değil.');
        }

        if ($item->status !== OrderItemStatus::Pending) {
            throw new InvalidArgumentException('Mutfağa gönderilmiş veya hazırlanan ürünün adedi güncellenemez.');
        }

        if ($qty < 1 || $qty > 99) {
            throw new InvalidArgumentException('Adet 1 ile 99 arasında olmalıdır.');
        }

        return DB::transaction(function () use ($order, $item, $qty) {
            $item->update(['qty' => $qty]);
            $order->recalculateTotal();

            return $item->fresh(['product']);
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

        $this->logCafe($order, $this->actor(), 'order.sent_kitchen', __('menu.log_order_sent_kitchen', ['id' => $order->id]));

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

        $this->logCafe($order, $this->actor(), 'order.payment_requested', __('menu.log_order_payment_requested', ['id' => $order->id]));

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

            $this->logCafe($order, $this->actor(), 'order.voided', __('menu.log_order_voided', ['id' => $order->id]));

            return $order->fresh();
        });
    }

    public function recordPayment(Order $order, ?string $paymentMethod, int $splitCount, float $discountPercent): Order
    {
        return DB::transaction(function () use ($order, $paymentMethod, $splitCount, $discountPercent) {
            if ($order->splitPaidCount() > 0) {
                $splitCount = (int) $order->split_count;
                $discountPercent = $order->discountPercent();
            } else {
                $splitCount = max(0, $splitCount);
                $discountPercent = min(100, max(0, $discountPercent));

                $order->update([
                    'split_count' => $splitCount,
                    'discount_percent' => $discountPercent,
                ]);
            }

            if ($splitCount <= 0) {
                return $this->closeOrder($order, $paymentMethod, 0, $discountPercent);
            }

            $paidCount = $order->splitPaidCount() + 1;

            $order->update([
                'split_paid_count' => $paidCount,
                'payment_method' => $paymentMethod,
            ]);

            if ($paidCount >= $splitCount) {
                return $this->closeOrder($order, $paymentMethod, $splitCount, $discountPercent);
            }

            return $order->fresh(['items.product', 'cafeTable']);
        });
    }

    public function closeOrder(Order $order, ?string $paymentMethod = null, int $splitCount = 1, float $discountPercent = 0): Order
    {
        return DB::transaction(function () use ($order, $paymentMethod, $splitCount, $discountPercent) {
            $closedAt = now();
            $percent = min(100, max(0, $discountPercent));
            $finalTotal = $order->amountDue($percent);
            $resolvedSplit = max(0, $splitCount);

            $order->update([
                'status' => OrderStatus::Closed,
                'payment_method' => $paymentMethod,
                'split_count' => $resolvedSplit,
                'split_paid_count' => $resolvedSplit > 0 ? $resolvedSplit : 0,
                'discount_percent' => $percent,
                'total' => $finalTotal,
                'closed_at' => $closedAt,
            ]);
            $table = $order->cafeTable;
            app(ReservationService::class)->finalizeCheckoutForTable($table, $closedAt, $order->created_at);
            $table->update(['status' => TableStatus::Empty]);

            $this->logCafe($order, $this->actor(), 'order.closed', __('menu.log_order_closed', [
                'id' => $order->id,
                'total' => $finalTotal,
            ]), ['payment_method' => $paymentMethod]);

            return $order->fresh();
        });
    }

    private function actor(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }

    private function logCafe(Order $order, ?User $user, string $action, string $summary, array $context = []): void
    {
        $tenantId = (string) ($order->tenant_id ?? '');

        if ($tenantId === '' && function_exists('tenant') && tenant()) {
            $tenantId = (string) tenant()->getTenantKey();
        }

        $this->auditLogs->cafe($tenantId, $user, $action, $summary, $context, $order);
    }
}
