<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        if ($request->user()->role === 'cpdo_admin') {
            return redirect()->route('admin.reservations.index');
        }

        return redirect()->route('reservations.index');
    }
}
