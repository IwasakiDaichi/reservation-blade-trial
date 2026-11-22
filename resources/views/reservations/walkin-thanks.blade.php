<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation submitted - CPDO Venue Reservation</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
    <main class="mx-auto flex min-h-screen max-w-xl flex-col justify-center px-4 py-10">
        <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
            <x-application-logo class="mx-auto h-14 w-auto" />

            <h1 class="mt-6 text-center text-2xl font-bold">Reservation submitted</h1>
            <p class="mt-2 text-center text-sm text-slate-600">
                Your request is now <strong>pending</strong> with CPDO.
            </p>

            <dl class="mt-6 divide-y divide-slate-100 rounded-xl border border-slate-200 text-sm">
                <div class="flex justify-between gap-4 px-4 py-3"><dt class="text-slate-500">Reference no.</dt><dd class="font-semibold">#{{ $reservation->id }}</dd></div>
                <div class="flex justify-between gap-4 px-4 py-3"><dt class="text-slate-500">Applicant</dt><dd class="font-semibold">{{ $reservation->applicantName() }}</dd></div>
                <div class="flex justify-between gap-4 px-4 py-3"><dt class="text-slate-500">Venue</dt><dd class="font-semibold">{{ $reservation->venueName() }}</dd></div>
                <div class="flex justify-between gap-4 px-4 py-3"><dt class="text-slate-500">Date</dt><dd class="font-semibold">{{ $reservation->dateText() }}</dd></div>
                <div class="flex justify-between gap-4 px-4 py-3"><dt class="text-slate-500">Time</dt><dd class="font-semibold">{{ $reservation->time }}</dd></div>
            </dl>

            <p class="mt-5 text-sm leading-6 text-slate-600">
                Walk-in and external applicants do not have an account, so please <strong>print this form and bring it to the CPDO office</strong>
                to follow up on your reservation. Keep your reference number.
            </p>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                <a href="{{ $printUrl }}" target="_blank" rel="noopener"
                    class="inline-flex flex-1 justify-center rounded-xl bg-blue-700 px-5 py-3 text-sm font-bold text-white hover:bg-blue-800">
                    Print reservation form
                </a>
                <a href="{{ url('/') }}"
                    class="inline-flex flex-1 justify-center rounded-xl border border-slate-300 px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">
                    Back to home
                </a>
            </div>
        </div>
    </main>
</body>
</html>
