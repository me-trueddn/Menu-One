<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class TicketController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('profile.edit', ['tab' => 'ticket']);
    }
}
