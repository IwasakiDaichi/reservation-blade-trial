
<nav x-data="{ open: false }" class="sticky top-0 z-50 border-b border-slate-200 bg-white">

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">

            {{-- Brand --}}
            <div class="flex shrink-0 items-center">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <x-application-logo class="block h-9 w-auto fill-current text-indigo-600" />

                    <div class="hidden sm:block">
                        <p class="text-sm font-bold tracking-tight text-slate-800">
                            CPDO Venue Reservation
                        </p>
                        <p class="text-xs text-slate-500">
                            Reservation Management
                        </p>
                    </div>
                </a>
            </div>

            {{-- Desktop navigation --}}
            <div class="hidden items-center gap-2 sm:flex">

                @if (auth()->user()->role === 'cpdo_admin')
                    <x-nav-link
                        :href="route('admin.reservations.index')"
                        :active="request()->routeIs('admin.reservations.*')">
                        Manage Reservation
                    </x-nav-link>

                    <x-nav-link
                        :href="route('admin.venues.index')"
                        :active="request()->routeIs('admin.venues.*')">
                        Venues
                    </x-nav-link>
                @else
                    <x-nav-link
                        :href="route('venues.index')"
                        :active="request()->routeIs('venues.*')">
                        Venues
                    </x-nav-link>

                    <x-nav-link
                        :href="route('reservations.index')"
                        :active="request()->routeIs('reservations.index')">
                        My Reservations
                    </x-nav-link>

                    <x-nav-link
                        :href="route('reservations.create')"
                        :active="request()->routeIs('reservations.create')">
                        Make a Reservation
                    </x-nav-link>
                @endif
            </div>

            {{-- Account dropdown --}}
            <div class="hidden items-center sm:flex">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center gap-2 rounded-md border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition-colors hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-700 focus:ring-offset-2">

                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-sm font-semibold text-slate-700">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </span>

                            <span class="max-w-36 truncate">
                                {{ Auth::user()->name }}
                            </span>

                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 0l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="border-b border-slate-200 px-4 py-3">
                            <p class="truncate text-sm font-semibold text-slate-800">
                                {{ Auth::user()->name }}
                            </p>
                            <p class="truncate text-xs text-slate-500">
                                {{ Auth::user()->email }}
                            </p>
                        </div>

                        <x-dropdown-link :href="route('profile.edit')">
                            Profile
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link
                                :href="route('logout')"
                                class="text-red-700 hover:bg-red-50 hover:text-red-800 focus:bg-red-50"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                Log Out
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            {{-- Mobile menu button --}}
            <div class="flex items-center sm:hidden">
                <button
                    @click="open = !open"
                    type="button"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 p-2 text-slate-600 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                    :aria-expanded="open.toString()"
                    aria-label="Toggle navigation menu">

                    <svg x-show="!open" class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>

                    <svg x-show="open" x-cloak class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

        </div>
    </div>

    {{-- Responsive navigation --}}
    <div x-show="open" x-cloak class="border-t border-slate-200 bg-white sm:hidden">

        <div class="space-y-1 px-4 py-3">
            @if (auth()->user()->role === 'cpdo_admin')
                <x-responsive-nav-link
                    :href="route('admin.reservations.index')"
                    :active="request()->routeIs('admin.reservations.*')">
                    Admin Dashboard
                </x-responsive-nav-link>

                <x-responsive-nav-link
                    :href="route('admin.venues.index')"
                    :active="request()->routeIs('admin.venues.*')">
                    Venues
                </x-responsive-nav-link>
            @else
                <x-responsive-nav-link
                    :href="route('venues.index')"
                    :active="request()->routeIs('venues.*')">
                    Venues
                </x-responsive-nav-link>

                <x-responsive-nav-link
                    :href="route('reservations.index')"
                    :active="request()->routeIs('reservations.index')">
                    My Reservations
                </x-responsive-nav-link>

                <x-responsive-nav-link
                    :href="route('reservations.create')"
                    :active="request()->routeIs('reservations.create')">
                    Make a Reservation
                </x-responsive-nav-link>
            @endif
        </div>

        {{-- Mobile account information --}}
        <div class="border-t border-slate-100 px-4 py-4">
            <p class="text-sm font-semibold text-slate-800">
                {{ Auth::user()->name }}
            </p>
            <p class="break-all text-xs text-slate-500">
                {{ Auth::user()->email }}
            </p>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    Profile
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link
                        :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                        Log Out
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>

</nav>
