<nav class="app-header navbar navbar-expand bg-body">
    <div class="container-fluid">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                    <i class="bi bi-list"></i>
                </a>
            </li>
        </ul>
        <ul class="navbar-nav ms-auto align-items-center gap-2">
            @if(!empty($canSwitchTenants) && $canSwitchTenants)
                <li class="nav-item">@include('theme::partials.tenant-switcher')</li>
            @endif
            <li class="nav-item">@include('theme::partials.locale-switcher')</li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i> {{ $currentUser->name }}
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}">{{ __('menu.profile') }}</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">{{ __('menu.logout') }}</button>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>
