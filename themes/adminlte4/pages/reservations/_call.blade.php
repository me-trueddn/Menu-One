@if($reservation->guestPhoneDial())
    <a href="tel:{{ $reservation->guestPhoneDial() }}" class="btn btn-sm btn-success" title="{{ __('menu.reservation_call_guest') }}">
        <i class="fa fa-phone"></i> {{ __('menu.reservation_call_guest') }}
    </a>
@else
    <span class="text-muted small">—</span>
@endif
