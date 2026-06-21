@php
    $reservation = $reservation ?? null;
    $selectedTableId = $selectedTableId ?? old('cafe_table_id', $reservation?->cafe_table_id);
    $startsAtValue = old('starts_at', $reservation?->starts_at?->format('Y-m-d\TH:i'));
    $endsAtValue = old('ends_at', $reservation?->ends_at?->format('Y-m-d\TH:i'));
@endphp
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">{{ __('menu.table') }}</label>
        <select name="cafe_table_id" class="form-select" required>
            <option value="">{{ __('menu.select_table') }}</option>
            @foreach($tables as $table)
                <option value="{{ $table->id }}" @selected($selectedTableId == $table->id)>
                    {{ $table->name }} ({{ $table->capacity }} {{ __('menu.seats') }})
                </option>
            @endforeach
        </select>
        @error('cafe_table_id')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ __('menu.reservation_guest') }}</label>
        <input type="text" name="guest_name" class="form-control" value="{{ old('guest_name', $reservation?->guest_name) }}" required>
        @error('guest_name')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ __('menu.reservation_guest_phone') }}</label>
        <input type="tel" name="guest_phone" class="form-control" value="{{ old('guest_phone', $reservation?->guest_phone) }}"
               placeholder="05xx xxx xx xx" autocomplete="tel">
        @error('guest_phone')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">{{ __('menu.reservation_party_size') }}</label>
        <input type="number" name="party_size" class="form-control" min="1" max="99"
               value="{{ old('party_size', $reservation?->party_size ?? 2) }}" required>
        @error('party_size')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">{{ __('menu.reservation_starts_at') }}</label>
        <input type="datetime-local" name="starts_at" class="form-control" value="{{ $startsAtValue }}" required>
        @error('starts_at')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">{{ __('menu.reservation_ends_at') }}</label>
        <input type="datetime-local" name="ends_at" class="form-control" value="{{ $endsAtValue }}" required>
        @error('ends_at')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label">{{ __('menu.notes') }}</label>
        <textarea name="notes" class="form-control" rows="2">{{ old('notes', $reservation?->notes) }}</textarea>
        @error('notes')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
</div>
