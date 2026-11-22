<?php

namespace App\Http\Controllers;

use App\Enums\SignatureRole;
use App\Models\Equipment;
use App\Models\Department;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Venue;
use App\Notifications\ReservationNotice;
use Carbon\Carbon;
use App\Services\ReservationFormPdf;
use App\Enums\VenueStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use RuntimeException;

class ReservationController extends Controller
{
    public function index()
    {
        $reservations = Auth::user()
            ->reservations()
            ->with('venue')
            ->latest()
            ->get();

        return view('reservations.index', compact('reservations'));
    }

    public function create()
    {
        $user = Auth::user();

        if (blank($user->department_id) || blank($user->mobile_number)) {
            return redirect()
                ->route('profile.edit')
                ->with('error', 'Please complete your student profile before making a reservation.');
        }

        $venues = Venue::visibleToStudents()->orderBy('name')->get();

        return view('reservations.create', [
            'venues' => $venues,
            'selectedVenue' => old('venue_id', request('venue')),
            'earliestDate' => $this->earliestDate()->toDateString(),
        ]);
    }

    /** First day a new reservation may be for (CPDO rule: finalized two weeks before the event). */
    private function earliestDate(): Carbon
    {
        return today()->addDays((int) config('reservation.lead_days'));
    }

    /** Reservation form for walk-in / external applicants (no account). */
    public function createWalkIn()
    {
        return view('reservations.create', [
            'venues' => Venue::visibleToStudents()->orderBy('name')->get(),
            'selectedVenue' => old('venue_id', request('venue')),
            'earliestDate' => $this->earliestDate()->toDateString(),
            'walkIn' => true,
        ]);
    }

    public function store(Request $request)
    {
        return $this->submit($request, Auth::user());
    }

    public function storeWalkIn(Request $request)
    {
        return $this->submit($request, null);
    }

