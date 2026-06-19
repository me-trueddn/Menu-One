<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        if (! in_array($locale, config('locale.available', []), true)) {
            abort(404);
        }

        $request->session()->put('locale', $locale);

        return back();
    }
}
