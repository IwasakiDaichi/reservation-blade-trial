<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Services\ReservationFormPdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

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
        $user = Auth::user();

        if (blank($user->department) || blank($user->mobile_number)) {
            return redirect()
                ->route('profile.edit')
                ->with('error', 'Please complete your student profile before making a reservation.');
        }

        return view('reservations.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (blank($user->department) || blank($user->mobile_number)) {
            return redirect()
                ->route('profile.edit')
                ->with('error', 'Please complete your student profile before submitting a reservation.');
        }

        $validated = $request->validate([
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
            'applicant_signature' => 'nullable|image|mimes:jpeg,png|max:10240',

            'flags' => 'nullable|in:Yes',
            'podium' => 'nullable|in:Yes',
            'platform' => 'nullable|in:Yes',
            'removalOfRings' => 'nullable|in:Yes',

            'tables' => 'nullable|integer|min:0',
            'chairs' => 'nullable|integer|min:0',

            'otherRequrements1' => 'nullable|string|max:2000',
            'agreed' => 'accepted',
        ], [
            'applicant_signature.uploaded' => 'The signature image could not be uploaded. Please choose a smaller JPG or PNG image and try again.',
            'applicant_signature.max' => 'The applicant signature must be 10 MB or smaller.',
        ]);

        $startDate = $validated['reservationType'] === 'single'
            ? $validated['reservedDate']
            : $validated['startDate'];

        $endDate = $validated['reservationType'] === 'single'
            ? $validated['reservedDate']
            : $validated['endDate'];

        $signaturePath = null;
        if ($request->hasFile('applicant_signature')) {
            $signaturePath = $request->file('applicant_signature')->store('reservations/signatures', 'local');
            if (! is_string($signaturePath) || $signaturePath === '') {
                throw new RuntimeException('The applicant signature could not be saved.');
            }
        }

        Reservation::create([
            'user_id' => $user->id,
            'department' => $user->department,
            'mobile_number' => $user->mobile_number,
            'purpose' => $validated['purpose'],
            'venue' => $validated['venue'],
            'other_venue' => $validated['otherVenue'] ?? null,

            // Keep the existing fields working with older pages.
            'reserved_date' => $startDate,
            'time' => $validated['startTime'].' - '.$validated['endTime'],

            // New date-range and time-range fields.
            'start_date' => $startDate,
            'end_date' => $endDate,
            'start_time' => $validated['startTime'],
            'end_time' => $validated['endTime'],

            'sound_system' => filled($validated['soundSystem'] ?? null),
            'microphone' => filled($validated['microphone'] ?? null),
            'video_wall' => filled($validated['videoWall'] ?? null),
            'sound_system_details' => $validated['soundSystem'] ?? null,
            'microphone_details' => $validated['microphone'] ?? null,
            'video_wall_details' => $validated['videoWall'] ?? null,
            'flags' => isset($validated['flags']),
            'podium' => isset($validated['podium']),
            'platform' => isset($validated['platform']),
            'tables' => (int) ($validated['tables'] ?? 0),
            'chairs' => (int) ($validated['chairs'] ?? 0),
            'removal_of_rings' => isset($validated['removalOfRings']),

            'other_requirements' => $validated['otherRequrements1'] ?? null,
            'agreed' => true,
            'status' => 'pending',
            'applicant_signature_path' => $signaturePath,
        ]);

        return redirect()
            ->route('reservations.index')
            ->with('success', 'Reservation submitted successfully!');
    }

    public function print(Reservation $reservation, ReservationFormPdf $formPdf)
    {
        $user = Auth::user();
        if ($user->id !== $reservation->user_id && $user->role !== 'cpdo_admin') {
            abort(403);
        }

        return response($formPdf->generate($reservation), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="reservation-form-'.$reservation->id.'.pdf"',
        ]);
    }
}
