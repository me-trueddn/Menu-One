<?php

namespace App\Models;

use App\Enums\IntegrationOrderStatus;
use App\Enums\IntegrationProvider;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Order extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'cafe_table_id',
        'user_id',
        'order_type',
        'integration_provider',
        'external_order_id',
        'integration_status',
        'customer_name',
        'customer_phone',
        'delivery_note',
        'integration_payload',
        'payment_collected_externally',
        'status',
        'total',
        'discount_percent',
        'payment_method',
        'split_count',
        'split_paid_count',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'order_type' => OrderType::class,
            'integration_provider' => IntegrationProvider::class,
            'integration_status' => IntegrationOrderStatus::class,
            'integration_payload' => 'array',
            'payment_collected_externally' => 'boolean',
            'status' => OrderStatus::class,
            'total' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'split_count' => 'integer',
            'split_paid_count' => 'integer',
            'closed_at' => 'datetime',
        ];
    }

    public function isDelivery(): bool
    {
        return $this->order_type === OrderType::Delivery;
    }

    public function integrationProviderLabel(): ?string
    {
        return $this->integration_provider?->label();
    }

    public function cafeTable(): BelongsTo
    {
        return $this->belongsTo(DiningTable::class, 'cafe_table_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function recalculateTotal(): void
    {
        $total = $this->items()
            ->selectRaw('COALESCE(SUM(qty * unit_price), 0) as aggregate')
            ->value('aggregate');

        $this->update(['total' => $total ?? 0]);
    }

    public function discountPercent(): float
    {
        return (float) ($this->discount_percent ?? 0);
    }

    public function discountAmount(): float
    {
        return round((float) $this->total * $this->discountPercent() / 100, 2);
    }

    public function amountDue(?float $discountPercent = null): float
    {
        $percent = $discountPercent ?? $this->discountPercent();

        return round((float) $this->total * (1 - min(100, max(0, $percent)) / 100), 2);
    }

    public function splitPaidCount(): int
    {
        return (int) ($this->split_paid_count ?? 0);
    }

    public function isSplitPaymentInProgress(): bool
    {
        return $this->split_count > 0
            && $this->splitPaidCount() > 0
            && $this->splitPaidCount() < $this->split_count;
    }

    public function nextPaymentAmount(): float
    {
        $due = $this->amountDue();
        $split = (int) $this->split_count;

        if ($split <= 0) {
            return $due;
        }

        $paid = $this->splitPaidCount();
        $perPerson = round($due / $split, 2);

        if ($paid >= $split - 1) {
            return round($due - $perPerson * max(0, $split - 1), 2);
        }

        return $perPerson;
    }

    public function perPersonTotal(): float
    {
        $total = (float) $this->total;
        $split = (int) $this->split_count;

        if ($split <= 0) {
            return round($total, 2);
        }

        return round($total / $split, 2);
    }
}
