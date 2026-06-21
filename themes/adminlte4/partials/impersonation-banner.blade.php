@if(!empty($isImpersonating))
<div class="impersonation-banner alert alert-warning border-0 rounded-0 mb-0 py-2 px-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
        <i class="bi bi-person-badge"></i>
        <span>{{ __('menu.impersonating_as', ['name' => auth()->user()->name, 'email' => auth()->user()->email]) }}</span>
    </div>
    <form method="POST" action="{{ route('impersonation.leave') }}" class="mb-0">
        @csrf
        <button type="submit" class="btn btn-sm btn-dark">
            <i class="bi bi-box-arrow-left"></i> {{ __('menu.exit_impersonation') }}
        </button>
    </form>
</div>
@endif
