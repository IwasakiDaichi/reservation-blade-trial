
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'CPDO Venue Reservation') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts and Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="font-sans antialiased text-slate-800">
        <main class="relative flex min-h-screen items-center justify-center overflow-hidden bg-[#F5F7FB] px-4 py-10 sm:px-6">

            {{-- Decorative background shapes --}}
            <div class="pointer-events-none absolute -left-24 -top-24 h-72 w-72 rounded-full bg-indigo-100/70 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-24 -right-24 h-80 w-80 rounded-full bg-blue-100/70 blur-3xl"></div>

            {{-- Login and registration content --}}
            <div class="relative z-10 w-full max-w-md">

                {{-- Brand --}}
                <div class="mb-8 text-center">
                    <a href="/" class="inline-flex flex-col items-center gap-3">
                        <x-application-logo class="h-16 w-auto fill-current text-indigo-600" />

                        <span class="text-xl font-bold tracking-tight text-slate-800">
                            CPDO Venue Reservation
                        </span>
                    </a>

                    <p class="mt-2 text-sm text-slate-500">
                        Venue reservations made simpler.
                    </p>
                </div>

                {{-- Main card --}}
                <section class="rounded-2xl border border-white/80 bg-white p-6 shadow-xl shadow-slate-200/60 sm:p-8">
                    {{ $slot }}
                </section>

                {{-- Footer --}}
                <p class="mt-6 text-center text-xs text-slate-400">
                    CPDO Venue Reservation System
                </p>

            </div>
        </main>
    </body>
</html>
