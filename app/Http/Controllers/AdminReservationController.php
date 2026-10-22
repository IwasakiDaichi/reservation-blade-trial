<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;

class AdminReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::with('user')
            ->latest()
            ->get();

        return view('admin.reservations.index', compact('reservations'));
    }

   
    public function updateStatus(
        Request $request,
        Reservation $reservation,
        string $status
    ) {
        if (!in_array($status, ['approved', 'rejected'], true)) {
            abort(400, 'Invalid reservation status.');
        }
    
        $validated = $request->validate([
            'rejection_reason' => [
                $status === 'rejected' ? 'required' : 'nullable',
                'string',
                'max:2000',
            ],
        ]);
    
        $reservation->update([
            'status' => $status,
            'rejection_reason' => $status === 'rejected'
                ? $validated['rejection_reason']
                : null,
        ]);
    
        return redirect()
            ->route('admin.reservations.index')
            ->with('success', 'Reservation ' . $status . ' successfully.');
    }

}