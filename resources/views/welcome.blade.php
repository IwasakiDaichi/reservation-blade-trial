<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>University of Baguio | CPDO Venue Reservation</title>
    <link rel="icon" type="image/png" href="{{ asset('images/ub-seal.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#FFF8F8] font-sans antialiased text-[#1A202C]">
    <main class="mx-auto flex min-h-screen max-w-[1800px] flex-col lg:flex-row">
        <section class="relative flex min-h-[650px] flex-1 items-center justify-center overflow-hidden bg-[#B21F24] px-6 py-12 sm:px-10 lg:min-h-screen lg:w-[44%] lg:flex-none lg:px-12">
            <div class="pointer-events-none absolute -left-24 -top-24 h-80 w-80 rounded-full bg-[#8A151B]/35 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-20 -right-20 h-72 w-72 rounded-full bg-white/10 blur-3xl"></div>

            <div class="relative z-10 w-full max-w-md">
                <div class="mb-10 text-center">
                    <img src="{{ asset('images/ub-seal.png') }}" alt="University of Baguio seal"
                        class="mx-auto mb-5 h-28 w-28 rounded-full shadow-[0_8px_18px_rgba(55,8,10,0.38)]">
                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-[#FFE5E6]">Campus Services</p>
                    <h1 class="mt-3 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">CPDO</h1>
                    <p class="mt-2 text-sm font-medium tracking-[0.18em] text-[#FFE5E6]">VENUE RESERVATION SYSTEM</p>
                </div>

                <div class="rounded-lg border border-white/20 bg-[#8A151B] p-7 shadow-[0_2px_8px_rgba(0,0,0,0.16)] sm:p-9">
                    <div class="mb-7 text-center">
                        <p class="text-sm font-medium text-[#FFE5E6]">Welcome</p>
                        <h2 class="mt-2 text-2xl font-bold text-white">Your event starts here.</h2>
                        <p class="mt-3 text-sm leading-6 text-slate-300">Choose how you would like to continue.</p>
                    </div>

                    @if (session('login_error'))
                        <div class="mb-4 rounded-xl border border-red-400/40 bg-red-500/10 px-4 py-3 text-sm text-red-200" role="alert">
                            {{ session('login_error') }}
                        </div>
                    @endif

                    @auth
                        <a href="{{ route('dashboard') }}" class="flex w-full items-center justify-center gap-3 rounded-md bg-white px-5 py-4 text-sm font-bold text-[#B21F24] transition hover:bg-[#FFF1F2] focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-[#8A151B]">
                            Go to Dashboard
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7 7 7-7 7" /></svg>
                        </a>
                        <p class="mt-4 text-center text-xs text-slate-300">
                            Signed in as <strong class="text-white">{{ auth()->user()->email }}</strong>
                            ({{ auth()->user()->role === 'cpdo_admin' ? 'CPDO staff' : 'Student' }})
                        </p>
                        <form method="POST" action="{{ route('logout') }}" class="mt-2 text-center">
                            @csrf
                            <button type="submit" class="text-sm font-semibold text-[#FFE5E6] underline hover:text-white">Not you? Log out</button>
                        </form>
                    @else
                        <div class="space-y-3">
                            <a href="{{ route('google.redirect') }}" class="flex w-full items-center justify-center gap-3 rounded-md bg-white px-5 py-4 text-sm font-bold text-[#B21F24] transition hover:bg-[#FFF1F2] focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-[#8A151B]">
                                <svg class="h-5 w-5" viewBox="0 0 48 48" aria-hidden="true"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5Z" /><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.76 7.18l7.73 6C44.42 37.95 46.98 31.7 46.98 24.55Z" /><path fill="#FBBC05" d="M10.53 28.59A14.4 14.4 0 0 1 9.75 24c0-1.59.27-3.13.76-4.59l-7.98-6.19A23.8 23.8 0 0 0 0 24c0 3.86.92 7.5 2.56 10.78l7.97-6.19Z" /><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.91-5.8l-7.73-6c-2.14 1.44-4.89 2.3-8.18 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48Z" /></svg>
                                Log in with your student account
                            </a>

                            <a href="{{ route('login') }}" class="flex w-full items-center justify-center gap-3 rounded-md border border-white/70 px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-[#741116] focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-[#8A151B]">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3 4 6v5c0 5.2 3.4 8.6 8 10 4.6-1.4 8-4.8 8-10V6l-8-3Z" /></svg>
                                CPDO sign in
                            </a>
                        </div>
                        <p class="mt-5 text-center text-xs leading-5 text-slate-400">Students use their University of Baguio account.</p>
                    @endauth

                    <div class="my-6 border-t border-white/10"></div>
                    <div class="flex items-start justify-center gap-2 text-xs leading-5 text-slate-400">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-[#FFE5E6]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3 4 6v5c0 5.2 3.4 8.6 8 10 4.6-1.4 8-4.8 8-10V6l-8-3Z" /><path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4" /></svg>
                        <p>Access to reservation services is managed through your account.</p>
                    </div>
                </div>
                <p class="mt-8 text-center text-xs text-[#FFE5E6]">CPDO Venue Reservation System</p>
            </div>
        </section>

        <x-landing-info />
    </main>

    <x-landing-footer />
</body>
</html>
