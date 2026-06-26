<div class="mb-3 d-flex flex-wrap gap-2">
    <a href="{{ route('platform.tickets.index') }}" class="btn btn-sm {{ request()->routeIs('platform.tickets.index', 'platform.tickets.show') ? 'btn-primary' : 'btn-outline-primary' }}">
        <i class="bi bi-ticket-perforated me-1"></i>{{ __('menu.tickets') }}
    </a>
    <a href="{{ route('platform.ticket-categories.index') }}" class="btn btn-sm {{ request()->routeIs('platform.ticket-categories.*') ? 'btn-primary' : 'btn-outline-primary' }}">
        <i class="bi bi-folder me-1"></i>{{ __('menu.ticket_categories') }}
    </a>
    <a href="{{ route('platform.ticket-tags.index') }}" class="btn btn-sm {{ request()->routeIs('platform.ticket-tags.*') ? 'btn-primary' : 'btn-outline-primary' }}">
        <i class="bi bi-tags me-1"></i>{{ __('menu.ticket_tags') }}
    </a>
    <a href="{{ route('platform.tickets.settings') }}" class="btn btn-sm {{ request()->routeIs('platform.tickets.settings') ? 'btn-primary' : 'btn-outline-primary' }}">
        <i class="bi bi-gear me-1"></i>{{ __('menu.ticket_settings') }}
    </a>
</div>
