<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        if ($request->user()->role === 'cpdo_admin') {
            return redirect()->route('admin.dashboard');
        }

        return view('student.dashboard');
    }
}