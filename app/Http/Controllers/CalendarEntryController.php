<?php

namespace App\Http\Controllers;

use App\Models\CalendarEntry;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CalendarEntryController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        CalendarEntry::create($this->validated($request) + ['user_id' => $request->user()->id]);

        return back()->with('success', 'Activity added to the calendar.');
    }

    public function update(Request $request, CalendarEntry $entry): RedirectResponse
    {
        $entry->update($this->validated($request));

        return back()->with('success', 'Activity updated.');
    }

    public function destroy(CalendarEntry $entry): RedirectResponse
    {
        $entry->delete();

        return back()->with('success', 'Activity removed from the calendar.');
    }

    /** Edit the calendar remarks of a reservation. Blank = automatic text. */
    public function remarks(Request $request, Reservation $reservation): RedirectResponse
    {
        $data = $request->validate([
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $reservation->calendar_remarks = filled($data['remarks'] ?? null) ? trim($data['remarks']) : null;
        $reservation->save();

        return back()->with('success', 'Remarks updated.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'start_date' => ['required', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time'   => ['nullable', 'date_format:H:i', Rule::when($request->filled('start_time'), ['after:start_time'])],
            'activity'   => ['required', 'string', 'max:500'],
            'department' => ['nullable', 'string', 'max:100'],
            'venue'      => ['nullable', 'string', 'max:255'],
            'remarks'    => ['nullable', 'string', 'max:500'],
        ]);
    }
}
