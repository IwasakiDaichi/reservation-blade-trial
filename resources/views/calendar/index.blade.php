@php
    $venues   = config('venues.venues');
    $fallback = config('venues.default');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar of Activities</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        table.calendar, table.calendar * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    </style>
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
<div class="mx-auto max-w-6xl px-4 py-8">

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3 print:hidden">
        <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-600 hover:text-slate-900">&larr; Back to dashboard</a>
        <button type="button" onclick="window.print()" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold hover:bg-slate-50">Print</button>
    </div>

    <header class="mb-6 rounded-2xl bg-gradient-to-r from-blue-950 to-blue-800 p-6 text-center text-white shadow">
        <h1 class="text-3xl font-black italic tracking-tight sm:text-4xl">CALENDAR OF ACTIVITIES</h1>
        <p class="mt-1 text-lg font-bold">{{ $month->format('F Y') }}</p>
    </header>

    <div class="mb-4 flex items-center justify-between print:hidden">
        <a href="{{ route('admin.calendar', ['month' => $prev]) }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold hover:bg-slate-50">&larr; {{ $month->subMonth()->format('M Y') }}</a>
        <a href="{{ route('admin.calendar') }}" class="text-sm font-semibold text-blue-800 hover:underline">This month</a>
        <a href="{{ route('admin.calendar', ['month' => $next]) }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold hover:bg-slate-50">{{ $month->addMonth()->format('M Y') }} &rarr;</a>
    </div>

    {{-- Legend --}}
    <div class="mb-4 flex flex-wrap gap-2">
        @foreach (collect($venues)->unique('label') as $v)
            <span class="rounded-md px-3 py-1 text-xs font-bold" style="background-color: {{ $v['bg'] }}; color: {{ $v['text'] }}">{{ $v['label'] }}</span>
        @endforeach
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-300 bg-white shadow-sm">
        <table class="calendar w-full min-w-[860px] border-collapse text-sm">
            <thead>
                <tr class="bg-slate-400 text-left text-xs font-bold uppercase tracking-wide text-slate-900">
                    <th class="border border-slate-300 px-3 py-2">Date</th>
                    <th class="border border-slate-300 px-3 py-2">Activity</th>
                    <th class="border border-slate-300 px-3 py-2 text-center">Department</th>
                    <th class="border border-slate-300 px-3 py-2 text-center">Time</th>
                    <th class="border border-slate-300 px-3 py-2 text-center">Venue</th>
                    <th class="border border-slate-300 px-3 py-2">Remarks</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($days as $date => $entries)
                @php $day = \Carbon\Carbon::parse($date); @endphp
                @foreach ($entries as $entry)
                    @php
                        $r      = $entry['reservation'];
                        $v      = $venues[$r->venue] ?? $fallback;
                        $venue  = $r->venue === 'Other' ? ($r->other_venue ?: 'Other') : ($venues[$r->venue]['label'] ?? $r->venue);
                        $time   = ($r->start_time && $r->end_time)
                                    ? \Carbon\Carbon::parse($r->start_time)->format('g:i A') . ' – ' . \Carbon\Carbon::parse($r->end_time)->format('g:i A')
                                    : $r->time;
                    @endphp
                    <tr style="background-color: {{ $v['bg'] }}; color: {{ $v['text'] }}">
                        @if ($loop->first)
                            <td rowspan="{{ count($entries) }}" class="w-44 border border-slate-300 bg-white px-3 py-2 align-top text-slate-800">
                                {{ $day->format('F j, Y') }} – {{ $day->format('l') }}
                            </td>
                        @endif
                        <td class="border border-slate-300 px-3 py-2 uppercase break-words">{{ $r->purpose }}</td>
                        <td class="border border-slate-300 px-3 py-2 text-center">{{ $r->department }}</td>
                        <td class="border border-slate-300 px-3 py-2 text-center whitespace-nowrap">{{ $time }}</td>
                        <td class="border border-slate-300 px-3 py-2 text-center uppercase">{{ $venue }}</td>
                        <td class="border border-slate-300 px-3 py-2">
                            @if ($entry['total_days'] > 1)
                                Day {{ $entry['day_number'] }} of {{ $entry['total_days'] }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="6" class="px-3 py-10 text-center text-slate-500">No approved reservations for {{ $month->format('F Y') }}.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