    /** $user is null for walk-in / external applicants. */
    private function submit(Request $request, ?User $user)
    {
        $walkIn = $user === null;
        $earliest = $this->earliestDate()->toDateString();

        if (! $walkIn && (blank($user->department_id) || blank($user->mobile_number))) {
            return redirect()
                ->route('profile.edit')
                ->with('error', 'Please complete your student profile before submitting a reservation.');
        }

        $validated = $request->validate(array_merge($walkIn ? [
            'applicant_name' => 'required|string|max:255',
            'local_number' => 'required|string|max:50',
            'department' => 'nullable|string|max:255',
        ] : [], [
            'purpose' => 'required|string|max:2000',
            'venue_id' => [
                'required',
                Rule::when(
                    $request->input('venue_id') !== 'other',
                    [Rule::exists('venues', 'id')->where('status', VenueStatus::Active->value)]
                ),
            ],
            'otherVenue' => 'required_if:venue_id,other|nullable|string|max:255',

            'reservationType' => 'required|in:single,multiple',

            'reservedDate' => 'required_if:reservationType,single|nullable|date|after_or_equal:'.$earliest,
            'startDate' => 'required_if:reservationType,multiple|nullable|date|after_or_equal:'.$earliest,
            'endDate' => 'required_if:reservationType,multiple|nullable|date|after_or_equal:startDate',

            'startTime' => 'required|date_format:H:i',
            'endTime' => 'required|date_format:H:i|after:startTime',

            'soundSystem' => 'nullable|string|max:255',
            'microphone' => 'nullable|string|max:255',
            'videoWall' => 'nullable|string|max:255',
            'applicant_signature' => 'required|image|mimes:jpeg,png|max:10240',

            'flags' => 'nullable|in:Yes',
            'podium' => 'nullable|in:Yes',
            'platform' => 'nullable|in:Yes',
            'removalOfRings' => 'nullable|in:Yes',

            'tables' => 'nullable|integer|min:0',
            'chairs' => 'nullable|integer|min:0',

            'otherRequrements1' => 'nullable|string|max:2000',
            'agreed' => 'accepted',
        ]), [
            'venue_id.required' => 'Please choose a venue.',
            'reservedDate.after_or_equal' => 'Reservations must be made at least two (2) weeks before the event. The earliest date you can reserve is '.$this->earliestDate()->format('F j, Y').'.',
            'startDate.after_or_equal' => 'Reservations must be made at least two (2) weeks before the event. The earliest start date is '.$this->earliestDate()->format('F j, Y').'.',
            'venue_id.exists' => 'That venue is not available for reservation right now.',
            'applicant_signature.uploaded' => 'The signature image could not be uploaded. Please choose a smaller JPG or PNG image and try again.',
            'applicant_signature.max' => 'The applicant signature must be 10 MB or smaller.',
        ]);

        $venue = $validated['venue_id'] === 'other' ? null : Venue::find($validated['venue_id']);

        if ($venue && (int) ($validated['chairs'] ?? 0) > $venue->capacity_max) {
            return back()->withInput()->withErrors([
                'chairs' => $venue->name.' holds up to '.number_format($venue->capacity_max).' people.',
            ]);
        }

        $startDate = $validated['reservationType'] === 'single'
            ? $validated['reservedDate']
            : $validated['startDate'];

        $endDate = $validated['reservationType'] === 'single'
            ? $validated['reservedDate']
            : $validated['endDate'];

        // CPDO rule: student activities are not held on Sundays.
        for ($day = Carbon::parse($startDate); $day->lte(Carbon::parse($endDate)); $day->addDay()) {
            if ($day->isSunday()) {
                $field = $validated['reservationType'] === 'single' ? 'reservedDate' : 'startDate';

                return back()->withInput()->withErrors([
                    $field => 'Student activities cannot be held on Sundays. Please choose another date.',
                ]);
            }
        }

        // Double booking: somebody else already has this venue at that date and time.
        if ($venue) {
            $conflicts = Reservation::conflictsFor(
                $venue->id, $startDate, $endDate, $validated['startTime'], $validated['endTime']
            );

            if ($conflicts->isNotEmpty()) {
                return back()->withInput()->with('conflicts', $conflicts->map(fn (Reservation $other) => [
                    'id' => $other->id,
                    'venue' => $venue->name,
                    'when' => $other->dateText().', '.$this->timeRange($other),
                    'purpose' => $other->purpose,
                ])->all());
            }
        }

        $signaturePath = null;
        if ($request->hasFile('applicant_signature')) {
            $signaturePath = $request->file('applicant_signature')->store('reservations/signatures', 'local');
            if (! is_string($signaturePath) || $signaturePath === '') {
                throw new RuntimeException('The applicant signature could not be saved.');
            }
        }

        $reservation = DB::transaction(function () use ($user, $validated, $venue, $startDate, $endDate, $signaturePath) {
            $reservation = Reservation::create([
                'user_id' => $user?->id,
                'applicant_name' => $user ? null : trim($validated['applicant_name']),
                'department_id' => $user
                    ? $user->department_id
                    : (filled($validated['department'] ?? null) ? Department::resolve($validated['department'])->id : null),
                'mobile_number' => $user ? $user->mobile_number : trim($validated['local_number']),
                'purpose' => $validated['purpose'],
                'venue_id' => $venue?->id,
                'other_venue' => $venue ? null : ($validated['otherVenue'] ?? null),

                // Keep the existing fields working with older pages.
                'reserved_date' => $startDate,
                'time' => $validated['startTime'].' - '.$validated['endTime'],

                // New date-range and time-range fields.
                'start_date' => $startDate,
                'end_date' => $endDate,
                'start_time' => $validated['startTime'],
                'end_time' => $validated['endTime'],

                'other_requirements' => $validated['otherRequrements1'] ?? null,
                'agreed' => true,
                'status' => 'pending',
            ]);

            $reservation->equipment()->sync($this->equipmentRows($validated));

            if ($signaturePath) {
                $reservation->signatures()->create([
                    'role' => SignatureRole::Applicant->value,
                    'path' => $signaturePath,
                ]);
            }

            return $reservation;
        });

        if ($walkIn) {
            return redirect(URL::temporarySignedRoute('walkin.thanks', now()->addHours(2), ['reservation' => $reservation->id]));
        }

        return redirect()
            ->route('reservations.index')
            ->with('success', 'Reservation submitted successfully!');
    }

    private function timeRange(Reservation $reservation): string
    {
        if ($reservation->start_time && $reservation->end_time) {
            return Carbon::createFromFormat('H:i', substr((string) $reservation->start_time, 0, 5))->format('g:i A')
                .' - '.Carbon::createFromFormat('H:i', substr((string) $reservation->end_time, 0, 5))->format('g:i A');
        }

        return (string) $reservation->time;
    }

