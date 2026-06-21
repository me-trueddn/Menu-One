<?php

namespace App\Models;

use App\Enums\OrderStatus;
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
        'status',
        'total',
        'payment_method',
        'split_count',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'total' => 'decimal:2',
            'split_count' => 'integer',
            'closed_at' => 'datetime',
        ];
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
