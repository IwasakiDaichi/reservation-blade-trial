<?php

namespace App\Http\Controllers;

use App\Enums\VenueStatus;
use App\Http\Requests\VenueRequest;
use App\Models\Venue;
use App\Services\MaintenanceNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminVenueController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $venues = Venue::query()
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.$search.'%';
                $query->where(fn ($q) => $q->where('name', 'like', $like)->orWhere('location', 'like', $like));
            })
            ->orderBy('name')
            ->get();

        $counts = Venue::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.venues.index', compact('venues', 'counts', 'search'));
    }

    public function create()
    {
        return view('admin.venues.create', ['venue' => new Venue(['status' => VenueStatus::Active])]);
    }

    public function store(VenueRequest $request): RedirectResponse
    {
        $data = $this->prepare($request, $request->validated());

        $venue = Venue::create($data);

        return redirect()
            ->route('admin.venues.index')
            ->with('success', $venue->name.' was added.');
    }

    public function edit(Venue $venue)
    {
        return view('admin.venues.edit', compact('venue'));
    }

    public function update(VenueRequest $request, Venue $venue, MaintenanceNotifier $notifier): RedirectResponse
    {
        $data = $this->prepare($request, $request->validated(), $venue);

        $wasInMaintenance = $venue->status === VenueStatus::Maintenance;

        $venue->update($data);

        return redirect()
            ->route('admin.venues.index')
            ->with('success', $venue->name.' was updated.'.$this->maintenanceNote($venue, $wasInMaintenance, $notifier));
    }

    /** Quick status change from the venue list. */
    public function status(Request $request, Venue $venue, MaintenanceNotifier $notifier): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::enum(VenueStatus::class)],
        ]);

        $wasInMaintenance = $venue->status === VenueStatus::Maintenance;

        $venue->status = $data['status'];

        // Notes and return dates only make sense while a venue is unavailable.
        if ($venue->status === VenueStatus::Active) {
            $venue->status_note = null;
            $venue->available_from = null;
        }

        $venue->save();

        return back()->with('success', $venue->name.' is now '.strtolower($venue->status->label()).'.'.$this->maintenanceNote($venue, $wasInMaintenance, $notifier));
    }

    /** Informs students with affected reservations when a venue newly goes under maintenance. */
    private function maintenanceNote(Venue $venue, bool $wasInMaintenance, MaintenanceNotifier $notifier): string
    {
        if ($venue->status !== VenueStatus::Maintenance || $wasInMaintenance) {
            return '';
        }

        $count = $notifier->notify($venue);

        return $count > 0
            ? ' '.$count.' student'.($count === 1 ? ' was' : 's were').' notified and their reservations are now white on the calendar.'
            : ' No upcoming reservations were affected.';
    }

    /** Handles the photo upload and clears the note/date when a venue is active. */
    private function prepare(Request $request, array $data, ?Venue $venue = null): array
    {
        unset($data['image'], $data['remove_image'], $data['use_calendar_color']);

        // No color means the venue is shown in white on the calendar.
        if (! $request->boolean('use_calendar_color')) {
            $data['calendar_color'] = null;
            $data['calendar_text_color'] = null;
        } else {
            $data['calendar_color'] = strtoupper($data['calendar_color']);
            $data['calendar_text_color'] = $data['calendar_text_color'] ?? '#111827';
        }

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('venues', 'public');

            if ($venue?->image_path) {
                Storage::disk('public')->delete($venue->image_path);
            }
        } elseif ($request->boolean('remove_image') && $venue?->image_path) {
            Storage::disk('public')->delete($venue->image_path);
            $data['image_path'] = null;
        }

        if (($data['status'] ?? null) === VenueStatus::Active->value) {
            $data['status_note'] = null;
            $data['available_from'] = null;
        }

        return $data;
    }
}