    /**
     * The student said "yes" to talking with whoever already has the venue.
     * Only reservations that were just shown as conflicts can be asked about, and the other
     * student is told who asked (the form warns that contact details are shared in this case).
     */
    public function contact(Request $request)
    {
        $walkIn = $request->routeIs('walkin.*');
        $back = $walkIn ? 'walkin.create' : 'reservations.create';

        $data = $request->validate(['reservation_id' => 'required|integer']);

        $conflict = collect(session('conflicts', []))->firstWhere('id', (int) $data['reservation_id']);

        if (! $conflict) {
            return redirect()->route($back)
                ->with('error', 'That booking can no longer be contacted from here. Please submit your request again.');
        }

        $other = Reservation::with('user')->findOrFail($conflict['id']);
        $me = $request->user();

        $whoAmI = $me
            ? $me->name.' ('.$me->email.')'
            : (session('_old_input.applicant_name') ?: 'A walk-in applicant').' (local no. '.(session('_old_input.local_number') ?: 'not given').')';

        $other->user?->notify(new ReservationNotice(
            'Someone would like to talk about '.$conflict['venue'],
            $whoAmI.' tried to reserve '.$conflict['venue'].' for the same date and time as your reservation #'
                .$other->id.' ("'.$other->purpose.'") and asked to talk with you about the booking.',
        ));

        $info = session('contact_info', []);
        $info[$other->id] = [
            'name' => $other->applicantName(),
            'email' => $other->user?->email,
            'local' => $other->mobile_number,
        ];

        // Keep the form answers and the conflict panel for the next page load.
        $request->session()->reflash();
        $request->session()->flash('contact_info', $info);

        return redirect()->route($back);
    }

    /** Student cancels their own reservation (CPDO rule: at least three days before the date). */
    public function cancel(Request $request, Reservation $reservation)
    {
        abort_unless($reservation->user_id === $request->user()->id, 403);

        if (! $reservation->canBeCancelledByOwner()) {
            $message = in_array($reservation->status, ['pending', 'approved'], true)
                ? 'Cancellations must reach CPDO at least '.config('reservation.cancellation_notice_days')
                    .' days before the reserved date. Please contact the CPDO office directly.'
                : 'This reservation can no longer be cancelled.';

            return redirect()->route('reservations.index')->with('error', $message);
        }

        $reservation->update([
            'status' => 'cancelled',
            'cancelled_by' => 'student',
            'cancellation_reason' => $request->input('reason'),
        ]);

        return redirect()->route('reservations.index')->with('success', 'Your reservation was cancelled.');
    }

    /** Equipment ticked or filled in on the form, as [equipment_id => pivot values]. */
    private function equipmentRows(array $validated): array
    {
        $ids = Equipment::pluck('id', 'slug');
        $rows = [];

        foreach (['sound-system' => 'soundSystem', 'microphone' => 'microphone', 'led-video-wall' => 'videoWall'] as $slug => $field) {
            if (filled($validated[$field] ?? null)) {
                $rows[$ids[$slug]] = ['details' => $validated[$field]];
            }
        }

        foreach (['flags' => 'flags', 'podium' => 'podium', 'platform' => 'platform', 'removal-of-rings' => 'removalOfRings'] as $slug => $field) {
            if (isset($validated[$field])) {
                $rows[$ids[$slug]] = [];
            }
        }

        foreach (['tables' => 'tables', 'chairs' => 'chairs'] as $slug => $field) {
            if ((int) ($validated[$field] ?? 0) > 0) {
                $rows[$ids[$slug]] = ['quantity' => (int) $validated[$field]];
            }
        }

        return $rows;
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

    /** Thank-you page shown right after a walk-in submits (reached through a signed link). */
    public function walkInThanks(Reservation $reservation)
    {
        abort_unless($reservation->isWalkIn(), 404);

        return view('reservations.walkin-thanks', [
            'reservation' => $reservation->load('venue'),
            'printUrl' => URL::temporarySignedRoute('walkin.print', now()->addHours(2), ['reservation' => $reservation->id]),
        ]);
    }

    public function printWalkIn(Reservation $reservation, ReservationFormPdf $formPdf)
    {
        abort_unless($reservation->isWalkIn(), 404);

        return response($formPdf->generate($reservation), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="reservation-form-'.$reservation->id.'.pdf"',
        ]);
    }
}
