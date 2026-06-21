{{ $reservation->starts_at->format('d.m.Y H:i') }}
–
{{ $reservation->ends_at->format('d.m.Y H:i') }}
@if($reservation->leftEarly())
    <div class="small text-muted mt-1">
        {{ __('menu.reservation_scheduled_ends_at') }}:
        {{ $reservation->scheduled_ends_at->format('d.m.Y H:i') }}
    </div>
@endif
