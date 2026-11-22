
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>University of Baguio | CPDO Venue Reservation</title>
        <link rel="icon" type="image/png" href="{{ asset('images/ub-seal.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts and Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="font-sans antialiased text-slate-800">
        <main class="relative flex min-h-screen items-center justify-center overflow-hidden bg-[#FFF8F8] px-4 py-10 sm:px-6">

            {{-- Decorative background shapes --}}
            <div class="pointer-events-none absolute -left-24 -top-24 h-72 w-72 rounded-full bg-[#FADBDD] blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-24 -right-24 h-80 w-80 rounded-full bg-[#FCEDEE] blur-3xl"></div>

            {{-- Login and registration content --}}
            <div class="relative z-10 w-full max-w-md">

                {{-- Brand --}}
                <div class="mb-8 text-center">
                    <a href="/" class="inline-flex flex-col items-center gap-3">
                        <x-application-logo class="h-16 w-auto" />

                        <span class="text-xl font-bold tracking-tight text-[#1A202C]">
                            CPDO Venue Reservation
                        </span>
                    </a>

                    <p class="mt-2 text-sm text-[#B21F24]">
                        Venue reservations made simpler.
                    </p>
                </div>

                {{-- Main card --}}
                <section class="rounded-lg border border-[#F1C9CC] bg-white p-6 shadow-[0_2px_8px_rgba(0,0,0,0.08)] sm:p-8">
                    {{ $slot }}
                </section>

                {{-- Footer --}}
                <p class="mt-6 text-center text-xs text-[#4A5568]">
                    CPDO Venue Reservation System
                </p>

            </div>
        </main>
    </body>
</html>
