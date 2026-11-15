
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CPDO Venue Reservation') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 font-sans antialiased text-slate-800">

    <main class="mx-auto flex min-h-screen max-w-[1800px] flex-col lg:flex-row">

        {{-- LEFT PANEL: BRANDING AND SIGN IN --}}
        <section class="relative flex min-h-[650px] flex-1 items-center justify-center overflow-hidden bg-indigo-700 px-6 py-12 sm:px-10 lg:min-h-screen lg:w-[44%] lg:flex-none lg:px-12">

            {{-- Decorative background shapes --}}
            <div class="pointer-events-none absolute -left-24 -top-24 h-80 w-80 rounded-full bg-indigo-500/50 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-20 -right-20 h-72 w-72 rounded-full bg-blue-400/20 blur-3xl"></div>

            <div class="relative z-10 w-full max-w-md">

                {{-- Branding --}}
                <div class="mb-10 text-center">
                    <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-3xl border border-white/20 bg-white/10 shadow-lg backdrop-blur-sm">
                        <svg class="h-11 w-11 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6M8 10h.01M12 10h.01M16 10h.01" />
                        </svg>
                    </div>

                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-indigo-200">
                        Campus Services
                    </p>

                    <h1 class="mt-3 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                        CPDO
                    </h1>

                    <p class="mt-2 text-sm font-medium tracking-[0.18em] text-indigo-100">
                        VENUE RESERVATION SYSTEM
                    </p>
                </div>

                {{-- Sign-in card --}}
                <div class="rounded-3xl bg-slate-950/85 p-7 shadow-2xl shadow-indigo-950/20 ring-1 ring-white/10 sm:p-9">

                    <div class="mb-7 text-center">
                        <p class="text-sm font-medium text-indigo-200">
                            Welcome
                        </p>

                        <h2 class="mt-2 text-2xl font-bold text-white">
                            Your event starts here.
                        </h2>

                        <p class="mt-3 text-sm leading-6 text-slate-300">
                            Sign in to request a campus venue and keep track of your reservations.
                        </p>
                    </div>

                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            class="flex w-full items-center justify-center gap-3 rounded-xl bg-white px-5 py-4 text-sm font-bold text-indigo-700 shadow-lg transition hover:bg-indigo-50 focus:outline-none focus:ring-4 focus:ring-white/30">
                            Go to Dashboard
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7 7 7-7 7" />
                            </svg>
                        </a>
                    @else
                        @if (Route::has('login'))
                            <a
                                href="{{ route('login') }}"
                                class="flex w-full items-center justify-center gap-3 rounded-xl bg-white px-5 py-4 text-sm font-bold text-indigo-700 shadow-lg transition hover:bg-indigo-50 focus:outline-none focus:ring-4 focus:ring-white/30">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M3 12h18" />
                                </svg>
                                Sign in to your account
                            </a>
                        @endif

                        @if (Route::has('google.redirect'))
                            <a
                                href="{{ route('google.redirect') }}"
                                class="mt-3 flex w-full items-center justify-center gap-3 rounded-xl border border-white/20 bg-white/10 px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-white/15 focus:outline-none focus:ring-4 focus:ring-white/20">
                                <svg class="h-5 w-5" viewBox="0 0 48 48" aria-hidden="true">
                                    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5Z" />
                                    <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.76 7.18l7.73 6C44.42 37.95 46.98 31.7 46.98 24.55Z" />
                                    <path fill="#FBBC05" d="M10.53 28.59A14.4 14.4 0 0 1 9.75 24c0-1.59.27-3.13.76-4.59l-7.98-6.19A23.8 23.8 0 0 0 0 24c0 3.86.92 7.5 2.56 10.78l7.97-6.19Z" />
                                    <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.91-5.8l-7.73-6c-2.14 1.44-4.89 2.3-8.18 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48Z" />
                                </svg>
                                Continue with Google
                            </a>
                        @endif

                        <p class="mt-5 text-center text-xs leading-5 text-slate-400">
                            Use your account to access the reservation system.
                        </p>
                    @endauth

                    <div class="my-6 border-t border-white/10"></div>

                    <div class="flex items-start justify-center gap-2 text-xs leading-5 text-slate-400">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-indigo-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3 4 6v5c0 5.2 3.4 8.6 8 10 4.6-1.4 8-4.8 8-10V6l-8-3Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4" />
                        </svg>
                        <p>Access to reservation services is managed through your account.</p>
                    </div>
                </div>

                <p class="mt-8 text-center text-xs text-indigo-200/80">
                    CPDO Venue Reservation System
                </p>
            </div>
        </section>

        {{-- RIGHT PANEL: INFORMATION AND FEATURES --}}
        <section class="relative flex flex-1 flex-col justify-center overflow-hidden bg-slate-100 px-5 py-12 sm:px-10 lg:min-h-screen lg:px-12 xl:px-16">

            {{-- Subtle background decoration --}}
            <div class="pointer-events-none absolute -right-24 top-10 h-72 w-72 rounded-full bg-indigo-200/40 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-24 left-10 h-64 w-64 rounded-full bg-blue-200/40 blur-3xl"></div>

            <div class="relative z-10 mx-auto w-full max-w-2xl">

                {{-- Section heading --}}
                <div class="mb-8">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-700">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18m-9-9h18" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 5h14v14H5z" />
                            </svg>
                        </div>

                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.22em] text-indigo-600">
                                Campus Venue Services
                            </p>
                            <h2 class="mt-1 text-3xl font-extrabold tracking-tight text-slate-800 sm:text-4xl">
                                GET STARTED
                            </h2>
                        </div>
                    </div>

                    <p class="mt-4 max-w-xl text-sm leading-6 text-slate-600 sm:text-base">
                        Everything you need to begin your campus venue reservation request, organized in one place.
                    </p>

                    <div class="mt-5 h-1 w-full rounded-full bg-slate-200">
                        <div class="h-1 w-24 rounded-full bg-indigo-600"></div>
                    </div>
                </div>

                {{-- Information cards --}}
                <div class="space-y-3">

                    {{-- Card 1 --}}
                    <article class="group flex overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <div class="flex w-16 shrink-0 items-center justify-center bg-indigo-700 text-white sm:w-20">
                            <svg class="h-7 w-7 sm:h-8 sm:w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                <rect x="4" y="5" width="16" height="16" rx="2" />
                                <path stroke-linecap="round" d="M8 3v4m8-4v4M4 10h16" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="m9 15 2 2 4-4" />
                            </svg>
                        </div>

                        <div class="flex min-w-0 flex-1 items-center justify-between gap-3 p-4 sm:p-5">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">Step 01</p>
                                <h3 class="mt-1 text-sm font-bold text-slate-800 sm:text-base">Submit a reservation request</h3>
                                <p class="mt-1 text-xs leading-5 text-slate-500 sm:text-sm">
                                    Enter your event details, preferred venue, date, and requirements.
                                </p>
                            </div>
                            <svg class="h-5 w-5 shrink-0 text-slate-300 transition group-hover:translate-x-1 group-hover:text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7 7 7-7 7" />
                            </svg>
                        </div>
                    </article>

                    {{-- Card 2 --}}
                    <article class="group flex overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <div class="flex w-16 shrink-0 items-center justify-center bg-slate-800 text-white sm:w-20">
                            <svg class="h-7 w-7 sm:h-8 sm:w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 5h16v15H4z" />
                                <path stroke-linecap="round" d="M8 3v4m8-4v4M4 10h16" />
                                <circle cx="12" cy="15" r="2.5" />
                            </svg>
                        </div>

                        <div class="flex min-w-0 flex-1 items-center justify-between gap-3 p-4 sm:p-5">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Step 02</p>
                                <h3 class="mt-1 text-sm font-bold text-slate-800 sm:text-base">Review your reservation status</h3>
                                <p class="mt-1 text-xs leading-5 text-slate-500 sm:text-sm">
                                    Return to your account to check the status of your submitted request.
                                </p>
                            </div>
                            <svg class="h-5 w-5 shrink-0 text-slate-300 transition group-hover:translate-x-1 group-hover:text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7 7 7-7 7" />
                            </svg>
                        </div>
                    </article>

                    {{-- Card 3 --}}
                    <article class="group flex overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <div class="flex w-16 shrink-0 items-center justify-center bg-slate-700 text-white sm:w-20">
                            <svg class="h-7 w-7 sm:h-8 sm:w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3 4 6v5c0 5.2 3.4 8.6 8 10 4.6-1.4 8-4.8 8-10V6l-8-3Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4" />
                            </svg>
                        </div>

                        <div class="flex min-w-0 flex-1 items-center justify-between gap-3 p-4 sm:p-5">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Step 03</p>
                                <h3 class="mt-1 text-sm font-bold text-slate-800 sm:text-base">Wait for CPDO review</h3>
                                <p class="mt-1 text-xs leading-5 text-slate-500 sm:text-sm">
                                    Your request is subject to review and approval by the responsible office.
                                </p>
                            </div>
                            <svg class="h-5 w-5 shrink-0 text-slate-300 transition group-hover:translate-x-1 group-hover:text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7 7 7-7 7" />
                            </svg>
                        </div>
                    </article>
                </div>

                {{-- Bottom note --}}
                <div class="mt-6 flex items-start gap-3 rounded-2xl border border-indigo-100 bg-indigo-50/80 p-4">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="12" cy="12" r="9" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 11v5m0-8h.01" />
                    </svg>

                    <p class="text-xs leading-5 text-slate-600 sm:text-sm">
                        Please provide complete and accurate information when submitting your reservation request.
                    </p>
                </div>
                

            </div>
        </section>
    </main>

    {{-- Footer --}}
    <footer class="bg-slate-900 px-6 py-8 text-white sm:px-10">
        <div class="mx-auto flex max-w-7xl flex-col gap-6
                    sm:flex-row sm:items-center sm:justify-between">
    
            {{-- System Information --}}
            <div>
                <p class="text-sm font-bold tracking-wide">
                    CPDO Venue Reservation System
                </p>
                <p class="mt-1 text-sm text-slate-400">
                    Making venue reservations simpler and more accessible.
                </p>
            </div>
        
            {{-- Contact Information --}}
            <div class="sm:text-right">
                <p class="text-sm font-semibold text-white">
                    Need more information?
                </p>
                <p class="mt-1 text-sm text-slate-400">
                    Contact the CPDO office for reservation inquiries.
                </p>
                <p class="mt-2 text-sm font-medium text-indigo-300">
                    Reservations@e.ubaguio.edu
                </p>
            </div>
        
        </div>
    
        <div class="mx-auto mt-6 max-w-7xl border-t border-slate-700 pt-4">
            <p class="text-center text-xs text-slate-500">
                © {{ date('Y') }} CPDO Venue Reservation System. All rights reserved.
            </p>
        </div>
    </footer>

</body>
</html>
