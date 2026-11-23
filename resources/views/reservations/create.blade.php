<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University of Baguio | CPDO Venue Reservation</title>
    <link rel="icon" type="image/png" href="{{ asset('images/ub-seal.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#FFF8F8] text-[#1A202C]">
<div class="min-h-screen bg-[#FFF8F8] lg:grid lg:grid-cols-[250px_minmax(0,1fr)]">
    <aside class="reservation-sidebar bg-[#8A151B] px-6 py-8 text-white lg:sticky lg:top-0 lg:h-screen lg:self-start">
        <a href="{{ url('/dashboard') }}" class="block rounded-md focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-[#8A151B]">
            <x-application-logo class="h-auto w-36" />
            <span class="mt-5 block border-t border-[#B94F54] pt-4">
                <span class="block text-xs font-semibold tracking-wide text-[#FFE5E6]">CPDO</span>
                <span class="mt-1 block text-sm font-semibold text-white">Venue Reservation Services</span>
            </span>
        </a>

        <nav class="mt-10 hidden lg:block" aria-label="Reservation progress">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#FFE5E6]">Reservation progress</p>
            <ol class="mt-5 border-l border-[#B94F54]">
                <li class="reservation-progress-item reservation-progress-current" data-progress-step="0" aria-current="step">
                    <span class="reservation-progress-marker">1</span>
                    <span class="block text-sm font-medium">Terms and conditions</span>
                    <span class="mt-1 block text-xs text-[#FFE5E6]">Review venue use requirements</span>
                </li>
                <li class="reservation-progress-item" data-progress-step="1">
                    <span class="reservation-progress-marker">2</span>
                    <span class="block text-sm font-medium">Policies and guidelines</span>
                    <span class="mt-1 block text-xs text-[#FFE5E6]">Review venue use policies</span>
                </li>
                <li class="reservation-progress-item" data-progress-step="2">
                    <span class="reservation-progress-marker">3</span>
                    <span class="block text-sm font-medium">Reservation details</span>
                    <span class="mt-1 block text-xs text-[#FFE5E6]">Venue, schedule, and setup</span>
                </li>
                <li class="reservation-progress-item" data-progress-step="3">
                    <span class="reservation-progress-marker">4</span>
                    <span class="block text-sm font-medium">Confirmation and signature</span>
                    <span class="mt-1 block text-xs text-[#FFE5E6]">Confirm details and submit</span>
                </li>
            </ol>
        </nav>

        <div class="mt-10 hidden border-t border-[#B94F54] pt-5 lg:block">
            <p class="text-sm font-semibold">Before you submit</p>
            <p class="mt-2 text-sm leading-6 text-[#FFE5E6]">Read the terms and policies, then review your venue, schedule, and setup details before submission.</p>
        </div>
    </aside>

    <main class="min-w-0 px-4 py-6 sm:px-8 sm:py-8 xl:px-12">
    <div class="mx-auto max-w-5xl">
        @if (session('success'))
        <div id="success-message" class="mb-6 flex justify-between gap-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-emerald-900" role="status">
            <div><p class="font-bold">Reservation submitted</p><p class="mt-1 text-sm">{{ session('success') }}</p></div>
            <button type="button" onclick="document.getElementById('success-message').remove()" aria-label="Close message">&times;</button>
        </div>
        @endif
        @if ($errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-5 text-red-900" role="alert">
            <p class="font-bold">Please check the following fields:</p>
            <ul class="mt-2 list-inside list-disc space-y-1 text-sm">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
        @endif

        @if (session('conflicts'))
        <div id="conflict-panel" class="mb-6 rounded-lg border border-amber-300 bg-amber-50 p-5 text-amber-950" role="alert">
            <p class="font-bold">This venue is already booked</p>
            @foreach (session('conflicts') as $conflict)
                <div class="mt-3 border-t border-amber-200 pt-3 first:mt-2 first:border-0 first:pt-0">
                    <p class="text-sm leading-6">
                        Unfortunately, <strong>{{ $conflict['venue'] }}</strong> is reserved on <strong>{{ $conflict['when'] }}</strong>
                        for <strong>&ldquo;{{ $conflict['purpose'] }}&rdquo;</strong>.
                        Would you like to have a conversation with them regarding the booking?
                    </p>

                    @php $shared = session('contact_info.'.$conflict['id']); @endphp
                    @if ($shared)
                        <div class="mt-3 rounded-md border border-emerald-300 bg-emerald-50 p-3 text-sm text-emerald-900">
                            <p>You can reach <strong>{{ $shared['name'] }}</strong>
                                @if (! empty($shared['email']))
                                    at <a href="mailto:{{ $shared['email'] }}" class="font-semibold underline">{{ $shared['email'] }}</a>.
                                @else
                                    at local no. <strong>{{ $shared['local'] }}</strong>.
                                @endif
                            </p>
                            <p class="mt-1 text-xs">They were also told that you would like to talk, and your name and email were shared with them.</p>
                        </div>
                    @else
                        <div class="mt-3 flex flex-wrap gap-2">
                            <form method="POST" action="{{ route('reservations.contact') }}">
                                @csrf
                                <input type="hidden" name="reservation_id" value="{{ $conflict['id'] }}">
                                <button type="submit" class="rounded-md bg-[#B21F24] px-4 py-2 text-sm font-semibold text-white hover:bg-[#8A151B]">
                                    Yes, give me their email
                                </button>
                            </form>
                            <button type="button" onclick="document.getElementById('conflict-panel').remove()"
                                class="rounded-md border border-amber-400 px-4 py-2 text-sm font-semibold text-amber-900 hover:bg-amber-100">
                                No, I will choose another date or venue
                            </button>
                        </div>
                        <p class="mt-2 text-xs">If you say yes, your name and email will also be given to them.</p>
                    @endif
                </div>
            @endforeach
        </div>
        @endif

        <header class="mb-8 border-b border-[#F1C9CC] pb-6">
            <p class="text-sm font-medium text-[#B21F24]">Step 1 of 4</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-[#1A202C]">Request a venue reservation</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-[#4A5568]">Read the terms and policies first, then enter your venue, schedule, and setup details. You will confirm and sign before submission.</p>
        </header>

        @php
            $initialStep = 0;

            if ($errors->hasAny(['applicant_signature', 'agreed'])) {
                $initialStep = 3;
            } elseif ($errors->hasAny([
                'purpose',
                'venue_id',
                'otherVenue',
                'reservationType',
                'reservedDate',
                'startDate',
                'endDate',
                'startTime',
                'endTime',
                'soundSystem',
                'microphone',
                'videoWall',
                'flags',
                'podium',
                'platform',
                'removalOfRings',
                'tables',
                'chairs',
                'otherRequrements1',
            ]) || session('conflicts')) {
                $initialStep = 2;
            }
        @endphp

        <form action="{{ url('/reservations') }}" method="POST" enctype="multipart/form-data" id="reservationForm" class="space-y-6" novalidate data-initial-step="{{ $initialStep }}">
        @csrf
        <div class="wizard-step space-y-6" data-step="0">
            <section id="agreement-section" class="reservation-card">
                <div class="flex gap-4 border-b border-slate-100 px-5 py-5 sm:px-7">
                    <span class="flex h-10 w-10 items-center justify-center rounded-md bg-[#FFF1F2] font-black text-[#B21F24]">01</span>
                    <div>
                        <h2 class="text-xl font-bold">Terms and Conditions</h2>
                        <p class="mt-1 text-sm text-slate-500">Please read the applicable conditions carefully.</p>
                    </div>
                </div>
            
                <div class="space-y-6 p-5 sm:p-7">
                
                    <div class="rounded-xl border border-slate-200 p-5">
                        <h3 class="mb-4 text-lg font-bold text-slate-800">University / School Events</h3>
                    
                        <ol class="list-decimal space-y-3 pl-5 text-sm leading-7 text-slate-700">
                            <li>All venue requirements, including Reservation Form and setup layout, must be finalized and submitted at least two (2) weeks before the scheduled event.</li>
                            <li>All activities organized by students and faculty members must be endorsed by the School Dean.</li>
                            <li>Student activities shall not be held on <strong>SUNDAYS or HOLIDAYS.</strong></li>
                            <li>All activities organized by university offices must be endorsed by the respective Director.</li>
                            <li>CPDO must be informed of any postponement or cancellation at least three (3) days before the reserved date to allow the venue to be made available for other University activities.</li>
                            <li>Any damage to university property shall be charged to the student or personnel responsible.</li>
                        </ol>
                    </div>
                
                    <div class="rounded-xl border border-slate-200 p-5">
                        <h3 class="mb-4 text-lg font-bold text-slate-800">External Organization</h3>
                    
                        <ol class="list-decimal space-y-3 pl-5 text-sm leading-7 text-slate-700">
                            <li>All venue requirements must be finalized at least <strong>ONE (1) MONTH</strong> before the scheduled activity.</li>
                            <li>Requests from government agencies and external private institutions or organizations to conduct activities on campus are subject to the President's approval.</li>
                            <li>A fifty percent (50%) down payment must be made upon submission of the Reservation Form. The remaining balance and the bond fee (if applicable) must be paid at least two (2) days before the event. Otherwise, the reservation shall be cancelled. All payments must be made officially through the Cashier's office.</li>
                            <li>CPDO must be informed of any postponement or cancellation. If payment has been made, a refund will be processed.</li>
                            <li>The setup of selling booths and exhibits by the concessionaries is subject to the President's approval.</li>
                            <li>Pre-security briefing and debriefing must be conducted by the assigned personnel in charge to trace out security measures to be improved before and after the event.</li>
                            <li>A clearance must be obtained from CPDO before the bond fee can be claimed. Any additional charges and costs for property damage shall be deducted from the bond.</li>
                        </ol>
                    </div>
                
                </div>
            </section>
        </div>

        
        <div class="wizard-step space-y-6 hidden" data-step="1">
            <section class="reservation-card">
                <div class="flex gap-4 border-b border-slate-100 px-5 py-5 sm:px-7">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#FFF1F2] font-black text-[#B21F24]">02</span>
                    <div>
                        <h2 class="text-xl font-bold">Policies and Guidelines</h2>
                        <p class="mt-1 text-sm text-slate-500">The following policies must be strictly followed.</p>
                    </div>
                </div>
            
                <div class="p-5 sm:p-7">
                    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4">
                        <h3 class="font-black uppercase tracking-wide text-red-800">
                            The Following Policies Must Be Strictly Followed
                        </h3>
                    </div>
                
                    <ol class="list-decimal space-y-3 pl-5 text-sm leading-7 text-slate-700">
                        <li><strong>NO RESERVATION FORM. NO UTILIZATION POLICY.</strong></li>
                        <li>The approved seating capacity of the venue must be observed at all times for safety purposes.</li>
                        <li>Energy and water must be used responsibly and conserved.</li>
                        <li>The installation of heavy structures on venue floor is prohibited.</li>
                        <li>The use of open flames and flammable materials is prohibited.</li>
                        <li>Activities must end by 9:00 p.m. in accordance with Baguio City Ordinance No. 44, Series of 2018 ("Silent Night").</li>
                        <li>Furniture and equipment must not be removed from or relocated within the venue without prior authorization.</li>
                        <li>The use of double-sided tape, paint, or spray paint on venue surfaces is prohibited.</li>
                        <li>Decorations must not obstruct or cover safety devices and features, including fire extinguishers, exit signs, piping, and fire alarm pull stations.</li>
                        <li>The use of materials such as Styrofoam, bamboo poles or sticks, coconut husks, banana leaves, wood, stones, sand, and similar materials should be avoided. Decorations must not damage curtains, walls, or flooring.</li>
                        <li>The prescribed standard size for tarpaulins must be observed. Tarpaulins that do not comply with the standard will not be permitted.</li>
                        <li>Posters, display booths, and other marketing or advertising materials are not permitted without prior coordination.</li>
                        <li>Chewing of betel nut is discouraged on university premises, in accordance with the applicable Anti-Moma City Ordinance.</li>
                        <li>Smoking and vaping are prohibited.</li>
                        <li>Support groups, including Janitorial, Maintenance, AVR, Sound System, MAP, MIS, and Security personnel, are not responsible for venue setup, decorations, or beautification.</li>
                        <li>The applicant must coordinate any required physical setup with the appropriate technical support group at least two (2) days before the activity.</li>
                        <li>Visitors must obtain security clearance from the Security Department.</li>
                        <li>Do not leave your valuables unattended. Support committees are not responsible for safeguarding materials or personal belongings brought to or left at the venue.</li>
                        <li>Event organizers must provide their own seat covers, tablecloths, linens, and similar items.</li>
                        <li>Available resources are limited. Requests for materials and equipment are subject to availability.</li>
                        <li>All event participants must comply with the minimum health standards implemented on the University campus.</li>
                        <li>The clean-as-you-go (CLAYGO) practice must be followed. Participants must dispose of their garbage properly.</li>
                        <li>Loitering inside the venue is strictly prohibited.</li>
                        <li>Only athletes are allowed during trainings and only enrolled students are allowed during PE classes inside the gym.</li>
                        <li>Any stage extension must be coordinated with CPDO in advance.</li>
                        <li>Shoes with heels, steel toes, combat boots, leather soles, and similar footwear are not allowed on the open floor area. Rubber-soled or gum-soled shoes must be worn.</li>
                        <li>Food is prohibited unless the activity has been properly coordinated with CPDO. Schools and departments are encouraged to serve food in packed form, except for institutional events.</li>
                        <li>The coordinator is responsible for overall communication and for overseeing the implementation of health and safety protocols.</li>
                        <li>The reservation shall be considered confirmed only upon approval by the authorized office. Requesting offices shall verify the status of their reservation and follow up on pending requests if necessary.</li>
                    </ol>
                
                    
                </div>
            </section>
        </div>

        <div class="wizard-step space-y-6 hidden" data-step="2">
            <div class="rounded-xl border border-amber-300 bg-amber-50 p-5 text-sm leading-6 text-amber-950" role="note">
                <p class="font-bold">Please read before you reserve</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    <li>Reservations must be made at least <strong>two (2) weeks</strong> before the event. The earliest date you can choose is <strong>{{ \Carbon\Carbon::parse($earliestDate)->format('F j, Y') }}</strong>.</li>
                    <li>Student activities cannot be held on <strong>Sundays or holidays</strong>, and every activity must <strong>end by 9:00 PM</strong>.</li>
                    <li>You may cancel up to <strong>{{ config('reservation.cancellation_notice_days') }} days</strong> before the reserved date. CPDO may also cancel a reservation, for example when a venue goes under maintenance, and will tell you why.</li>
                    <li><strong>Double booking:</strong> if another reservation already has the same venue, date and time, we will tell you and ask if you would like to talk to that person. If you say yes, your name and email will be shared with them, and theirs with you, so you can discuss the booking.</li>
                </ul>
            </div>
        <section id="applicant-section" class="reservation-card">
                    <div class="flex gap-4 border-b border-slate-100 px-5 py-5 sm:px-7"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#FFF1F2] font-black text-[#B21F24]">03</span><div><h2 class="text-xl font-bold">Reservation Details</h2><p class="mt-1 text-sm text-slate-500">Provide the purpose and details of your venue reservation.</p></div></div>
                    <div class="grid gap-5 p-5 sm:grid-cols-2 sm:p-7">
                        <div class="sm:col-span-2"><label for="purpose" class="mb-2 block text-sm font-semibold">Reservation Purpose <b class="text-red-600">*</b></label><textarea id="purpose" name="purpose" rows="3" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-[#B21F24] focus:outline-none focus:ring-4 focus:ring-[#FADBDD]">{{ old('purpose') }}</textarea>@error('purpose')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
                    </div>
                </section>
            
                <section id="venue-section" class="reservation-card">
                    <div class="flex gap-4 border-b border-slate-100 px-5 py-5 sm:px-7"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#FFF1F2] font-black text-[#B21F24]">04</span><div><h2 class="text-xl font-bold">Venue Selection</h2><p class="mt-1 text-sm text-slate-500">Choose the venue for your event.</p></div></div>
                    <div class="p-5 sm:p-7">
                        <label for="venue" class="mb-2 block text-sm font-semibold">Venue / Capacity <b class="text-red-600">*</b></label>
                        <select id="venue" name="venue_id" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 focus:border-[#B21F24] focus:outline-none focus:ring-4 focus:ring-[#FADBDD]">
                            <option value="">Choose a venue</option>
                            @foreach ($venues as $venue)
                                <option value="{{ $venue->id }}" data-name="{{ $venue->name }}" data-capacity="{{ $venue->capacity_max }}"
                                    @disabled(! $venue->isReservable()) @selected((string) $selectedVenue === (string) $venue->id)>
                                    {{ $venue->name }} — {{ $venue->capacityLabel() }} capacity{{ $venue->isReservable() ? '' : ' (under maintenance)' }}
                                </option>
                            @endforeach
                            <option value="other" @selected((string) $selectedVenue === 'other')>Other venue</option>
                        </select>
                        @error('venue_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        <div id="other-venue-wrap" class="mt-5 {{ (string) $selectedVenue === 'other' ? '' : 'hidden' }}"><label for="otherVenue" class="mb-2 block text-sm font-semibold">Please specify the venue <b class="text-red-600">*</b></label><input id="otherVenue" name="otherVenue" value="{{ old('otherVenue') }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-[#B21F24] focus:outline-none focus:ring-4 focus:ring-[#FADBDD]">@error('otherVenue')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
                    </div>
                </section>
            
                <section id="schedule-section" class="reservation-card">
                    <div class="flex gap-4 border-b border-slate-100 px-5 py-5 sm:px-7"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#FFF1F2] font-black text-[#B21F24]">05</span><div><h2 class="text-xl font-bold">Reservation Schedule</h2><p class="mt-1 text-sm text-slate-500">Choose one day or a continuous date range.</p></div></div>
                    <div class="p-5 sm:p-7">
                        <fieldset><legend class="mb-3 text-sm font-semibold">Reservation Duration <b class="text-red-600">*</b></legend>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <label class="flex cursor-pointer gap-3 rounded-xl border border-slate-200 p-4 hover:border-[#D88B90] has-[:checked]:border-[#B21F24] has-[:checked]:bg-[#FFF8F8]"><input id="singleDay" type="radio" name="reservationType" value="single" @checked(old('reservationType', 'single') === 'single') class="mt-1 h-4 w-4"><span><b class="block">One Day</b><span class="mt-1 block text-sm text-slate-500">Reserve for a single date.</span></span></label>
                                <label class="flex cursor-pointer gap-3 rounded-xl border border-slate-200 p-4 hover:border-[#D88B90] has-[:checked]:border-[#B21F24] has-[:checked]:bg-[#FFF8F8]"><input id="multipleDays" type="radio" name="reservationType" value="multiple" @checked(old('reservationType') === 'multiple') class="mt-1 h-4 w-4"><span><b class="block">Multiple Days</b><span class="mt-1 block text-sm text-slate-500">Reserve across a date range.</span></span></label>
                            </div>
                            @error('reservationType')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                        </fieldset>
                        <div id="singleDateContainer" class="mt-5"><label for="reservedDate" class="mb-2 block text-sm font-semibold">Reservation Date <b class="text-red-600">*</b></label><input id="reservedDate" name="reservedDate" type="date" value="{{ old('reservedDate') }}" min="{{ $earliestDate }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-[#B21F24] focus:outline-none focus:ring-4 focus:ring-[#FADBDD]"><p id="dateWarning" class="mt-1 hidden text-sm font-semibold text-red-600" role="alert"></p>@error('reservedDate')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
                        <div id="dateRangeContainer" class="mt-5 hidden"><div class="grid gap-5 sm:grid-cols-2"><div><label for="startDate" class="mb-2 block text-sm font-semibold">Start Date <b class="text-red-600">*</b></label><input id="startDate" name="startDate" type="date" value="{{ old('startDate') }}" min="{{ $earliestDate }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-[#B21F24] focus:outline-none focus:ring-4 focus:ring-[#FADBDD]">@error('startDate')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div><div><label for="endDate" class="mb-2 block text-sm font-semibold">End Date <b class="text-red-600">*</b></label><input id="endDate" name="endDate" type="date" value="{{ old('endDate') }}" min="{{ old('startDate', $earliestDate) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-[#B21F24] focus:outline-none focus:ring-4 focus:ring-[#FADBDD]">@error('endDate')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div></div><p class="mt-3 rounded-xl bg-[#FFF1F2] p-3 text-sm leading-6 text-[#B21F24]">The period includes every date between the start and end dates. Sundays cannot be included.</p></div>
                       <div class="mt-5 grid gap-5 sm:grid-cols-2">
            <div>
                <label for="startTime" class="mb-2 block text-sm font-semibold">
                    Start Time <b class="text-red-600">*</b>
                </label>
                <input
                    id="startTime"
                    name="startTime"
                    type="time"
                    max="{{ config('reservation.closing_time') }}"
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
                    max="{{ config('reservation.closing_time') }}"
                    value="{{ old('endTime') }}"
                    required
                    class="w-full rounded-xl border border-slate-300 px-4 py-3"
                >
                <p id="timeWarning" class="mt-1 hidden text-sm font-semibold text-red-600" role="alert"></p>
                @error('endTime')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
                    </div>
                </section>
            
                <section id="requirements-section" class="reservation-card">
                    <div class="flex gap-4 border-b border-slate-100 px-5 py-5 sm:px-7"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#FFF1F2] font-black text-[#B21F24]">06</span><div><h2 class="text-xl font-bold">Equipment and Physical Setup</h2><p class="mt-1 text-sm text-slate-500">Add the equipment and setup items required.</p></div></div>
                    <div class="p-5 sm:p-7">
                        <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-500">Equipment</h3>
                        <div class="grid gap-5 sm:grid-cols-3">
                            <div><label for="soundSystem" class="mb-2 block text-sm font-semibold">Sound System</label><input id="soundSystem" name="soundSystem" value="{{ old('soundSystem') }}" placeholder="e.g. 1 set" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-[#B21F24] focus:outline-none focus:ring-4 focus:ring-[#FADBDD]"></div>
                            <div><label for="microphone" class="mb-2 block text-sm font-semibold">Microphone</label><input id="microphone" name="microphone" value="{{ old('microphone') }}" placeholder="e.g. 2 units" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-[#B21F24] focus:outline-none focus:ring-4 focus:ring-[#FADBDD]"></div>
                            <div><label for="videoWall" class="mb-2 block text-sm font-semibold">LED Video Wall</label><input id="videoWall" name="videoWall" value="{{ old('videoWall') }}" placeholder="Specify requirement" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-[#B21F24] focus:outline-none focus:ring-4 focus:ring-[#FADBDD]"></div>
                        </div>
                        <div class="mt-8 border-t border-slate-100 pt-6"><h3 class="font-bold">Physical Setup</h3><p class="mt-1 text-sm text-slate-500">Select items and enter quantities as needed.</p>
                            <div class="mt-4 flex flex-wrap gap-3">
                                <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 px-4 py-3"><input id="flags" name="flags" type="checkbox" value="Yes" @checked(old('flags') === 'Yes') class="h-4 w-4 rounded text-[#B21F24]"><span class="text-sm font-medium">Flags (UB and Philippines)</span></label>
                                <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 px-4 py-3"><input id="podium" name="podium" type="checkbox" value="Yes" @checked(old('podium') === 'Yes') class="h-4 w-4 rounded text-[#B21F24]"><span class="text-sm font-medium">Podium</span></label>
                                <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 px-4 py-3"><input id="platform" name="platform" type="checkbox" value="Yes" @checked(old('platform') === 'Yes') class="h-4 w-4 rounded text-[#B21F24]"><span class="text-sm font-medium">Platform</span></label>
                            </div>
                            <div id="removalOfRingsContainer" class="mt-4 hidden rounded-xl border border-amber-200 bg-amber-50 p-4"><label for="removalOfRings" class="flex cursor-pointer items-start gap-3"><input id="removalOfRings" name="removalOfRings" type="checkbox" value="Yes" @checked(old('removalOfRings') === 'Yes') class="mt-1 h-4 w-4 rounded text-[#B21F24]"><span><b class="block text-sm">Removal of Rings / Goal (Gym)</b><span class="mt-1 block text-sm text-slate-600">Only available when a gym venue is selected.</span></span></label></div>
                            <div class="mt-5 grid gap-5 sm:grid-cols-2"><div><label for="tables" class="mb-2 block text-sm font-semibold">Number of Tables</label><input id="tables" name="tables" type="number" min="0" value="{{ old('tables') }}" placeholder="0" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-[#B21F24] focus:outline-none focus:ring-4 focus:ring-[#FADBDD]"></div><div><label for="chairs" class="mb-2 block text-sm font-semibold">Number of Chairs</label><input id="chairs" name="chairs" type="number" min="0" value="{{ old('chairs') }}" placeholder="0" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-[#B21F24] focus:outline-none focus:ring-4 focus:ring-[#FADBDD]"><p id="chairsWarning" class="mt-2 hidden text-sm font-semibold text-red-600" role="alert"></p></div></div>
                        </div>
                        <div class="mt-6"><label for="otherRequrements1" class="mb-2 block text-sm font-semibold">Other Requirements</label><textarea id="otherRequrements1" name="otherRequrements1" rows="4" placeholder="Additional equipment, setup, or special requirements..." class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-[#B21F24] focus:outline-none focus:ring-4 focus:ring-[#FADBDD]">{{ old('otherRequrements1') }}</textarea><input type="hidden" name="otherRequrements2" value="">@error('otherRequrements1')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
                    </div>
                </section>
        </div>
        
        <div class="wizard-step space-y-6 hidden" data-step="3">
            <section class="reservation-card">
                <div class="border-b border-slate-100 px-5 py-5 sm:px-7">
                    <h2 class="text-xl font-bold">Agreement and Signature</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Confirm that you understand and agree to the conditions.
                    </p>
                </div>
            
                <div class="space-y-5 p-5 sm:p-7">
                            <div class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm leading-6 text-amber-950" role="note">
                                <p class="font-semibold">Please read</p>
                                <p class="mt-1">If another user's reservation overlaps with yours (a double booking), your name and email may be given to that user so the two of you can talk about the booking, and theirs to you. CPDO may also cancel a reservation and will tell you why.</p>
                            </div>
                            <label for="agreed"
                                class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4">
                                <input id="agreed"
                                    name="agreed"
                                    type="checkbox"
                                    value="1"
                                    required
                                    @checked(old('agreed'))
                                    class="mt-1 h-4 w-4 rounded text-[#B21F24]">
                
                                <span class="text-sm leading-6">
                                   I have read, understood, and agree to follow all the Terms and Conditions and Policies stated above.
                                    <b class="text-red-600">*</b>
                                </span>
                            </label>
                        
                            @error('agreed')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        
                            
                            <div class="border-t border-slate-100 pt-6">
                                <h3 class="text-lg font-bold">Applicant Signature</h3>
                                <p class="mt-1 text-sm text-slate-500">
                                    Draw your signature or upload an image of your handwritten signature.
                                </p>
                            
                                <div class="mt-4">
                                    <label for="signatureMethod" class="mb-2 block text-sm font-semibold">
                                        Signature Method
                                    </label>
                                
                                    <select id="signatureMethod"
                                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3">
                                        <option value="draw">Draw Signature</option>
                                        <option value="upload">Upload Signature</option>
                                    </select>
                                </div>
                            
                                <!-- Draw Signature -->
                                <div id="drawSignatureSection" class="mt-5">
                                    <label class="mb-2 block text-sm font-semibold">
                                        Draw your signature below
                                    </label>
                                
                                    <div class="overflow-hidden rounded-xl border border-slate-300 bg-white">
                                        <canvas id="signatureCanvas"
                                            width="1000"
                                            height="260"
                                            class="block h-40 w-full touch-none cursor-crosshair">
                                        </canvas>
                                    </div>
                                
                                    <div class="mt-2 flex justify-end">
                                        <button type="button"
                                            id="clearSignature"
                                            class="text-sm font-semibold text-red-600 hover:text-red-800">
                                            Clear Signature
                                        </button>
                                    </div>
                                </div>
                            
                                <!-- Upload Signature -->
                                <div id="uploadSignatureSection" class="mt-5 hidden">
                                    <label for="applicant_signature"
                                        class="mb-2 block text-sm font-semibold">
                                        Upload your signature
                                    </label>
                                
                                    <input id="applicant_signature"
                                        name="applicant_signature"
                                        type="file"
                                        accept="image/jpeg,image/png"
                                        class="w-full rounded-xl border border-slate-300 px-4 py-3">
                                
                                    <p class="mt-1 text-xs text-slate-500">
                                        JPG or PNG, up to 10 MB.
                                    </p>
                                </div>
                            
                                <p id="signature-upload-status"
                                    class="mt-2 text-sm text-slate-600"
                                    aria-live="polite"></p>
                            
                                <!-- Printed Name -->
                                <div class="mt-10 text-center">
                                    <div class="mx-auto flex min-h-12 max-w-md items-end justify-center border-b border-slate-700 pb-2">
                                        <span id="printed-applicant-name" class="font-semibold uppercase tracking-wide">
                                            {{ Auth::user()->name }}
                                        </span>
                                    </div>
                                
                                    <p class="mt-2 text-xs text-slate-600">
                                        Signature over printed name of Applicant
                                    </p>
                                </div>
                            </div>

                        </div>
                    </section>
                </div>
                
            

            
                <div class="border-t border-slate-200 pt-6">
            <div class="mb-5 flex items-center justify-between">
                <p id="step-indicator" class="text-sm font-semibold text-slate-600">
                    Step 1 of 4
                </p>
            
                <p class="text-xs text-slate-500">
                    <span class="text-red-600">*</span> Required fields
                </p>
            </div>
        
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-between">
                <a href="{{ url('/dashboard') }}"
                    class="inline-flex justify-center rounded-md border border-[#D9A6AA] bg-white px-6 py-3 text-sm font-bold text-[#4A5568] hover:bg-[#FFF1F2] hover:text-[#B21F24]">
                    Cancel
                </a>
            
                <div class="flex flex-col-reverse gap-3 sm:flex-row">
                    <button type="button"
                        id="prevStep"
                        class="hidden rounded-md border border-[#D9A6AA] px-6 py-3 text-sm font-bold text-[#4A5568] hover:bg-[#FFF1F2] hover:text-[#B21F24]">
                        Back
                    </button>
                
                    <button type="button"
                        id="nextStep"
                        class="rounded-md bg-[#B21F24] px-7 py-3 text-sm font-bold text-white hover:bg-[#8A151B]">
                        Next →
                    </button>
                
                    <button type="submit"
                        id="submitReservation"
                        class="hidden rounded-md bg-[#B21F24] px-7 py-3 text-sm font-bold text-white hover:bg-[#8A151B]">
                        Submit Reservation →
                    </button>
                </div>
            </div>
        </div>
        </form>
        <footer class="mt-8 border-t border-slate-200 pt-5 text-center text-xs text-slate-500">CPDO Venue Reservation System · Contact details to be added</footer>
    </div>
    </main>
</div>

<script>

document.addEventListener('DOMContentLoaded', function () {
    const reservationForm = document.getElementById('reservationForm');

    if (!reservationForm) return;

    // ==========================================
    // 1. WIZARD ELEMENTS
    // ==========================================

    const steps = document.querySelectorAll('.wizard-step');
    const nextButton = document.getElementById('nextStep');
    const prevButton = document.getElementById('prevStep');
    const submitButton = document.getElementById('submitReservation');
    const stepIndicator = document.getElementById('step-indicator');
    const progressSteps = document.querySelectorAll('[data-progress-step]');

    let currentStep = 0;

    // ==========================================
    // 2. SIGNATURE ELEMENTS
    // ==========================================

    const signatureInput = document.getElementById('applicant_signature');
    const signatureUploadStatus = document.getElementById('signature-upload-status');
    const signatureMethod = document.getElementById('signatureMethod');

    const drawSignatureSection = document.getElementById('drawSignatureSection');
    const uploadSignatureSection = document.getElementById('uploadSignatureSection');

    const signatureCanvas = document.getElementById('signatureCanvas');
    const clearSignatureButton = document.getElementById('clearSignature');

    const maxSignatureSize = 10 * 1024 * 1024;

    let isDrawing = false;
    let hasDrawnSignature = false;
    let signaturePrepared = false;
    let signatureOptimized = false;
    let isProcessingSignature = false;

    const signatureContext = signatureCanvas
        ? signatureCanvas.getContext('2d')
        : null;

    if (signatureContext) {
        signatureContext.strokeStyle = '#000000';
        signatureContext.lineWidth = 3;
        signatureContext.lineCap = 'round';
        signatureContext.lineJoin = 'round';
    }

    // ==========================================
    // 3. WIZARD NAVIGATION
    // ==========================================

    function showStep(index, shouldScrollToTop = false) {
        steps.forEach(function (step, i) {
            step.classList.toggle('hidden', i !== index);
        });

        currentStep = index;

        progressSteps.forEach(function (step, progressIndex) {
            const isCurrent = progressIndex === index;
            const isComplete = progressIndex < index;

            step.classList.toggle('reservation-progress-current', isCurrent);
            step.classList.toggle('reservation-progress-complete', isComplete);

            if (isCurrent) {
                step.setAttribute('aria-current', 'step');
            } else {
                step.removeAttribute('aria-current');
            }
        });

        if (stepIndicator) {
            stepIndicator.textContent =
                `Step ${index + 1} of ${steps.length}`;
        }

        if (prevButton) {
            prevButton.classList.toggle('hidden', index === 0);
        }

        if (nextButton) {
            nextButton.classList.toggle(
                'hidden',
                index === steps.length - 1
            );
        }

        if (submitButton) {
            submitButton.classList.toggle(
                'hidden',
                index !== steps.length - 1
            );
        }

        if (shouldScrollToTop) {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

    }

    function validateStep(index) {
        const fields = steps[index].querySelectorAll(
            'input, select, textarea'
        );

        for (const field of fields) {
            if (
                field.disabled ||
                field.type === 'hidden' ||
                field.type === 'file'
            ) {
                continue;
            }

            if (!field.checkValidity()) {
                field.reportValidity();
                field.focus();
                return false;
            }
        }

        return true;
    }

    if (nextButton) {
        nextButton.addEventListener('click', function () {
            if (!validateStep(currentStep)) return;

            if (currentStep < steps.length - 1) {
                showStep(currentStep + 1, true);
            }
        });
    }

    if (prevButton) {
        prevButton.addEventListener('click', function () {
            if (currentStep > 0) {
                showStep(currentStep - 1, true);
            }
        });
    }

    // ==========================================
    // 4. VENUE SELECTION
    // ==========================================

    const venueSelect = document.getElementById('venue');
    const otherVenueWrap = document.getElementById('other-venue-wrap');
    const otherVenueInput = document.getElementById('otherVenue');

    const removalContainer = document.getElementById(
        'removalOfRingsContainer'
    );

    const removalCheckbox = document.getElementById('removalOfRings');

    function updateVenueFields() {
        if (!venueSelect) return;

        const showOther = venueSelect.value === 'other';

        otherVenueWrap.classList.toggle('hidden', !showOther);

        otherVenueInput.required = showOther;

        if (!showOther) {
            otherVenueInput.value = '';
        }

        const chosen = venueSelect.options[venueSelect.selectedIndex];
        const isGym = (chosen?.dataset.name || '').toLowerCase().includes('gym');

        removalContainer.classList.toggle('hidden', !isGym);

        if (!isGym) {
            removalCheckbox.checked = false;
        }
    }

    // Live check: chairs cannot exceed the chosen venue's capacity
    const chairsInput = document.getElementById('chairs');
    const chairsWarning = document.getElementById('chairsWarning');

    function checkChairs() {
        if (!chairsInput || !venueSelect) return true;

        const chosen = venueSelect.options[venueSelect.selectedIndex];
        const max = parseInt(chosen?.dataset.capacity || '', 10);
        const name = chosen?.dataset.name || 'This venue';
        const value = parseInt(chairsInput.value || '0', 10);

        if (!isNaN(max) && max > 0) {
            chairsInput.max = max;
        } else {
            chairsInput.removeAttribute('max');
        }

        if (!isNaN(max) && max > 0 && value > max) {
            const message = name + ' holds up to ' + max.toLocaleString() + ' people. Please enter ' + max.toLocaleString() + ' chairs or fewer.';
            chairsWarning.textContent = message;
            chairsWarning.classList.remove('hidden');
            chairsInput.classList.add('border-red-500');
            chairsInput.setCustomValidity(message);
            return false;
        }

        chairsWarning.classList.add('hidden');
        chairsInput.classList.remove('border-red-500');
        chairsInput.setCustomValidity('');
        return true;
    }

    if (chairsInput) {
        chairsInput.addEventListener('input', checkChairs);
    }

    if (venueSelect) {
        venueSelect.addEventListener('change', function () {
            updateVenueFields();
            checkChairs();
        });
        updateVenueFields();
        checkChairs();
    }

    // ==========================================
    // 5. RESERVATION DATE HANDLING
    // ==========================================

    const singleDayRadio = document.getElementById('singleDay');
    const multipleDaysRadio = document.getElementById('multipleDays');

    const singleDateContainer = document.getElementById(
        'singleDateContainer'
    );

    const dateRangeContainer = document.getElementById(
        'dateRangeContainer'
    );

    const reservedDateInput = document.getElementById('reservedDate');
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');

    function updateDateFields() {
        if (!singleDayRadio || !multipleDaysRadio) return;

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

        endDateInput.min =
            startDateInput.value || startDateInput.min;
    }

    if (singleDayRadio && multipleDaysRadio) {
        singleDayRadio.addEventListener('change', updateDateFields);
        multipleDaysRadio.addEventListener('change', updateDateFields);

        updateDateFields();
    }

    // Live checks: no Sundays, and nothing may run past the closing time (9:00 PM)
    const dateWarning = document.getElementById('dateWarning');
    const timeWarning = document.getElementById('timeWarning');
    const startTimeInput = document.getElementById('startTime');
    const endTimeInput = document.getElementById('endTime');
    const closingTime = @json(config('reservation.closing_time'));

    function hasSunday(from, to) {
        if (!from) return false;
        const last = new Date((to || from) + 'T00:00:00');
        for (let d = new Date(from + 'T00:00:00'); d <= last; d.setDate(d.getDate() + 1)) {
            if (d.getDay() === 0) return true;
        }
        return false;
    }

    function checkDates() {
        if (!reservedDateInput) return true;
        const multiple = multipleDaysRadio && multipleDaysRadio.checked;
        const from = multiple ? startDateInput.value : reservedDateInput.value;
        const to = multiple ? endDateInput.value : reservedDateInput.value;
        const bad = hasSunday(from, to);
        const message = multiple
            ? 'Student activities cannot be held on Sundays. Your dates include a Sunday - please choose other dates.'
            : 'Student activities cannot be held on Sundays. Please choose another date.';

        [reservedDateInput, startDateInput, endDateInput].forEach(function (input) {
            input.setCustomValidity('');
        });
        (multiple ? startDateInput : reservedDateInput).setCustomValidity(bad ? message : '');

        if (dateWarning) {
            dateWarning.textContent = bad ? message : '';
            dateWarning.classList.toggle('hidden', !bad);
        }

        return !bad;
    }

    function checkTimes() {
        if (!endTimeInput) return true;
        const late = (startTimeInput.value && startTimeInput.value > closingTime) || (endTimeInput.value && endTimeInput.value > closingTime);
        const message = 'Activities must end by 9:00 PM. Please choose an earlier time.';

        startTimeInput.setCustomValidity('');
        endTimeInput.setCustomValidity(late ? message : '');

        if (timeWarning) {
            timeWarning.textContent = late ? message : '';
            timeWarning.classList.toggle('hidden', !late);
        }

        return !late;
    }

    [reservedDateInput, startDateInput, endDateInput].forEach(function (input) {
        if (input) input.addEventListener('input', checkDates);
        if (input) input.addEventListener('change', checkDates);
    });
    [singleDayRadio, multipleDaysRadio].forEach(function (radio) {
        if (radio) radio.addEventListener('change', checkDates);
    });
    [startTimeInput, endTimeInput].forEach(function (input) {
        if (input) input.addEventListener('input', checkTimes);
    });
    checkDates();
    checkTimes();

    if (startDateInput && endDateInput) {
        startDateInput.addEventListener('change', function () {
            endDateInput.min = startDateInput.value || endDateInput.min;

            if (
                endDateInput.value &&
                endDateInput.value < startDateInput.value
            ) {
                endDateInput.value = '';
            }
        });
    }

    // ==========================================
    // 6. SIGNATURE DRAWING
    // ==========================================

    function clearSignature() {
        if (!signatureContext) return;

        signatureContext.clearRect(
            0,
            0,
            signatureCanvas.width,
            signatureCanvas.height
        );

        hasDrawnSignature = false;
        signaturePrepared = false;
    }

    function updateSignatureMethod() {
        const isDraw = signatureMethod.value === 'draw';

        drawSignatureSection.classList.toggle('hidden', !isDraw);
        uploadSignatureSection.classList.toggle('hidden', isDraw);

        clearSignature();

        signatureInput.value = '';

        signaturePrepared = false;
        signatureOptimized = false;

        signatureUploadStatus.textContent = '';
    }

    if (signatureMethod) {
        signatureMethod.addEventListener(
            'change',
            updateSignatureMethod
        );

        updateSignatureMethod();
    }

    if (signatureCanvas && signatureContext) {

        signatureCanvas.addEventListener('pointerdown', function (event) {
            const rect = signatureCanvas.getBoundingClientRect();

            isDrawing = true;
            hasDrawnSignature = true;

            signatureInput.value = '';

            signatureCanvas.setPointerCapture(event.pointerId);

            signatureContext.beginPath();

            signatureContext.moveTo(
                (event.clientX - rect.left) *
                    signatureCanvas.width / rect.width,

                (event.clientY - rect.top) *
                    signatureCanvas.height / rect.height
            );
        });

        signatureCanvas.addEventListener('pointermove', function (event) {
            if (!isDrawing) return;

            const rect = signatureCanvas.getBoundingClientRect();

            signatureContext.lineTo(
                (event.clientX - rect.left) *
                    signatureCanvas.width / rect.width,

                (event.clientY - rect.top) *
                    signatureCanvas.height / rect.height
            );

            signatureContext.stroke();
        });

        function stopDrawing() {
            isDrawing = false;
        }

        signatureCanvas.addEventListener('pointerup', stopDrawing);
        signatureCanvas.addEventListener('pointercancel', stopDrawing);
        signatureCanvas.addEventListener('lostpointercapture', stopDrawing);
    }

    if (clearSignatureButton) {
        clearSignatureButton.addEventListener('click', function () {
            clearSignature();

            signatureInput.value = '';
            signatureUploadStatus.textContent = '';
        });
    }

    // ==========================================
    // 7. SIGNATURE PREPARATION
    // ==========================================

    function prepareDrawnSignature() {
        return new Promise(function (resolve, reject) {
            signatureCanvas.toBlob(function (blob) {
                if (!blob) {
                    reject(new Error('Could not process signature.'));
                    return;
                }

                const file = new File(
                    [blob],
                    'applicant-signature.png',
                    {
                        type: 'image/png',
                        lastModified: Date.now()
                    }
                );

                const transfer = new DataTransfer();
                transfer.items.add(file);

                signatureInput.files = transfer.files;

                resolve();
            }, 'image/png');
        });
    }

    // ==========================================
    // 8. SIGNATURE IMAGE OPTIMIZATION
    // ==========================================

    async function optimizeSignature() {
        const signature = signatureInput.files[0];

        if (
            !signature ||
            signature.size <= maxSignatureSize ||
            signatureOptimized
        ) {
            return;
        }

        signatureUploadStatus.textContent =
            'Optimizing signature image…';

        signatureUploadStatus.classList.remove('text-red-600');
        signatureUploadStatus.classList.add('text-slate-600');

        let image = null;

        try {
            image = await createImageBitmap(signature);

            let scale = Math.min(
                1,
                1600 / Math.max(image.width, image.height)
            );

            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');

            if (!context) {
                throw new Error('Image processing is unavailable.');
            }

            let optimizedImage = null;

            for (let attempt = 0; attempt < 8; attempt++) {
                canvas.width = Math.max(
                    1,
                    Math.round(image.width * scale)
                );

                canvas.height = Math.max(
                    1,
                    Math.round(image.height * scale)
                );

                context.fillStyle = '#ffffff';

                context.fillRect(
                    0,
                    0,
                    canvas.width,
                    canvas.height
                );

                context.drawImage(
                    image,
                    0,
                    0,
                    canvas.width,
                    canvas.height
                );

                const quality = Math.max(
                    0.35,
                    0.85 - attempt * 0.1
                );

                optimizedImage = await new Promise(function (resolve) {
                    canvas.toBlob(resolve, 'image/jpeg', quality);
                });

                if (
                    optimizedImage &&
                    optimizedImage.size <= maxSignatureSize
                ) {
                    break;
                }

                scale *= 0.75;
            }

            if (image) {
                image.close();
                image = null;
            }

            if (
                !optimizedImage ||
                optimizedImage.size > maxSignatureSize
            ) {
                throw new Error('Image exceeds upload limit.');
            }

            const optimizedFile = new File(
                [optimizedImage],
                signature.name.replace(/\.[^.]+$/, '') + '.jpg',
                {
                    type: 'image/jpeg',
                    lastModified: Date.now()
                }
            );

            const transfer = new DataTransfer();
            transfer.items.add(optimizedFile);

            signatureInput.files = transfer.files;

            signatureOptimized = true;

        } finally {
            if (image) {
                image.close();
            }
        }
    }

    // ==========================================
    // 9. FINAL FORM SUBMISSION
    // ==========================================

    reservationForm.addEventListener('submit', async function (event) {

        // Validate all wizard steps.
        for (let i = 0; i < steps.length; i++) {
            if (!validateStep(i)) {
                event.preventDefault();
                showStep(i);
                validateStep(i);
                return;
            }
        }

        if (isProcessingSignature) {
            event.preventDefault();
            return;
        }

        if (
            signatureMethod &&
            signatureMethod.value === 'draw' &&
            hasDrawnSignature &&
            !signaturePrepared
        ) {
            event.preventDefault();

            isProcessingSignature = true;

            try {
                await prepareDrawnSignature();

                signaturePrepared = true;
                isProcessingSignature = false;

                reservationForm.requestSubmit();

            } catch (error) {
                isProcessingSignature = false;

                signatureUploadStatus.textContent =
                    'Could not prepare your drawn signature. Please try again.';

                signatureUploadStatus.classList.add('text-red-600');
            }

            return;
        }

        try {
            await optimizeSignature();

            signatureUploadStatus.textContent =
                signatureInput.files.length
                    ? 'Signature ready. Submitting reservation…'
                    : '';

        } catch (error) {
            event.preventDefault();

            signatureUploadStatus.textContent =
                'Could not optimize this image. Please choose a JPG or PNG signature image under 10 MB.';

            signatureUploadStatus.classList.remove('text-slate-600');
            signatureUploadStatus.classList.add('text-red-600');

            return;
        }

    });

    // ==========================================
    // 10. INITIALIZE WIZARD
    // ==========================================

    if (steps.length > 0) {
        const initialStep = Number(reservationForm.dataset.initialStep || 0);
        showStep(initialStep);
    }

});

</script>
</body>
</html>
