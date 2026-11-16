<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class AdminReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::with('user')
            ->latest()
            ->get();

        return view('admin.reservations.index', compact('reservations'));
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

        $signatureFields = [
            'security_signature' => 'security_signature_path',
            'endorsement_signature' => 'endorsement_signature_path',
            'reservation_officer_signature' => 'reservation_officer_signature_path',
            'approval_signature' => 'approval_signature_path',
        ];
        $files = array_filter($validated, static fn ($value) => $value instanceof UploadedFile);

        if ($files === []) {
            throw ValidationException::withMessages([
                'security_signature' => 'Choose at least one signature image to upload.',
            ]);
        }

        $updates = [];
        $oldPaths = [];
        foreach ($files as $field => $file) {
            $path = $file->store('reservations/signatures', 'local');
            if (! is_string($path) || $path === '') {
                throw new RuntimeException('A reservation signature could not be saved.');
            }

            $attribute = $signatureFields[$field];
            $oldPaths[] = $reservation->{$attribute};
            $updates[$attribute] = $path;
        }

        $reservation->update($updates);

        foreach ($oldPaths as $oldPath) {
            if ($oldPath && Storage::disk('local')->exists($oldPath) && ! Storage::disk('local')->delete($oldPath)) {
                throw new RuntimeException('An older reservation signature could not be removed.');
            }
        }

        return redirect()
            ->route('admin.reservations.signatures.edit', $reservation)
            ->with('success', 'Reservation signatures updated successfully.');
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
