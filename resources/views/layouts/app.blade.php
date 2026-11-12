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
        <div class="min-h-screen bg-[#F5F7FB]">

            {{-- Main navigation --}}
            @include('layouts.navigation')

            {{-- Optional page heading --}}
            @isset($header)
                <header class="border-b border-slate-200/70 bg-white">
                    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        <div class="text-xl font-semibold tracking-tight text-slate-800">
                            {{ $header }}
                        </div>
                    </div>
                </header>
            @endisset

            {{-- Main page content --}}
            <main class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
                {{ $slot }}
            </main>

        </div>
    </body>
</html>