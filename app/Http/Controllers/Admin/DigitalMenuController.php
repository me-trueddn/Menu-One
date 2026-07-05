<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DigitalMenu;
use App\Services\QrCodeService;
use App\Support\DigitalMenuUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class DigitalMenuController extends Controller
{
    public function __construct(private QrCodeService $qrCodes) {}

    public function index(): View
    {
        $menus = DigitalMenu::query()
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('theme::pages.admin.digital-menus.index', compact('menus'));
    }

    public function create(): View
    {
        return view('theme::pages.admin.digital-menus.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $menu = DigitalMenu::create([
            'name' => $validated['name'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.digital-menus.show', $menu)
            ->with('success', __('menu.digital_menu_created'));
    }

    public function show(DigitalMenu $digitalMenu): View
    {
        $publicUrl = DigitalMenuUrl::forMenu($digitalMenu);
        $qrDataUri = $this->qrCodes->svgDataUri($publicUrl);

        return view('theme::pages.admin.digital-menus.show', [
            'menu' => $digitalMenu,
            'publicUrl' => $publicUrl,
            'qrDataUri' => $qrDataUri,
        ]);
    }

    public function downloadQr(DigitalMenu $digitalMenu): Response
    {
        $publicUrl = DigitalMenuUrl::forMenu($digitalMenu);
        $file = $this->qrCodes->downloadable($publicUrl);
        $filename = 'qr-menu-'.$digitalMenu->public_id.'.'.$file['extension'];

        return response($file['content'], 200, [
            'Content-Type' => $file['mime'],
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function destroy(DigitalMenu $digitalMenu): RedirectResponse
    {
        $digitalMenu->delete();

        return redirect()
            ->route('admin.digital-menus.index')
            ->with('success', __('menu.digital_menu_deleted'));
    }
}
