<?php

namespace App\Http\Controllers;

use App\Enums\SignatureRole;
use App\Models\Reservation;
use App\Notifications\ReservationNotice;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class AdminReservationController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');

        if (! in_array($status, ['pending', 'approved', 'rejected', 'cancelled'], true)) {
            $status = '';
        }

        $reservations = Reservation::with('user', 'venue', 'department')
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.$search.'%';
                $idPart = ltrim($search, '#');

                $query->where(function ($q) use ($like, $idPart) {
                    // "#12" or "12" finds reservation number 12.
                    if ($idPart !== '' && ctype_digit($idPart)) {
                        $q->orWhere('id', (int) $idPart);
                    }

                    $q->orWhere('purpose', 'like', $like)
                        ->orWhereHas('department', fn ($d) => $d->where('name', 'like', $like))
                        ->orWhereHas('venue', fn ($v) => $v->where('name', 'like', $like)->orWhere('short_name', 'like', $like))
                        ->orWhere('other_venue', 'like', $like)
                        ->orWhereHas('user', function ($u) use ($like) {
                            $u->where('name', 'like', $like)
                                ->orWhere('email', 'like', $like);
                        });
                });
            })
            ->latest()
            ->get();

        return view('admin.reservations.index', compact('reservations', 'search', 'status'));
    }

    public function editSignatures(Reservation $reservation)
    {
        return view('admin.reservations.signatures', compact('reservation'));
    }

    public function updateSignatures(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'security_signature' => 'nullable|image|mimes:jpeg,png|max:10240',
            'endorsement_signature' => 'nullable|image|mimes:jpeg,png|max:10240',
            'reservation_officer_signature' => 'nullable|image|mimes:jpeg,png|max:10240',
            'approval_signature' => 'nullable|image|mimes:jpeg,png|max:10240',
        ]);

        $files = array_filter($validated, static fn ($value) => $value instanceof UploadedFile);

        if ($files === []) {
            throw ValidationException::withMessages([
                'security_signature' => 'Choose at least one signature image to upload.',
            ]);
        }

        $oldPaths = [];
        foreach (SignatureRole::staff() as $role) {
            $file = $files[$role->fileField()] ?? null;
            if (! $file) {
                continue;
            }

            $path = $file->store('reservations/signatures', 'local');
            if (! is_string($path) || $path === '') {
                throw new RuntimeException('A reservation signature could not be saved.');
            }

            $oldPaths[] = $reservation->signatures()->where('role', $role->value)->value('path');
            $reservation->signatures()->updateOrCreate(['role' => $role->value], ['path' => $path]);
        }

        foreach ($oldPaths as $oldPath) {
            if ($oldPath && Storage::disk('local')->exists($oldPath) && ! Storage::disk('local')->delete($oldPath)) {
                throw new RuntimeException('An older reservation signature could not be removed.');
            }
        }

        return redirect()
            ->route('admin.reservations.signatures.edit', $reservation)
            ->with('success', 'Reservation signatures updated successfully.');
    }

    /** CPDO cancels a pending or approved reservation and tells the student why. */
    public function cancel(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:2000'],
        ]);

        if (! in_array($reservation->status, ['pending', 'approved'], true)) {
            return redirect()
                ->route('admin.reservations.index')
                ->with('error', 'Only pending or approved reservations can be cancelled.');
        }

        $reservation->update([
            'status' => 'cancelled',
            'cancelled_by' => 'cpdo',
            'cancellation_reason' => $validated['cancellation_reason'],
        ]);

        $reservation->user?->notify(new ReservationNotice(
            'Your reservation was cancelled by CPDO',
            'Reservation #'.$reservation->id.' ("'.$reservation->purpose.'") on '.$reservation->dateText()
                .' was cancelled by CPDO. Reason: '.$validated['cancellation_reason'],
            route('reservations.index'),
        ));

        return redirect()
            ->route('admin.reservations.index')
            ->with('success', 'Reservation cancelled and the student was notified.');
    }

    public function updateStatus(
        Request $request,
        Reservation $reservation,
        string $status
    ) {
        if (! in_array($status, ['approved', 'rejected'], true)) {
            abort(400, 'Invalid reservation status.');
        }

        $validated = $request->validate([
            'rejection_reason' => [
                $status === 'rejected' ? 'required' : 'nullable',
                'string',
                'max:2000',
            ],
        ]);

        if ($status === 'approved' && $reservation->venue && ! $reservation->venue->isReservable()) {
            return redirect()
                ->route('admin.reservations.index')
                ->with('error', $reservation->venue->name.' is currently '.strtolower($reservation->venue->status->label()).' and cannot be approved. Set it to Active first, or reject this request.');
        }

        $reservation->update([
            'status' => $status,
            'rejection_reason' => $status === 'rejected'
                ? $validated['rejection_reason']
                : null,
        ]);

        return redirect()
            ->route('admin.reservations.index')
            ->with('success', 'Reservation '.$status.' successfully.');
    }
}
