<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\DigitalMenu;
use App\Services\DigitalMenuCatalogService;
use App\Support\Branding;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class DigitalMenuController extends Controller
{
    public function __construct(private DigitalMenuCatalogService $catalog) {}

    public function show(Request $request, string $tenantId, string $menuPublicId): View|Response
    {
        $menu = DigitalMenu::query()
            ->where('public_id', $menuPublicId)
            ->where('is_active', true)
            ->firstOrFail();

        $tenant = tenant();
        $logoUrl = $tenant ? Branding::cafeLogoUrl($tenant) : Branding::logoUrl();

        $categories = $this->catalog->categoriesForPublicMenu();

        return view('theme::pages.public.digital-menu.show', [
            'menu' => $menu,
            'tenant' => $tenant,
            'logoUrl' => $logoUrl,
            'categories' => $categories,
        ]);
    }
}
