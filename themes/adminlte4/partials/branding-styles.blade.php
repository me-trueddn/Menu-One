<style>
:root {
    --mo-sidebar-brand-height: {{ \App\Support\Branding::sidebarBrandHeight() }}px;
    --mo-sidebar-logo-height: {{ \App\Support\Branding::sidebarLogoHeight() }}px;
}

.app-sidebar .sidebar-brand {
    min-height: var(--mo-sidebar-brand-height);
    height: var(--mo-sidebar-brand-height);
    display: flex;
    align-items: center;
    padding: 0.5rem 0.75rem;
    overflow: hidden;
}

.app-sidebar .sidebar-brand .brand-link img {
    height: var(--mo-sidebar-logo-height);
    max-height: calc(var(--mo-sidebar-brand-height) - 1rem);
    width: auto;
    max-width: 100%;
    object-fit: contain;
}

.app-header .navbar-brand-logo {
    height: var(--mo-sidebar-logo-height, 28px);
    width: auto;
    max-width: min(100%, 12rem);
    object-fit: contain;
}
</style>