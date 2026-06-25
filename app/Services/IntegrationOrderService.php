<?php

namespace App\Services;

use App\DataTransferObjects\NormalizedOrderDto;
use App\Enums\IntegrationOrderStatus;
use App\Enums\IntegrationProvider;
use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\TableStatus;
use App\Models\DiningTable;
use App\Models\IntegrationProductMapping;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\TenantIntegration;
use App\Models\User;
use App\Support\IntegrationRegistry;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class IntegrationOrderService
{
    public function __construct(private OrderService $orders) {}

    public function ingestIncomingOrder(
        Tenant $tenant,
        IntegrationProvider $provider,
        NormalizedOrderDto $dto,
    ): Order {
        $existing = Order::query()
            ->where('integration_provider', $provider)
            ->where('external_order_id', $dto->externalOrderId)
            ->first();

        if ($existing) {
            return $existing->load(['items.product', 'cafeTable']);
        }

        return DB::transaction(function () use ($tenant, $provider, $dto) {
            if (! tenancy()->initialized || (string) tenant()->getTenantKey() !== (string) $tenant->getTenantKey()) {
                tenancy()->initialize($tenant);
            }

            $actor = $this->resolveActorUser($tenant);
            $table = $this->createVirtualTable($provider, $dto->externalOrderId);

            $order = Order::create([
                'cafe_table_id' => $table->id,
                'user_id' => $actor->id,
                'order_type' => OrderType::Delivery,
                'integration_provider' => $provider,
                'external_order_id' => $dto->externalOrderId,
                'integration_status' => IntegrationOrderStatus::PendingAcceptance,
                'customer_name' => $dto->customerName,
                'customer_phone' => $dto->customerPhone,
                'delivery_note' => $dto->deliveryNote,
                'integration_payload' => $dto->rawPayload,
                'payment_collected_externally' => $dto->paymentCollectedExternally,
                'status' => OrderStatus::Open,
                'total' => 0,
            ]);

            foreach ($dto->items as $line) {
                $this->addLineItem($order, $provider, $line);
            }

            $order->recalculateTotal();
            $table->update(['status' => TableStatus::Occupied]);

            TenantIntegration::forProvider($provider)?->update([
                'last_synced_at' => now(),
                'last_error' => null,
            ]);

            return $order->fresh(['items.product', 'cafeTable']);
        });
    }

    public function accept(Order $order): Order
    {
        $this->assertDeliveryOrder($order);

        if ($order->integration_status !== IntegrationOrderStatus::PendingAcceptance) {
            throw new InvalidArgumentException(__('menu.integration_invalid_status'));
        }

        return DB::transaction(function () use ($order) {
            $order->update(['integration_status' => IntegrationOrderStatus::Accepted]);

            $integration = TenantIntegration::forProvider($order->integration_provider);
            if ($integration) {
                IntegrationRegistry::adapter($order->integration_provider)
                    ->acknowledgeAccept($order, $integration);
            }

            if ($order->status === OrderStatus::Open) {
                $this->orders->sendToKitchen($order);
            }

            return $order->fresh(['items.product', 'cafeTable']);
        });
    }

    public function markPreparing(Order $order): Order
    {
        $this->assertDeliveryOrder($order);

        if (! in_array($order->integration_status, [
            IntegrationOrderStatus::Accepted,
            IntegrationOrderStatus::Preparing,
        ], true)) {
            throw new InvalidArgumentException(__('menu.integration_invalid_status'));
        }

        return DB::transaction(function () use ($order) {
            $order->update(['integration_status' => IntegrationOrderStatus::Preparing]);

            if ($order->status === OrderStatus::Open) {
                $this->orders->sendToKitchen($order);
            }

            $order->items()
                ->where('status', OrderItemStatus::Pending)
                ->update(['status' => OrderItemStatus::Preparing]);

            return $order->fresh(['items.product', 'cafeTable']);
        });
    }

    public function markReadyForCourier(Order $order): Order
    {
        $this->assertDeliveryOrder($order);

        $notReady = $order->items()
            ->whereNotIn('status', [OrderItemStatus::Ready, OrderItemStatus::Served])
            ->exists();

        if ($notReady) {
            throw new InvalidArgumentException(__('menu.integration_items_not_ready'));
        }

        return DB::transaction(function () use ($order) {
            $order->update(['integration_status' => IntegrationOrderStatus::ReadyForCourier]);

            $integration = TenantIntegration::forProvider($order->integration_provider);
            if ($integration) {
                IntegrationRegistry::adapter($order->integration_provider)
                    ->markReady($order, $integration);
            }

            return $order->fresh(['items.product', 'cafeTable']);
        });
    }

    public function handToCourier(Order $order): Order
    {
        $this->assertDeliveryOrder($order);

        if ($order->integration_status !== IntegrationOrderStatus::ReadyForCourier) {
            throw new InvalidArgumentException(__('menu.integration_invalid_status'));
        }

        return DB::transaction(function () use ($order) {
            $order->update(['integration_status' => IntegrationOrderStatus::HandedToCourier]);

            $order->items()
                ->where('status', OrderItemStatus::Ready)
                ->update(['status' => OrderItemStatus::Served]);

            $integration = TenantIntegration::forProvider($order->integration_provider);
            if ($integration) {
                IntegrationRegistry::adapter($order->integration_provider)
                    ->handToCourier($order, $integration);
            }

            if ($order->payment_collected_externally) {
                return $this->complete($order);
            }

            if ($order->status !== OrderStatus::AwaitingPayment && $order->status !== OrderStatus::Closed) {
                $this->orders->requestPayment($order);
            }

            return $order->fresh(['items.product', 'cafeTable']);
        });
    }

    public function reject(Order $order, ?string $reason = null): Order
    {
        $this->assertDeliveryOrder($order);

        if (in_array($order->integration_status, [
            IntegrationOrderStatus::Completed,
            IntegrationOrderStatus::Rejected,
            IntegrationOrderStatus::Cancelled,
        ], true)) {
            throw new InvalidArgumentException(__('menu.integration_invalid_status'));
        }

        return DB::transaction(function () use ($order, $reason) {
            $integration = TenantIntegration::forProvider($order->integration_provider);
            if ($integration) {
                IntegrationRegistry::adapter($order->integration_provider)
                    ->reject($order, $integration, $reason);
            }

            $order->update([
                'integration_status' => IntegrationOrderStatus::Rejected,
                'status' => OrderStatus::Closed,
                'closed_at' => now(),
            ]);

            $order->cafeTable?->update(['status' => TableStatus::Empty]);

            return $order->fresh(['items.product', 'cafeTable']);
        });
    }

    public function complete(Order $order): Order
    {
        $this->assertDeliveryOrder($order);

        return DB::transaction(function () use ($order) {
            $closed = $this->orders->closeOrder(
                $order,
                paymentMethod: 'platform',
                splitCount: 0,
                discountPercent: 0,
            );

            $closed->update(['integration_status' => IntegrationOrderStatus::Completed]);

            return $closed->fresh(['items.product', 'cafeTable']);
        });
    }

    /**
     * @param  array{external_id: ?string, name: string, qty: int, unit_price: float, notes: ?string}  $line
     */
    protected function addLineItem(Order $order, IntegrationProvider $provider, array $line): OrderItem
    {
        $product = null;

        if ($line['external_id']) {
            $mapping = IntegrationProductMapping::query()
                ->where('provider', $provider->value)
                ->where('external_id', $line['external_id'])
                ->first();

            $product = $mapping?->product;
        }

        if (! $product) {
            $product = Product::query()
                ->where('name', $line['name'])
                ->where('is_active', true)
                ->first();
        }

        $notes = $line['notes'];

        if (! $product) {
            $notes = trim(($notes ? $notes.' — ' : '').$line['name']);
        }

        return OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product?->id ?? $this->fallbackProductId(),
            'qty' => $line['qty'],
            'unit_price' => $product ? $product->price : $line['unit_price'],
            'status' => OrderItemStatus::Pending,
            'notes' => $notes ?: null,
        ]);
    }

    protected function fallbackProductId(): int
    {
        $product = Product::query()->where('is_active', true)->orderBy('id')->first();

        if (! $product) {
            throw new InvalidArgumentException(__('menu.integration_no_products'));
        }

        return $product->id;
    }

    protected function createVirtualTable(IntegrationProvider $provider, string $externalOrderId): DiningTable
    {
        $shortId = strlen($externalOrderId) > 8
            ? substr($externalOrderId, -8)
            : $externalOrderId;

        $name = $provider->label().' #'.$shortId;

        return DiningTable::create([
            'name' => $name,
            'capacity' => 0,
            'status' => TableStatus::Occupied,
            'is_virtual' => true,
            'integration_provider' => $provider,
        ]);
    }

    protected function resolveActorUser(Tenant $tenant): User
    {
        if ($tenant->owner_user_id) {
            $owner = User::find($tenant->owner_user_id);
            if ($owner) {
                return $owner;
            }
        }

        $admin = User::role('cafe_admin')
            ->where(function ($query) use ($tenant) {
                $query->where('tenant_id', $tenant->id)
                    ->orWhereHas('assignedTenants', fn ($q) => $q->where('tenants.id', $tenant->id))
                    ->orWhereHas('ownedTenants', fn ($q) => $q->where('tenants.id', $tenant->id));
            })
            ->first();

        if ($admin) {
            return $admin;
        }

        $staff = User::where('tenant_id', $tenant->id)->first();

        if (! $staff) {
            throw new InvalidArgumentException(__('menu.integration_no_actor_user'));
        }

        return $staff;
    }

    protected function assertDeliveryOrder(Order $order): void
    {
        if ($order->order_type !== OrderType::Delivery || ! $order->integration_provider) {
            throw new InvalidArgumentException(__('menu.integration_not_delivery_order'));
        }
    }
}
