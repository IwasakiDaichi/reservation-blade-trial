<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Venue;
use App\Notifications\ReservationNotice;

class MaintenanceNotifier
{
    /**
     * Tells every student with an upcoming pending or approved reservation at this venue
     * that the venue is under maintenance. Returns how many students were informed.
     */
    public function notify(Venue $venue): int
    {
        $reservations = Reservation::query()
            ->with('user', 'venue')
            ->where('venue_id', $venue->id)
            ->whereIn('status', ['pending', 'approved'])
            ->whereRaw('DATE(COALESCE(end_date, reserved_date)) >= ?', [today()->toDateString()])
            ->get()
            ->filter(fn (Reservation $r) => $r->affectedByMaintenance());

        foreach ($reservations as $reservation) {
            $reason = $venue->status_note ? ' Reason: '.$venue->status_note.'.' : '';
            $back = $venue->available_from ? ' Expected back on '.$venue->available_from->format('M d, Y').'.' : '';

            $reservation->user?->notify(new ReservationNotice(
                $venue->name.' is under maintenance',
                'Your reservation #'.$reservation->id.' ("'.$reservation->purpose.'") on '
                    .$reservation->dateText().' is affected because '.$venue->name
                    .' is under maintenance.'.$reason.$back
                    .' CPDO will get in touch about rescheduling. You may also cancel it without the three-day notice.',
                route('reservations.index'),
            ));
        }

        return $reservations->pluck('user_id')->unique()->count();
    }
}
