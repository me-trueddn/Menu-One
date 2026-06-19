<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
        <a href="{{ route('home') }}" class="brand-link">
            <span class="brand-text fw-light">{{ __('menu.app_name') }}</span>
        </a>
    </div>
    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" role="menu">
                @if($currentUser->canAccessPlatformPanel())
                    <li class="nav-header">{{ __('menu.management') }}</li>
                    @foreach(config('platform_modules.modules', []) as $moduleKey => $module)
                        @if($currentUser->canPlatformModule($moduleKey, 'view'))
                            <li class="nav-item">
                                <a href="{{ route($module['route']) }}" class="nav-link {{ collect($module['route_patterns'] ?? [])->contains(fn ($p) => request()->routeIs($p)) ? 'active' : '' }}">
                                    <i class="nav-icon bi {{ $module['icon'] }}"></i>
                                    <p>{{ __($module['label']) }}</p>
                                </a>
                            </li>
                        @endif
                    @endforeach
                @endif

                @if($currentUser->hasRole('cafe_admin'))
                    <li class="nav-header">{{ __('menu.management') }}</li>
                    <li class="nav-item">
                        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-speedometer2"></i>
                            <p>{{ __('menu.dashboard') }}</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.tables.index') }}" class="nav-link {{ request()->routeIs('admin.tables.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-grid-3x3"></i>
                            <p>{{ __('menu.tables') }}</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-tags"></i>
                            <p>{{ __('menu.categories') }}</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.products.index') }}" class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-cup-hot"></i>
                            <p>{{ __('menu.products') }}</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.staff.index') }}" class="nav-link {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-people"></i>
                            <p>{{ __('menu.staff') }}</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-bar-chart"></i>
                            <p>{{ __('menu.reports') }}</p>
                        </a>
                    </li>
                @endif

                @if($currentUser->hasRole('waiter'))
                    <li class="nav-header">{{ __('menu.waiter') }}</li>
                    <li class="nav-item">
                        <a href="{{ route('waiter.tables.index') }}" class="nav-link {{ request()->routeIs('waiter.tables.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-grid-3x3"></i>
                            <p>{{ __('menu.tables') }}</p>
                        </a>
                    </li>
                @endif

                @if($currentUser->hasRole('kitchen'))
                    <li class="nav-header">{{ __('menu.kitchen') }}</li>
                    <li class="nav-item">
                        <a href="{{ route('kitchen.index') }}" class="nav-link {{ request()->routeIs('kitchen.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-fire"></i>
                            <p>{{ __('menu.orders') }}</p>
                        </a>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
</aside>
