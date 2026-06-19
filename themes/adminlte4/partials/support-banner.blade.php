@if(!empty($activeSupportSession))
<div class="support-banner alert alert-info border-0 rounded-0 mb-0 py-2 px-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
        <i class="bi bi-headset"></i>
        <span>
            {{ __('menu.support_admin_connected', [
                'admin' => $activeSupportSession['admin_name'],
                'email' => $activeSupportSession['admin_email'],
            ]) }}
        </span>
    </div>
    @if(!empty($inSupportMode))
        <form method="POST" action="{{ route('platform.support.disconnect') }}" class="mb-0">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-dark">
                <i class="bi bi-box-arrow-left"></i> {{ __('menu.exit_support') }}
            </button>
        </form>
    @endif
</div>
@endif
