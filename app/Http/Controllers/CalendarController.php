<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    /** Only reservations with these statuses appear on the calendar. */
    private const VISIBLE_STATUSES = ['approved'];

    public function index(Request $request)
    {
        $input = (string) $request->query('month', '');

        $month = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $input)
            ? CarbonImmutable::createFromFormat('!Y-m', $input)
            : CarbonImmutable::now()->startOfMonth();

        $monthStart = $month->startOfMonth();
        $monthEnd   = $month->endOfMonth();

        $reservations = Reservation::query()
            ->whereIn('status', self::VISIBLE_STATUSES)
            ->whereRaw('DATE(COALESCE(start_date, reserved_date)) <= ?', [$monthEnd->toDateString()])
            ->whereRaw('DATE(COALESCE(end_date, reserved_date)) >= ?', [$monthStart->toDateString()])
            ->get();

        // Build [ 'Y-m-d' => [ entry, entry, ... ] ], repeating multi-day reservations on each day.
        $days = [];

        foreach ($reservations as $reservation) {
            $first = CarbonImmutable::parse(($reservation->start_date ?? $reservation->reserved_date)->toDateString());
            $last  = CarbonImmutable::parse(($reservation->end_date ?? $reservation->reserved_date)->toDateString());
            $total = (int) round($first->diffInDays($last)) + 1;

            $from = max($first->toDateString(), $monthStart->toDateString());
            $to   = min($last->toDateString(), $monthEnd->toDateString());

            for ($d = CarbonImmutable::parse($from); $d->toDateString() <= $to; $d = $d->addDay()) {
                $days[$d->toDateString()][] = [
                    'reservation' => $reservation,
                    'day_number'  => (int) round($first->diffInDays($d)) + 1,
                    'total_days'  => $total,
                ];
            }
        }

        ksort($days);

        foreach ($days as $date => $entries) {
            usort($entries, fn ($a, $b) => strcmp((string) $a['reservation']->start_time, (string) $b['reservation']->start_time));
            $days[$date] = $entries;
        }

        return view('calendar.index', [
            'days'  => $days,
            'month' => $month,
            'prev'  => $month->subMonth()->format('Y-m'),
            'next'  => $month->addMonth()->format('Y-m'),
        ]);
    }
}