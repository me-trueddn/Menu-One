<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OkcDeviceType;
use App\Http\Controllers\Controller;
use App\Models\OkcDevice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OkcDeviceController extends Controller
{
    public function index(): View
    {
        $devices = OkcDevice::query()->latest()->get();

        return view('theme::pages.admin.okc-devices.index', [
            'devices' => $devices,
            'deviceTypes' => OkcDeviceType::all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'device_type' => ['required', 'string', 'in:'.implode(',', array_column(OkcDeviceType::cases(), 'value'))],
            'brand' => ['nullable', 'string', 'max:120'],
            'model' => ['nullable', 'string', 'max:120'],
            'endpoint' => ['nullable', 'url', 'max:255'],
            'api_key' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        OkcDevice::create([
            ...$data,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', __('menu.okc_device_saved'));
    }

    public function update(Request $request, OkcDevice $okcDevice): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'device_type' => ['required', 'string', 'in:'.implode(',', array_column(OkcDeviceType::cases(), 'value'))],
            'brand' => ['nullable', 'string', 'max:120'],
            'model' => ['nullable', 'string', 'max:120'],
            'endpoint' => ['nullable', 'url', 'max:255'],
            'api_key' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $okcDevice->update([
            ...$data,
            'is_active' => $request->boolean('is_active', false),
        ]);

        return back()->with('success', __('menu.okc_device_saved'));
    }

    public function destroy(OkcDevice $okcDevice): RedirectResponse
    {
        $okcDevice->delete();

        return back()->with('success', __('menu.okc_device_deleted'));
    }
}

