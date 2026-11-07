<?php

namespace App\Http\Controllers;

use App\Models\Reservation;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalReservations = Reservation::count();

        $pendingReservations = Reservation::where('status', 'pending')->count();

        $approvedReservations = Reservation::where('status', 'approved')->count();

        $rejectedReservations = Reservation::where('status', 'rejected')->count();

        $recentReservations = Reservation::with('user')
            ->latest()
            ->take(5)
            ->get();

        $upcomingReservations = Reservation::with('user')
            ->where('status', 'approved')
            ->whereDate('reserved_date', '>=', today())
            ->orderBy('reserved_date')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalReservations',
            'pendingReservations',
            'approvedReservations',
            'rejectedReservations',
            'recentReservations',
            'upcomingReservations'
        ));
    }
}