<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class TableReservation extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'cafe_table_id',
        'user_id',
        'guest_name',
        'guest_phone',
        'party_size',
        'starts_at',
        'ends_at',
        'scheduled_ends_at',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'party_size' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'scheduled_ends_at' => 'datetime',
            'status' => ReservationStatus::class,
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

    public function isCurrent(): bool
    {
        $now = now();

        return $this->status === ReservationStatus::Active
            && $this->starts_at <= $now
            && $this->ends_at >= $now;
    }

    public function isUpcoming(): bool
    {
        return $this->status === ReservationStatus::Active && $this->starts_at > now();
    }

    public function leftEarly(): bool
    {
        return $this->scheduled_ends_at !== null;
    }

    public function guestPhoneDial(): ?string
    {
        if (! $this->guest_phone) {
            return null;
        }

        $digits = preg_replace('/[^\d+]/', '', $this->guest_phone);

        return $digits !== '' ? $digits : null;
    }

    /** @param Builder<self> $query */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ReservationStatus::Active);
    }

    /** @param Builder<self> $query */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->active()->where('ends_at', '>=', now())->orderBy('starts_at');
    }
}
