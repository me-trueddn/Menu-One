<?php

namespace App\Enums;

enum TicketStatus: string
{
    case New = 'new';
    case Answered = 'answered';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::New => __('menu.ticket_status_new'),
            self::Answered => __('menu.ticket_status_answered'),
            self::InProgress => __('menu.ticket_status_in_progress'),
            self::Resolved => __('menu.ticket_status_resolved'),
            self::Closed => __('menu.ticket_status_closed'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::New => 'text-bg-primary',
            self::Answered => 'text-bg-info',
            self::InProgress => 'text-bg-warning',
            self::Resolved => 'text-bg-success',
            self::Closed => 'text-bg-secondary',
        };
    }

    /** @return list<string> */
    public static function customerCreatable(): array
    {
        return [self::New->value];
    }

    /** @return list<string> */
    public static function adminValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function closesTicket(): bool
    {
        return in_array($this, [self::Resolved, self::Closed], true);
    }
}
