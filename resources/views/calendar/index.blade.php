@php
    $filterQuery = $selected === null ? [] : ['filtered' => 1, 'venues' => $selected];
    $fallback    = config('venues.default');
    $defaultDate = now()->isSameMonth($month) ? now()->toDateString() : $month->startOfMonth()->toDateString();
@endphp
<x-app-layout>
<style>
    table.calendar, table.calendar * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
</style>
<div>

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3 print:hidden">
        <p class="text-sm text-slate-600">Tick the venues you want to see.</p>
        <div class="flex gap-2">
            <button type="button" id="addBtn" class="rounded-lg bg-blue-800 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-900">+ Add activity</button>
            <button type="button" onclick="window.print()" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold hover:bg-slate-50">Print</button>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-900 print:hidden" role="status">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-900 print:hidden" role="alert">
            <p class="font-semibold">Please check the following:</p>
            <ul class="mt-1 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <header class="mb-6 rounded-2xl bg-gradient-to-r from-blue-950 to-blue-800 p-6 text-center text-white shadow">
        <h1 class="text-3xl font-black italic tracking-tight sm:text-4xl">CALENDAR OF ACTIVITIES</h1>
        <p class="mt-1 text-lg font-bold">{{ $month->format('F Y') }}</p>
    </header>

    <div class="mb-4 flex items-center justify-between print:hidden">
        <a href="{{ route('admin.calendar', ['month' => $prev] + $filterQuery) }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold hover:bg-slate-50">&larr; {{ $month->subMonth()->format('M Y') }}</a>
        <a href="{{ route('admin.calendar', $filterQuery) }}" class="text-sm font-semibold text-blue-800 hover:underline">This month</a>
        <a href="{{ route('admin.calendar', ['month' => $next] + $filterQuery) }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold hover:bg-slate-50">{{ $month->addMonth()->format('M Y') }} &rarr;</a>
    </div>

    {{-- Venue filter (checkboxes double as the color legend) --}}
    <form method="GET" action="{{ route('admin.calendar') }}" id="venueFilter" class="mb-4 rounded-xl border border-slate-200 bg-white p-4 print:hidden">
        <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
        <input type="hidden" name="filtered" value="1">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <p class="text-sm font-bold text-slate-700">Filter by venue</p>
            <div class="flex gap-3 text-xs font-semibold">
                <button type="button" id="venuesAll" class="text-blue-800 hover:underline">Select all</button>
                <button type="button" id="venuesNone" class="text-blue-800 hover:underline">Clear</button>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @foreach ($groups as $g)
                <label class="inline-flex cursor-pointer items-center gap-2 rounded-md border border-slate-300 px-3 py-1.5 text-xs font-bold shadow-sm"
                       style="background-color: {{ $g['bg'] }}; color: {{ $g['text'] }}">
                    <input type="checkbox" name="venues[]" value="{{ $g['value'] }}" class="venue-check h-4 w-4 rounded border-slate-400"
                           @checked($selected === null || in_array($g['value'], $selected, true))>
                    {{ $g['label'] }}
                </label>
            @endforeach
            <label class="inline-flex cursor-pointer items-center gap-2 rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-bold text-slate-700">
                <input type="checkbox" name="venues[]" value="other" class="venue-check h-4 w-4 rounded border-slate-400"
                       @checked($selected === null || in_array('other', $selected, true))>
                OTHER VENUES
            </label>
            <span class="rounded-md border border-slate-300 bg-white px-3 py-1 text-xs font-bold text-slate-600">PENDING / NOT YET CONFIRMED</span>
        </div>
    </form>

    <div class="overflow-x-auto rounded-xl border border-slate-300 bg-white shadow-sm">
        <table class="calendar w-full min-w-[900px] border-collapse text-sm">
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
                @foreach ($entries as $e)
                    @php
                        $bg   = $e['color']['bg']   ?? $fallback['bg'];
                        $text = $e['color']['text'] ?? $fallback['text'];
                    @endphp
                    <tr style="background-color: {{ $bg }}; color: {{ $text }}">
                        @if ($loop->first)
                            <td rowspan="{{ count($entries) }}" class="w-44 border border-slate-300 bg-white px-3 py-2 align-top text-slate-800">
                                {{ $day->format('F j, Y') }} – {{ $day->format('l') }}
                            </td>
                        @endif
                        <td class="border border-slate-300 px-3 py-2 uppercase break-words">{{ $e['activity'] }}</td>
                        <td class="border border-slate-300 px-3 py-2 text-center">{{ $e['department'] }}</td>
                        <td class="border border-slate-300 px-3 py-2 text-center whitespace-nowrap">{{ $e['time'] }}</td>
                        <td class="border border-slate-300 px-3 py-2 text-center uppercase">{{ $e['venue'] }}</td>
                        <td class="border border-slate-300 px-3 py-2">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <span class="uppercase">{{ $e['remarks'] }}</span>
                                    @if ($e['total_days'] > 1)
                                        <span class="block text-xs opacity-80">Day {{ $e['day_number'] }} of {{ $e['total_days'] }}</span>
                                    @endif
                                </div>
                                <button type="button"
                                        class="edit-btn shrink-0 text-xs font-semibold underline print:hidden"
                                        data-edit="{{ json_encode($e['edit']) }}">Edit</button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="6" class="px-3 py-10 text-center text-slate-500">No activities for {{ $month->format('F Y') }}.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Delete form (used by the dialog's Delete button) --}}
<form id="deleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

{{-- Add / edit dialog --}}
<dialog id="entryDialog" class="w-full max-w-lg rounded-2xl p-0 shadow-xl backdrop:bg-slate-900/50">
    <form id="entryForm" method="POST" class="p-6">
        @csrf
        <input type="hidden" name="_method" id="methodInput" value="PATCH" disabled>

        <h2 id="dialogTitle" class="text-lg font-bold">Add activity</h2>
        <p id="reservationSummary" class="mt-1 hidden text-sm text-slate-500"></p>

        <div id="manualFields" class="mt-4 space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="f_start_date" class="mb-1 block text-sm font-semibold">Date <span class="text-red-600">*</span></label>
                    <input id="f_start_date" name="start_date" type="date" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label for="f_end_date" class="mb-1 block text-sm font-semibold">End date <span class="font-normal text-slate-500">(optional)</span></label>
                    <input id="f_end_date" name="end_date" type="date" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="f_start_time" class="mb-1 block text-sm font-semibold">Start time</label>
                    <input id="f_start_time" name="start_time" type="time" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label for="f_end_time" class="mb-1 block text-sm font-semibold">End time</label>
                    <input id="f_end_time" name="end_time" type="time" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
            </div>
            <div>
                <label for="f_activity" class="mb-1 block text-sm font-semibold">Activity <span class="text-red-600">*</span></label>
                <input id="f_activity" name="activity" type="text" maxlength="500" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="f_department" class="mb-1 block text-sm font-semibold">Department</label>
                    <input id="f_department" name="department" type="text" maxlength="100" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label for="f_venue" class="mb-1 block text-sm font-semibold">Venue</label>
                    <input id="f_venue" name="venue" type="text" maxlength="255" list="venueList" placeholder="e.g. OUTSIDE" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    <datalist id="venueList">
                        @foreach ($venues->map->calendarLabel()->unique() as $label)
                            <option value="{{ $label }}"></option>
                        @endforeach
                        <option value="OUTSIDE"></option>
                    </datalist>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <label for="f_remarks" class="mb-1 block text-sm font-semibold">Remarks</label>
            <textarea id="f_remarks" name="remarks" rows="2" maxlength="500" class="w-full rounded-lg border border-slate-300 px-3 py-2"></textarea>
            <p id="remarksHint" class="mt-1 text-xs text-slate-500"></p>
        </div>

        <div class="mt-6 flex items-center justify-between gap-3">
            <button type="submit" form="deleteForm" id="deleteBtn"
                    onclick="return confirm('Delete this activity from the calendar?')"
                    class="hidden rounded-lg border border-red-300 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">Delete</button>
            <div class="ml-auto flex gap-2">
                <button type="button" id="cancelBtn" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold hover:bg-slate-50">Cancel</button>
                <button type="submit" class="rounded-lg bg-blue-800 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-900">Save</button>
            </div>
        </div>
    </form>
</dialog>

<script>
(function () {
    const form = document.getElementById('venueFilter');
    if (!form) return;
    const boxes = form.querySelectorAll('.venue-check');
    boxes.forEach(b => b.addEventListener('change', () => form.submit()));
    document.getElementById('venuesAll').addEventListener('click', () => { boxes.forEach(b => b.checked = true); form.submit(); });
    document.getElementById('venuesNone').addEventListener('click', () => { boxes.forEach(b => b.checked = false); form.submit(); });
})();

document.addEventListener('DOMContentLoaded', function () {
    const ADD_URL      = @json(route('admin.calendar.entries.store'));
    const DEFAULT_DATE = @json($defaultDate);

    const dlg         = document.getElementById('entryDialog');
    const form        = document.getElementById('entryForm');
    const methodInput = document.getElementById('methodInput');
    const manual      = document.getElementById('manualFields');
    const title       = document.getElementById('dialogTitle');
    const summary     = document.getElementById('reservationSummary');
    const hint        = document.getElementById('remarksHint');
    const deleteBtn   = document.getElementById('deleteBtn');
    const deleteForm  = document.getElementById('deleteForm');

    function setManual(on) {
        manual.classList.toggle('hidden', !on);
        manual.querySelectorAll('input').forEach(el => el.disabled = !on);
    }

    function openAdd() {
        form.reset();
        form.action = ADD_URL;
        methodInput.disabled = true;           // plain POST
        title.textContent = 'Add activity';
        summary.classList.add('hidden');
        hint.textContent = '';
        deleteBtn.classList.add('hidden');
        setManual(true);
        form.elements['start_date'].value = DEFAULT_DATE;
        dlg.showModal();
    }

    function openEdit(d) {
        form.reset();
        methodInput.disabled = false;          // PATCH

        if (d.kind === 'manual') {
            form.action = d.update_url;
            title.textContent = 'Edit activity';
            summary.classList.add('hidden');
            setManual(true);
            ['start_date', 'end_date', 'start_time', 'end_time', 'activity', 'department', 'venue', 'remarks']
                .forEach(name => form.elements[name].value = d[name] || '');
            hint.textContent = '';
            deleteForm.action = d.delete_url;
            deleteBtn.classList.remove('hidden');
        } else {
            form.action = d.action;
            title.textContent = 'Edit remarks';
            summary.textContent = d.summary;
            summary.classList.remove('hidden');
            setManual(false);
            form.elements['remarks'].value = d.remarks || '';
            hint.textContent = 'Clear the box and save to go back to: ' + d.auto;
            deleteBtn.classList.add('hidden');
        }

        dlg.showModal();
    }

    document.getElementById('addBtn').addEventListener('click', openAdd);
    document.getElementById('cancelBtn').addEventListener('click', () => dlg.close());
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', () => openEdit(JSON.parse(btn.dataset.edit)));
    });
});
</script>
</x-app-layout>
