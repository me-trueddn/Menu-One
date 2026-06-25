<?php

namespace App\Enums;

enum IntegrationOrderStatus: string
{
    case PendingAcceptance = 'pending_acceptance';
    case Accepted = 'accepted';
    case Preparing = 'preparing';
    case ReadyForCourier = 'ready_for_courier';
    case HandedToCourier = 'handed_to_courier';
    case Completed = 'completed';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PendingAcceptance => __('menu.integration_status_pending'),
            self::Accepted => __('menu.integration_status_accepted'),
            self::Preparing => __('menu.integration_status_preparing'),
            self::ReadyForCourier => __('menu.integration_status_ready_courier'),
            self::HandedToCourier => __('menu.integration_status_handed_courier'),
            self::Completed => __('menu.integration_status_completed'),
            self::Rejected => __('menu.integration_status_rejected'),
            self::Cancelled => __('menu.integration_status_cancelled'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PendingAcceptance => 'text-bg-warning',
            self::Accepted => 'text-bg-info',
            self::Preparing => 'text-bg-primary',
            self::ReadyForCourier => 'text-bg-success',
            self::HandedToCourier => 'text-bg-dark',
            self::Completed => 'text-bg-secondary',
            self::Rejected, self::Cancelled => 'text-bg-danger',
        };
    }

    /** @return list<string> */
    public static function activeValues(): array
    {
        return [
            self::PendingAcceptance->value,
            self::Accepted->value,
            self::Preparing->value,
            self::ReadyForCourier->value,
            self::HandedToCourier->value,
        ];
    }
}
