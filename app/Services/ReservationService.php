<?php

namespace App\Services;

use App\Enums\ReservationStatus;
use App\Models\DiningTable;
use App\Models\TableReservation;
use App\Models\User;
use Carbon\Carbon;
use InvalidArgumentException;

class ReservationService
{
    public function create(
        DiningTable $table,
        User $user,
        string $guestName,
        int $partySize,
        Carbon $startsAt,
        Carbon $endsAt,
        ?string $guestPhone = null,
        ?string $notes = null,
    ): TableReservation {
        $this->assertValid($table, $partySize, $startsAt, $endsAt);

        return TableReservation::create([
            'cafe_table_id' => $table->id,
            'user_id' => $user->id,
            'guest_name' => $guestName,
            'guest_phone' => $guestPhone,
            'party_size' => $partySize,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'notes' => $notes,
            'status' => ReservationStatus::Active,
        ]);
    }

    public function update(
        TableReservation $reservation,
        DiningTable $table,
        string $guestName,
        int $partySize,
        Carbon $startsAt,
        Carbon $endsAt,
        ?string $guestPhone = null,
        ?string $notes = null,
    ): TableReservation {
        if ($reservation->status !== ReservationStatus::Active) {
            throw new InvalidArgumentException(__('menu.reservation_not_active'));
        }

        $this->assertValid($table, $partySize, $startsAt, $endsAt, $reservation->id);

        $reservation->update([
            'cafe_table_id' => $table->id,
            'guest_name' => $guestName,
            'guest_phone' => $guestPhone,
            'party_size' => $partySize,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'notes' => $notes,
        ]);

        return $reservation->fresh(['cafeTable', 'user']);
    }

    public function cancel(TableReservation $reservation): TableReservation
    {
        if ($reservation->status !== ReservationStatus::Active) {
            throw new InvalidArgumentException(__('menu.reservation_not_active'));
        }

        $reservation->update(['status' => ReservationStatus::Cancelled]);

        return $reservation->fresh();
    }

    public function complete(TableReservation $reservation, ?Carbon $completedAt = null): TableReservation
    {
        if ($reservation->status !== ReservationStatus::Active) {
            throw new InvalidArgumentException(__('menu.reservation_not_active'));
        }

        $completedAt ??= now();

        $update = [
            'status' => ReservationStatus::Completed,
            'ends_at' => $completedAt,
        ];

        if ($completedAt->lt($reservation->ends_at)) {
            $update['scheduled_ends_at'] = $reservation->ends_at;
        }

        $reservation->update($update);

        return $reservation->fresh(['cafeTable', 'user']);
    }

    /** Set reservation end to checkout time; keep planned end when guests leave early. */
    public function finalizeCheckoutForTable(DiningTable $table, Carbon $checkoutAt, Carbon $visitStartedAt): void
    {
        TableReservation::query()
            ->where('cafe_table_id', $table->id)
            ->active()
            ->where(function ($query) use ($checkoutAt, $visitStartedAt) {
                $query->where(function ($q) use ($checkoutAt, $visitStartedAt) {
                    $q->where('starts_at', '<', $checkoutAt->copy()->addSecond())
                        ->where('ends_at', '>', $visitStartedAt);
                })->orWhere(function ($q) use ($checkoutAt, $visitStartedAt) {
                    $q->where('starts_at', '>', $visitStartedAt)
                        ->where('starts_at', '<=', $checkoutAt->copy()->addHours(2));
                });
            })
            ->get()
            ->each(fn (TableReservation $reservation) => $this->complete($reservation, $checkoutAt));
    }

    public function hasOverlap(DiningTable $table, Carbon $startsAt, Carbon $endsAt, ?int $ignoreId = null): bool
    {
        return TableReservation::query()
            ->where('cafe_table_id', $table->id)
            ->active()
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->exists();
    }

    protected function assertValid(
        DiningTable $table,
        int $partySize,
        Carbon $startsAt,
        Carbon $endsAt,
        ?int $ignoreReservationId = null,
    ): void {
        if ($partySize < 1) {
            throw new InvalidArgumentException(__('menu.reservation_party_size_invalid'));
        }

        if ($partySize > $table->capacity) {
            throw new InvalidArgumentException(__('menu.reservation_exceeds_capacity', [
                'capacity' => $table->capacity,
            ]));
        }

        if ($endsAt->lte($startsAt)) {
            throw new InvalidArgumentException(__('menu.reservation_end_before_start'));
        }

        if ($this->hasOverlap($table, $startsAt, $endsAt, $ignoreReservationId)) {
            throw new InvalidArgumentException(__('menu.reservation_overlap'));
        }
    }
}
