@extends('theme::layouts.app')
@section('title', __('menu.add_staff'))
@section('page-title', __('menu.add_staff'))
@section('content')
<div class="card">
    <div class="card-body">
        <p class="text-muted">{{ __('menu.staff_add_hint') }}</p>
        <form method="POST" action="{{ route('admin.staff.store') }}" id="staffAttachForm">
            @csrf
            <div class="mb-3">
                <label class="form-label">{{ __('menu.email') }}</label>
                <div class="input-group">
                    <input type="email" name="email" id="staffEmail" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" required>
                    <button type="button" class="btn btn-outline-secondary" id="staffLookupBtn">{{ __('menu.search_user') }}</button>
                </div>
                @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div id="staffLookupResult" class="alert d-none mb-3"></div>
            <div class="mb-3">
                <label class="form-label">{{ __('menu.role') }}</label>
                <select name="role" class="form-select" required>
                    @foreach($roles as $role)
                        <option value="{{ $role }}" @selected(old('role') === $role)>{{ __('menu.role_'.$role) }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary" id="staffSubmitBtn" disabled>{{ __('menu.add_staff') }}</button>
            <a href="{{ route('admin.staff.index') }}" class="btn btn-secondary">{{ __('menu.cancel') }}</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const emailInput = document.getElementById('staffEmail');
    const lookupBtn = document.getElementById('staffLookupBtn');
    const resultBox = document.getElementById('staffLookupResult');
    const submitBtn = document.getElementById('staffSubmitBtn');
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    async function lookup() {
        resultBox.classList.add('d-none');
        submitBtn.disabled = true;
        const response = await fetch(@json(route('admin.staff.lookup')), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ email: emailInput.value }),
        });
        const data = await response.json();
        resultBox.classList.remove('d-none', 'alert-success', 'alert-danger');
        if (data.found) {
            resultBox.classList.add('alert-success');
            resultBox.textContent = data.name + ' (' + data.email + ')';
            submitBtn.disabled = false;
        } else {
            resultBox.classList.add('alert-danger');
            resultBox.textContent = data.message;
        }
    }

    lookupBtn.addEventListener('click', lookup);
    emailInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            lookup();
        }
    });
});
</script>
@endpush
