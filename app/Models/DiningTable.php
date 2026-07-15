<?php

namespace App\Models;

use App\Enums\IntegrationProvider;
use App\Enums\OrderStatus;
use App\Enums\ReservationStatus;
use App\Enums\TableStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class DiningTable extends Model
{
    use BelongsToTenant;

    protected $table = 'cafe_tables';

    protected $fillable = [
        'tenant_id',
        'table_category_id',
        'name',
        'capacity',
        'status',
        'is_virtual',
        'integration_provider',
    ];

    protected function casts(): array
    {
        return [
            'status' => TableStatus::class,
            'is_virtual' => 'boolean',
            'integration_provider' => IntegrationProvider::class,
        ];
    }

    public function isVirtual(): bool
    {
        return (bool) $this->is_virtual;
    }

    public function tableCategory(): BelongsTo
    {
        return $this->belongsTo(TableCategory::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'cafe_table_id');
    }

    public function payableOrder(): HasOne
    {
        // No "today only" filter: overnight open / awaiting_payment bills
        // must stay visible for waiter + cashier until closed.
        return $this->hasOne(Order::class, 'cafe_table_id')
            ->whereIn('status', OrderStatus::payableValues())
            ->latestOfMany();
    }

    public function activeOrder(): ?Order
    {
        if ($this->relationLoaded('payableOrder')) {
            return $this->payableOrder;
        }

        return $this->payableOrder()->first();
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(TableReservation::class, 'cafe_table_id');
    }

    public function upcomingReservations(): HasMany
    {
        return $this->reservations()
            ->where('status', ReservationStatus::Active)
            ->whereDate('starts_at', today())
            ->where('ends_at', '>=', now())
            ->orderBy('starts_at');
    }

    public function currentReservation(): ?TableReservation
    {
        if ($this->relationLoaded('upcomingReservations')) {
            return $this->upcomingReservations->first(fn (TableReservation $r) => $r->isCurrent());
        }

        return $this->upcomingReservations()->get()->first(fn (TableReservation $r) => $r->isCurrent());
    }

    public function nextReservation(): ?TableReservation
    {
        if ($this->relationLoaded('upcomingReservations')) {
            return $this->upcomingReservations->first();
        }

        return $this->upcomingReservations()->first();
    }

    public function displayStatus(): TableStatus
    {
        if ($this->activeOrder()) {
            return TableStatus::Occupied;
        }

        if ($this->currentReservation()) {
            return TableStatus::Reserved;
        }

        return $this->status;
    }
}
