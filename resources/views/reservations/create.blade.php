<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CPDO Venue Reservation</title>
    @vite(['resources/css/app.css', 'resources/js/app.tsx'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
<div class="min-h-screen lg:grid lg:grid-cols-[250px_minmax(0,1fr)]">
    <aside class="bg-gradient-to-b from-blue-950 via-blue-900 to-slate-900 px-6 py-8 text-white lg:min-h-screen">
        <a href="{{ url('/dashboard') }}" class="flex items-center gap-3">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/10 font-black ring-1 ring-white/20">C</span>
            <span><span class="block text-xs font-bold uppercase tracking-[.22em] text-blue-200">CPDO</span><span class="block font-bold">Venue Services</span></span>
        </a>
        <div class="mt-10 hidden lg:block">
            <p class="text-xs font-bold uppercase tracking-widest text-blue-200">Reservation process</p>
            <ol class="mt-5 space-y-5 text-sm">
                <li>01 · Applicant information</li><li>02 · Venue selection</li><li>03 · Reservation schedule</li><li>04 · Equipment and setup</li><li>05 · Terms and conditions</li>
            </ol>
        </div>
        <div class="mt-8 rounded-2xl border border-white/10 bg-white/5 p-4 lg:mt-12">
            <p class="font-semibold">Before you submit</p><p class="mt-2 text-sm leading-6 text-blue-100">Check your venue, dates, time, and setup details. All requests are subject to CPDO review.</p>
        </div>
    </aside>

    <main class="min-w-0 px-4 py-6 sm:px-6 sm:py-10 xl:px-10">
    <div class="mx-auto max-w-5xl">
        @if (session('success'))
        <div id="success-message" class="mb-6 flex justify-between gap-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-900" role="status">
            <div><p class="font-bold">Reservation submitted</p><p class="mt-1 text-sm">{{ session('success') }}</p></div>
            <button type="button" onclick="document.getElementById('success-message').remove()" aria-label="Close message">&times;</button>
        </div>
        @endif
        @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-5 text-red-900" role="alert">
            <p class="font-bold">Please check the following fields:</p>
            <ul class="mt-2 list-inside list-disc space-y-1 text-sm">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
        @endif

        <header class="mb-8 rounded-3xl bg-gradient-to-r from-blue-950 via-blue-900 to-blue-800 p-6 text-white shadow-lg sm:p-9">
            <p class="text-xs font-bold uppercase tracking-[.22em] text-blue-200">Campus Planning and Development Office</p>
            <h1 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">Venue Reservation</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-blue-100 sm:text-base">Complete the details below to request a venue. Please review your information before submitting.</p>
        </header>

        <form action="{{ url('/reservations') }}" method="POST" id="reservationForm" class="space-y-6">
        @csrf

        <section id="applicant-section" class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex gap-4 border-b border-slate-100 px-5 py-5 sm:px-7"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 font-black text-blue-800">01</span><div><h2 class="text-xl font-bold">Applicant Information</h2><p class="mt-1 text-sm text-slate-500">Contact and department information.</p></div></div>
            <div class="grid gap-5 p-5 sm:grid-cols-2 sm:p-7">
                <div>
    
                <div class="sm:col-span-2"><label for="purpose" class="mb-2 block text-sm font-semibold">Purpose of Reservation <b class="text-red-600">*</b></label><textarea id="purpose" name="purpose" rows="3" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100">{{ old('purpose') }}</textarea>@error('purpose')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
            </div>
        </section>

        <section id="venue-section" class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex gap-4 border-b border-slate-100 px-5 py-5 sm:px-7"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 font-black text-blue-800">02</span><div><h2 class="text-xl font-bold">Venue Selection</h2><p class="mt-1 text-sm text-slate-500">Choose the venue for your event.</p></div></div>
            <div class="p-5 sm:p-7">
                <label for="venue" class="mb-2 block text-sm font-semibold">Venue / Capacity <b class="text-red-600">*</b></label>
                <select id="venue" name="venue" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100">
                    <option value="">Choose a venue</option>
                    <option value="Campus Gym" @selected(old('venue') === 'Campus Gym')>UB Cardinals Gym — 2,000–2,500 capacity</option>
                    <option value="RCB Dome" @selected(old('venue') === 'RCB Dome')>RCB Dome — 200 armchairs</option>
                    <option value="FGB Hall" @selected(old('venue') === 'FGB Hall')>FGB Hall — 150–200 capacity</option>
                    <option value="Centenial Hall" @selected(old('venue') === 'Centenial Hall')>Centennial Hall — 100–150 capacity</option>
                    <option value="AMS Hall" @selected(old('venue') === 'AMS Hall')>AMS Hall — 120 armchairs</option>
                    <option value="Legacy" @selected(old('venue') === 'Legacy')>Legacy Multipurpose Hall — 150–300 capacity</option>
                    <option value="UB Student Center" @selected(old('venue') === 'UB Student Center')>UB Student Center — 100 capacity</option>
                    <option value="Alumni AVR" @selected(old('venue') === 'Alumni AVR')>Alumni AVR — 55 capacity</option>
                    <option value="Centennial AVR" @selected(old('venue') === 'Centennial AVR')>Centennial AVR — 35 capacity</option>
                    <option value="Other" @selected(old('venue') === 'Other')>Other venue</option>
                </select>
                @error('venue')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                <div id="other-venue-wrap" class="mt-5 {{ old('venue') === 'Other' ? '' : 'hidden' }}"><label for="otherVenue" class="mb-2 block text-sm font-semibold">Please specify the venue <b class="text-red-600">*</b></label><input id="otherVenue" name="otherVenue" value="{{ old('otherVenue') }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100">@error('otherVenue')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
            </div>
        </section>

        <section id="schedule-section" class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex gap-4 border-b border-slate-100 px-5 py-5 sm:px-7"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 font-black text-blue-800">03</span><div><h2 class="text-xl font-bold">Reservation Schedule</h2><p class="mt-1 text-sm text-slate-500">Choose one day or a continuous date range.</p></div></div>
            <div class="p-5 sm:p-7">
                <fieldset><legend class="mb-3 text-sm font-semibold">Reservation Duration <b class="text-red-600">*</b></legend>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="flex cursor-pointer gap-3 rounded-xl border border-slate-200 p-4 hover:border-blue-400 has-[:checked]:border-blue-700 has-[:checked]:bg-blue-50"><input id="singleDay" type="radio" name="reservationType" value="single" @checked(old('reservationType', 'single') === 'single') class="mt-1 h-4 w-4"><span><b class="block">One Day</b><span class="mt-1 block text-sm text-slate-500">Reserve for a single date.</span></span></label>
                        <label class="flex cursor-pointer gap-3 rounded-xl border border-slate-200 p-4 hover:border-blue-400 has-[:checked]:border-blue-700 has-[:checked]:bg-blue-50"><input id="multipleDays" type="radio" name="reservationType" value="multiple" @checked(old('reservationType') === 'multiple') class="mt-1 h-4 w-4"><span><b class="block">Multiple Days</b><span class="mt-1 block text-sm text-slate-500">Reserve across a date range.</span></span></label>
                    </div>
                    @error('reservationType')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </fieldset>
                <div id="singleDateContainer" class="mt-5"><label for="reservedDate" class="mb-2 block text-sm font-semibold">Reservation Date <b class="text-red-600">*</b></label><input id="reservedDate" name="reservedDate" type="date" value="{{ old('reservedDate') }}" min="{{ now()->toDateString() }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100">@error('reservedDate')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
                <div id="dateRangeContainer" class="mt-5 hidden"><div class="grid gap-5 sm:grid-cols-2"><div><label for="startDate" class="mb-2 block text-sm font-semibold">Start Date <b class="text-red-600">*</b></label><input id="startDate" name="startDate" type="date" value="{{ old('startDate') }}" min="{{ now()->toDateString() }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100">@error('startDate')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div><div><label for="endDate" class="mb-2 block text-sm font-semibold">End Date <b class="text-red-600">*</b></label><input id="endDate" name="endDate" type="date" value="{{ old('endDate') }}" min="{{ old('startDate', now()->toDateString()) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100">@error('endDate')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div></div><p class="mt-3 rounded-xl bg-blue-50 p-3 text-sm leading-6 text-blue-800">The period includes every date between the start and end dates. Weekend rules can be adjusted after CPDO clarification.</p></div>
               <div class="mt-5 grid gap-5 sm:grid-cols-2">
    <div>
        <label for="startTime" class="mb-2 block text-sm font-semibold">
            Start Time <b class="text-red-600">*</b>
        </label>
        <input
            id="startTime"
            name="startTime"
            type="time"
            value="{{ old('startTime') }}"
            required
            class="w-full rounded-xl border border-slate-300 px-4 py-3"
        >
        @error('startTime')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="endTime" class="mb-2 block text-sm font-semibold">
            End Time <b class="text-red-600">*</b>
        </label>
        <input
            id="endTime"
            name="endTime"
            type="time"
            value="{{ old('endTime') }}"
            required
            class="w-full rounded-xl border border-slate-300 px-4 py-3"
        >
        @error('endTime')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
            </div>
        </section>

        <section id="requirements-section" class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex gap-4 border-b border-slate-100 px-5 py-5 sm:px-7"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 font-black text-blue-800">04</span><div><h2 class="text-xl font-bold">Equipment and Physical Setup</h2><p class="mt-1 text-sm text-slate-500">Add the equipment and setup items required.</p></div></div>
            <div class="p-5 sm:p-7">
                <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-500">Equipment</h3>
                <div class="grid gap-5 sm:grid-cols-3">
                    <div><label for="soundSystem" class="mb-2 block text-sm font-semibold">Sound System</label><input id="soundSystem" name="soundSystem" value="{{ old('soundSystem') }}" placeholder="e.g. 1 set" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100"></div>
                    <div><label for="microphone" class="mb-2 block text-sm font-semibold">Microphone</label><input id="microphone" name="microphone" value="{{ old('microphone') }}" placeholder="e.g. 2 units" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100"></div>
                    <div><label for="videoWall" class="mb-2 block text-sm font-semibold">LED Video Wall</label><input id="videoWall" name="videoWall" value="{{ old('videoWall') }}" placeholder="Specify requirement" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100"></div>
                </div>
                <div class="mt-8 border-t border-slate-100 pt-6"><h3 class="font-bold">Physical Setup</h3><p class="mt-1 text-sm text-slate-500">Select items and enter quantities as needed.</p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 px-4 py-3"><input id="flags" name="flags" type="checkbox" value="Yes" @checked(old('flags') === 'Yes') class="h-4 w-4 rounded text-blue-700"><span class="text-sm font-medium">Flags (UB and Philippines)</span></label>
                        <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 px-4 py-3"><input id="podium" name="podium" type="checkbox" value="Yes" @checked(old('podium') === 'Yes') class="h-4 w-4 rounded text-blue-700"><span class="text-sm font-medium">Podium</span></label>
                        <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 px-4 py-3"><input id="platform" name="platform" type="checkbox" value="Yes" @checked(old('platform') === 'Yes') class="h-4 w-4 rounded text-blue-700"><span class="text-sm font-medium">Platform</span></label>
                    </div>
                    <div id="removalOfRingsContainer" class="mt-4 hidden rounded-xl border border-amber-200 bg-amber-50 p-4"><label for="removalOfRings" class="flex cursor-pointer items-start gap-3"><input id="removalOfRings" name="removalOfRings" type="checkbox" value="Yes" @checked(old('removalOfRings') === 'Yes') class="mt-1 h-4 w-4 rounded text-blue-700"><span><b class="block text-sm">Removal of Rings / Goal (Gym)</b><span class="mt-1 block text-sm text-slate-600">Only available when a gym venue is selected.</span></span></label></div>
                    <div class="mt-5 grid gap-5 sm:grid-cols-2"><div><label for="tables" class="mb-2 block text-sm font-semibold">Number of Tables</label><input id="tables" name="tables" type="number" min="0" value="{{ old('tables') }}" placeholder="0" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100"></div><div><label for="chairs" class="mb-2 block text-sm font-semibold">Number of Chairs</label><input id="chairs" name="chairs" type="number" min="0" value="{{ old('chairs') }}" placeholder="0" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100"></div></div>
                </div>
                <div class="mt-6"><label for="otherRequrements1" class="mb-2 block text-sm font-semibold">Other Requirements</label><textarea id="otherRequrements1" name="otherRequrements1" rows="4" placeholder="Additional equipment, setup, or special requirements..." class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100">{{ old('otherRequrements1') }}</textarea><input type="hidden" name="otherRequrements2" value="">@error('otherRequrements1')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
            </div>
        </section>

        <section id="agreement-section" class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex gap-4 border-b border-slate-100 px-5 py-5 sm:px-7"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 font-black text-blue-800">05</span><div><h2 class="text-xl font-bold">Terms and Conditions</h2><p class="mt-1 text-sm text-slate-500">Review the guidance and confirm your agreement.</p></div></div>
            <div class="space-y-4 p-5 sm:p-7">
                <div class="rounded-xl bg-slate-50 p-4"><h3 class="font-bold">University / School Events</h3><p class="mt-2 text-sm leading-6 text-slate-600">Follow university policies and CPDO instructions for venue use. Confirm event details and equipment before submitting.</p></div>
                <div class="rounded-xl bg-slate-50 p-4"><h3 class="font-bold">External Organizations</h3><p class="mt-2 text-sm leading-6 text-slate-600">External organizations should coordinate with CPDO regarding requirements, permissions, and fees before using a venue.</p></div>
                <p class="text-xs text-slate-500">These are draft summaries. Replace them with the approved wording from the official CPDO form before production.</p>
                <label for="agreed" class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4"><input id="agreed" name="agreed" type="checkbox" value="1" required @checked(old('agreed')) class="mt-1 h-4 w-4 rounded text-blue-700"><span class="text-sm leading-6">I have read and agree to the terms and conditions. <b class="text-red-600">*</b></span></label>
                @error('agreed')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </section>

        <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between"><p class="text-xs text-slate-500"><span class="text-red-600">*</span> Required fields</p><div class="flex flex-col-reverse gap-3 sm:flex-row"><a href="{{ url('/dashboard') }}" class="inline-flex justify-center rounded-xl border border-slate-300 bg-white px-6 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">Cancel</a><button type="submit" class="inline-flex justify-center rounded-xl bg-blue-800 px-7 py-3 text-sm font-bold text-white shadow-sm hover:bg-blue-900 focus:outline-none focus:ring-4 focus:ring-blue-200">Submit Reservation <span class="ml-2">→</span></button></div></div>
        </form>
        <footer class="mt-8 border-t border-slate-200 pt-5 text-center text-xs text-slate-500">CPDO Venue Reservation System · Contact details to be added</footer>
    </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const venueSelect = document.getElementById('venue');
    const otherVenueWrap = document.getElementById('other-venue-wrap');
    const otherVenueInput = document.getElementById('otherVenue');
    const removalContainer = document.getElementById('removalOfRingsContainer');
    const removalCheckbox = document.getElementById('removalOfRings');

    const singleDayRadio = document.getElementById('singleDay');
    const multipleDaysRadio = document.getElementById('multipleDays');
    const singleDateContainer = document.getElementById('singleDateContainer');
    const dateRangeContainer = document.getElementById('dateRangeContainer');
    const reservedDateInput = document.getElementById('reservedDate');
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');

    function updateVenueFields() {
        const showOther = venueSelect.value === 'Other';
        otherVenueWrap.classList.toggle('hidden', !showOther);
        otherVenueInput.required = showOther;
        if (!showOther) otherVenueInput.value = '';

        const isGym = venueSelect.value.toLowerCase().includes('gym');
        removalContainer.classList.toggle('hidden', !isGym);
        if (!isGym) removalCheckbox.checked = false;
    }

    function updateDateFields() {
        const isMultiple = multipleDaysRadio.checked;
        singleDateContainer.classList.toggle('hidden', isMultiple);
        dateRangeContainer.classList.toggle('hidden', !isMultiple);
        reservedDateInput.required = !isMultiple;
        startDateInput.required = isMultiple;
        endDateInput.required = isMultiple;
        if (isMultiple) {
            reservedDateInput.value = '';
        } else {
            startDateInput.value = '';
            endDateInput.value = '';
        }
        endDateInput.min = startDateInput.value || startDateInput.min;
    }

    venueSelect.addEventListener('change', updateVenueFields);
    singleDayRadio.addEventListener('change', updateDateFields);
    multipleDaysRadio.addEventListener('change', updateDateFields);
    startDateInput.addEventListener('change', function () {
        endDateInput.min = startDateInput.value || startDateInput.min;
        if (endDateInput.value && endDateInput.value < startDateInput.value) endDateInput.value = '';
    });

    updateVenueFields();
    updateDateFields();
});
</script>
</body>
</html>
