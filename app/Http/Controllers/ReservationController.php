<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    public function index()
    {
        $reservations = Auth::user()
            ->reservations()
            ->latest()
            ->get();

        return view('reservations.index', compact('reservations'));
    }

    public function create()
    {
        return view('reservations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'department' => 'required|string|max:255',
            'mobile_number' => 'required|string|max:30',
            'purpose' => 'required|string|max:2000',
            'venue' => 'required|string|max:255',
            'other_venue' => 'nullable|string|max:255',
            'reserved_date' => 'required|date|after_or_equal:today',
            'time' => 'required|string|max:100',

            'sound_system' => 'nullable|boolean',
            'microphone' => 'nullable|boolean',
            'video_wall' => 'nullable|boolean',
            'flags' => 'nullable|boolean',
            'podium' => 'nullable|boolean',
            'platform' => 'nullable|boolean',
            'tables' => 'nullable|boolean',
            'chairs' => 'nullable|boolean',
            'removal_of_rings' => 'nullable|boolean',

            'other_requirements' => 'nullable|string|max:2000',
            'agreed' => 'accepted',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['status'] = 'pending';

        foreach ([
            'sound_system', 'microphone', 'video_wall', 'flags',
            'podium', 'platform', 'tables', 'chairs', 'removal_of_rings',
        ] as $field) {
            $validated[$field] = $request->boolean($field);
        }

        $validated['agreed'] = true;

        Reservation::create($validated);

        return redirect()
            ->route('reservations.index')
            ->with('success', 'Reservation submitted successfully!');
    }
}