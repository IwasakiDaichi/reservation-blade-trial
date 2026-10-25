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
            'mobileNumber' => 'required|string|max:30',
            'purpose' => 'required|string|max:2000',
            'venue' => 'required|string|max:255',
            'otherVenue' => 'nullable|string|max:255',
    
            'reservationType' => 'required|in:single,multiple',
    
            'reservedDate' => 'required_if:reservationType,single|nullable|date|after_or_equal:today',
            'startDate' => 'required_if:reservationType,multiple|nullable|date|after_or_equal:today',
            'endDate' => 'required_if:reservationType,multiple|nullable|date|after_or_equal:startDate',
    
            'startTime' => 'required|date_format:H:i',
            'endTime' => 'required|date_format:H:i|after:startTime',
    
            'soundSystem' => 'nullable|string|max:255',
            'microphone' => 'nullable|string|max:255',
            'videoWall' => 'nullable|string|max:255',
    
            'flags' => 'nullable|in:Yes',
            'podium' => 'nullable|in:Yes',
            'platform' => 'nullable|in:Yes',
            'removalOfRings' => 'nullable|in:Yes',
    
            'tables' => 'nullable|integer|min:0',
            'chairs' => 'nullable|integer|min:0',
    
            'otherRequrements1' => 'nullable|string|max:2000',
            'agreed' => 'accepted',
        ]);
    
        $startDate = $validated['reservationType'] === 'single'
            ? $validated['reservedDate']
            : $validated['startDate'];
    
        $endDate = $validated['reservationType'] === 'single'
            ? $validated['reservedDate']
            : $validated['endDate'];
    
        Reservation::create([
            'user_id' => Auth::id(),
            'department' => $validated['department'],
            'mobile_number' => $validated['mobileNumber'],
            'purpose' => $validated['purpose'],
            'venue' => $validated['venue'],
            'other_venue' => $validated['otherVenue'] ?? null,
    
            // Keep the existing fields working with older pages.
            'reserved_date' => $startDate,
            'time' => $validated['startTime'] . ' - ' . $validated['endTime'],
    
            // New date-range and time-range fields.
            'start_date' => $startDate,
            'end_date' => $endDate,
            'start_time' => $validated['startTime'],
            'end_time' => $validated['endTime'],
    
            'sound_system' => !empty($validated['soundSystem']),
            'microphone' => !empty($validated['microphone']),
            'video_wall' => !empty($validated['videoWall']),
            'flags' => isset($validated['flags']),
            'podium' => isset($validated['podium']),
            'platform' => isset($validated['platform']),
            'tables' => (int) ($validated['tables'] ?? 0),
            'chairs' => (int) ($validated['chairs'] ?? 0),
            'removal_of_rings' => isset($validated['removalOfRings']),
    
            'other_requirements' => $validated['otherRequrements1'] ?? null,
            'agreed' => true,
            'status' => 'pending',
        ]);
    
        return redirect()
            ->route('reservations.index')
            ->with('success', 'Reservation submitted successfully!');
    }
}