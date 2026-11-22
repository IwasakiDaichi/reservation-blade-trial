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
        <section class="relative flex min-h-[650px] flex-1 items-center justify-center overflow-hidden bg-indigo-700 px-6 py-12 sm:px-10 lg:min-h-screen lg:w-[44%] lg:flex-none lg:px-12">
            <div class="pointer-events-none absolute -left-24 -top-24 h-80 w-80 rounded-full bg-indigo-500/50 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-20 -right-20 h-72 w-72 rounded-full bg-blue-400/20 blur-3xl"></div>

            <div class="relative z-10 w-full max-w-md">
                <div class="mb-10 text-center">
                    <img src="{{ asset('images/ub-seal.png') }}" alt="University of Baguio seal"
                        class="mx-auto mb-5 h-28 w-28 drop-shadow-lg">
                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-indigo-200">Campus Services</p>
                    <h1 class="mt-3 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">CPDO</h1>
                    <p class="mt-2 text-sm font-medium tracking-[0.18em] text-indigo-100">VENUE RESERVATION SYSTEM</p>
                </div>

                <div class="rounded-3xl bg-slate-950/85 p-7 shadow-2xl shadow-indigo-950/20 ring-1 ring-white/10 sm:p-9">
                    <div class="mb-7 text-center">
                        <p class="text-sm font-medium text-indigo-200">Welcome</p>
                        <h2 class="mt-2 text-2xl font-bold text-white">Your event starts here.</h2>
                        <p class="mt-3 text-sm leading-6 text-slate-300">Choose how you would like to continue.</p>
                    </div>

                    @if (session('login_error'))
                        <div class="mb-4 rounded-xl border border-red-400/40 bg-red-500/10 px-4 py-3 text-sm text-red-200" role="alert">
                            {{ session('login_error') }}
                        </div>
                    @endif

                    @auth
                        <a href="{{ route('dashboard') }}" class="flex w-full items-center justify-center gap-3 rounded-xl bg-white px-5 py-4 text-sm font-bold text-indigo-700 shadow-lg transition hover:bg-indigo-50 focus:outline-none focus:ring-4 focus:ring-white/30">
                            Go to Dashboard
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7 7 7-7 7" /></svg>
                        </a>
                        <p class="mt-4 text-center text-xs text-slate-300">
                            Signed in as <strong class="text-white">{{ auth()->user()->email }}</strong>
                            ({{ auth()->user()->role === 'cpdo_admin' ? 'CPDO staff' : 'Student' }})
                        </p>
                        <form method="POST" action="{{ route('logout') }}" class="mt-2 text-center">
                            @csrf
                            <button type="submit" class="text-sm font-semibold text-indigo-200 underline hover:text-white">Not you? Log out</button>
                        </form>
                    @else
                        <div class="space-y-3">
                            <a href="{{ route('google.redirect') }}" class="flex w-full items-center justify-center gap-3 rounded-xl bg-white px-5 py-4 text-sm font-bold text-indigo-700 shadow-lg transition hover:bg-indigo-50 focus:outline-none focus:ring-4 focus:ring-white/30">
                                <svg class="h-5 w-5" viewBox="0 0 48 48" aria-hidden="true"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5Z" /><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.76 7.18l7.73 6C44.42 37.95 46.98 31.7 46.98 24.55Z" /><path fill="#FBBC05" d="M10.53 28.59A14.4 14.4 0 0 1 9.75 24c0-1.59.27-3.13.76-4.59l-7.98-6.19A23.8 23.8 0 0 0 0 24c0 3.86.92 7.5 2.56 10.78l7.97-6.19Z" /><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.91-5.8l-7.73-6c-2.14 1.44-4.89 2.3-8.18 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48Z" /></svg>
                                Log in with your student account
                            </a>

                            <a href="{{ route('walkin.create') }}" class="flex w-full items-center justify-center gap-3 rounded-xl border border-white/20 bg-white/10 px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-white/15 focus:outline-none focus:ring-4 focus:ring-white/20">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19a4 4 0 0 0-8 0m4-8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm8 8a4 4 0 0 0-3-3.87M16 5.13a3 3 0 0 1 0 5.74" /></svg>
                                Walk-in / External organization
                            </a>

                            <a href="{{ route('login') }}" class="flex w-full items-center justify-center gap-3 rounded-xl border border-white/20 px-5 py-3.5 text-sm font-semibold text-indigo-100 transition hover:bg-white/10 focus:outline-none focus:ring-4 focus:ring-white/20">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3 4 6v5c0 5.2 3.4 8.6 8 10 4.6-1.4 8-4.8 8-10V6l-8-3Z" /></svg>
                                CPDO sign in
                            </a>
                        </div>
                        <p class="mt-5 text-center text-xs leading-5 text-slate-400">Students use their University of Baguio account. Walk-ins and external organizations can fill in the form without an account.</p>
                    @endauth

                    <div class="my-6 border-t border-white/10"></div>
                    <div class="flex items-start justify-center gap-2 text-xs leading-5 text-slate-400">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-indigo-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3 4 6v5c0 5.2 3.4 8.6 8 10 4.6-1.4 8-4.8 8-10V6l-8-3Z" /><path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4" /></svg>
                        <p>Access to reservation services is managed through your account.</p>
                    </div>
                </div>
                <p class="mt-8 text-center text-xs text-indigo-200/80">CPDO Venue Reservation System</p>
            </div>
        </section>

        <x-landing-info />
    </main>

    <x-landing-footer />
</body>
</html>
