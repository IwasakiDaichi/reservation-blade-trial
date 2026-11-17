<?php

namespace App\Http\Controllers;

use App\Models\CalendarEntry;
use App\Models\Reservation;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    /** Reservations with these statuses appear on the calendar (only 'approved' ones are colored). */
    private const VISIBLE_STATUSES = ['approved', 'pending'];

    public function index(Request $request)
    {
        $input = (string) $request->query('month', '');

        $month = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $input)
            ? CarbonImmutable::createFromFormat('!Y-m', $input)
            : CarbonImmutable::now()->startOfMonth();

        $monthStart = $month->startOfMonth();
        $monthEnd   = $month->endOfMonth();

        $days = [];

        // ---- Reservations -------------------------------------------------
        $reservations = Reservation::query()
            ->whereIn('status', self::VISIBLE_STATUSES)
            ->whereRaw('DATE(COALESCE(start_date, reserved_date)) <= ?', [$monthEnd->toDateString()])
            ->whereRaw('DATE(COALESCE(end_date, reserved_date)) >= ?', [$monthStart->toDateString()])
            ->get();

        foreach ($reservations as $r) {
            $label = $r->venue === 'Other'
                ? ($r->other_venue ?: 'Other')
                : (config("venues.venues.{$r->venue}.label") ?? $r->venue);

            $auto = $r->status === 'approved'
                ? 'OK WITH RESERVATION ' . ($r->approved_at ?? $r->updated_at)->format('n/j/Y')
                : 'Pending';

            $remarks = filled($r->calendar_remarks) ? $r->calendar_remarks : $auto;

            $this->spread(
                $days,
                ($r->start_date ?? $r->reserved_date)->toDateString(),
                ($r->end_date ?? $r->reserved_date)->toDateString(),
                $monthStart,
                $monthEnd,
                [
                    'activity'   => $r->purpose,
                    'department' => $r->department,
                    'time'       => $this->timeText($r->start_time, $r->end_time, $r->time),
                    'venue'      => $label,
                    'color'      => $r->status === 'approved' ? $this->colorFor($r->venue) : null,
                    'remarks'    => $remarks,
                    'sort'       => (string) $r->start_time,
                    'edit'       => [
                        'kind'    => 'reservation',
                        'action'  => route('admin.calendar.remarks', $r),
                        'summary' => $r->purpose . ' — ' . $label,
                        'remarks' => $remarks,
                        'auto'    => $auto,
                    ],
                ]
            );
        }

        // ---- Manually typed rows -----------------------------------------
        $entries = CalendarEntry::query()
            ->whereRaw('DATE(start_date) <= ?', [$monthEnd->toDateString()])
            ->whereRaw('DATE(COALESCE(end_date, start_date)) >= ?', [$monthStart->toDateString()])
            ->get();

        foreach ($entries as $e) {
            $startTime = $e->start_time ? substr((string) $e->start_time, 0, 5) : '';
            $endTime   = $e->end_time ? substr((string) $e->end_time, 0, 5) : '';

            $this->spread(
                $days,
                $e->start_date->toDateString(),
                ($e->end_date ?? $e->start_date)->toDateString(),
                $monthStart,
                $monthEnd,
                [
                    'activity'   => $e->activity,
                    'department' => $e->department,
                    'time'       => $this->timeText($startTime, $endTime, ''),
                    'venue'      => $e->venue,
                    'color'      => $this->colorFor($e->venue, true),
                    'remarks'    => $e->remarks,
                    'sort'       => $startTime,
                    'edit'       => [
                        'kind'       => 'manual',
                        'update_url' => route('admin.calendar.entries.update', $e),
                        'delete_url' => route('admin.calendar.entries.destroy', $e),
                        'start_date' => $e->start_date->toDateString(),
                        'end_date'   => $e->end_date?->toDateString() ?? '',
                        'start_time' => $startTime,
                        'end_time'   => $endTime,
                        'activity'   => $e->activity,
                        'department' => $e->department ?? '',
                        'venue'      => $e->venue ?? '',
                        'remarks'    => $e->remarks ?? '',
                    ],
                ]
            );
        }

        // ---- Sort ---------------------------------------------------------
        ksort($days);

        foreach ($days as $date => $list) {
            usort($list, fn ($a, $b) => strcmp($a['sort'], $b['sort']));
            $days[$date] = $list;
        }

        return view('calendar.index', [
            'days'  => $days,
            'month' => $month,
            'prev'  => $month->subMonth()->format('Y-m'),
            'next'  => $month->addMonth()->format('Y-m'),
        ]);
    }

    /** Repeat an entry on each day of its date range that falls inside the month. */
    private function spread(array &$days, string $start, string $end, CarbonImmutable $monthStart, CarbonImmutable $monthEnd, array $base): void
    {
        $first = CarbonImmutable::parse($start);
        $last  = CarbonImmutable::parse($end);
        $total = (int) round($first->diffInDays($last)) + 1;

        $from = max($first->toDateString(), $monthStart->toDateString());
        $to   = min($last->toDateString(), $monthEnd->toDateString());

        for ($d = CarbonImmutable::parse($from); $d->toDateString() <= $to; $d = $d->addDay()) {
            $days[$d->toDateString()][] = $base + [
                'day_number' => (int) round($first->diffInDays($d)) + 1,
                'total_days' => $total,
            ];
        }
    }

    /**
     * Venue color from config/venues.php.
     * $matchByLabel lets manually typed venues like "UB GYM" use the same color.
     */
    private function colorFor(?string $venue, bool $matchByLabel = false): ?array
    {
        if (blank($venue)) {
            return null;
        }

        $venues = config('venues.venues');

        if (isset($venues[$venue])) {
            return ['bg' => $venues[$venue]['bg'], 'text' => $venues[$venue]['text']];
        }

        if ($matchByLabel) {
            foreach ($venues as $key => $v) {
                if (strcasecmp(trim($venue), $v['label']) === 0 || strcasecmp(trim($venue), $key) === 0) {
                    return ['bg' => $v['bg'], 'text' => $v['text']];
                }
            }
        }

        return null;
    }

    private function timeText($start, $end, ?string $fallback): string
    {
        if ($start && $end) {
            return CarbonImmutable::parse($start)->format('g:i A') . ' – ' . CarbonImmutable::parse($end)->format('g:i A');
        }

        return (string) $fallback;
    }
}
