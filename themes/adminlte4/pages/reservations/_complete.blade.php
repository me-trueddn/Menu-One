<form method="POST" action="{{ route('reservations.complete', $reservation) }}" class="d-inline"
      onsubmit="return confirm('{{ __('menu.confirm_complete_reservation') }}')">
    @csrf
    <button type="submit" class="btn btn-sm btn-outline-success @if(!empty($block)) w-100 @endif">{{ __('menu.reservation_mark_completed') }}</button>
</form>
