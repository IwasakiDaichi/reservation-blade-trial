
<nav x-data="{ open: false }" class="print:hidden sticky top-0 z-50 border-b border-[#F1C9CC] bg-white">

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">

            {{-- Brand --}}
            <div class="flex shrink-0 items-center">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-md focus:outline-none focus:ring-2 focus:ring-[#B21F24] focus:ring-offset-2">
                    <x-application-logo class="block h-10 w-auto" />

                    <div class="hidden border-l border-[#F1C9CC] pl-3 sm:block">
                        <p class="text-sm font-bold tracking-tight text-[#1A202C]">
                            CPDO
                        </p>
                        <p class="text-xs text-[#B21F24]">
                            Venue Reservation System
                        </p>
                    </div>
                </a>
            </div>

            {{-- Desktop navigation --}}
            <div class="hidden h-full items-center sm:flex">

                @if (auth()->user()->role === 'cpdo_admin')
                    <x-nav-link
                        :href="route('admin.reservations.index')"
                        :active="request()->routeIs('admin.reservations.*')">
                        Manage Reservation
                        @if (($pendingCount = \App\Models\Reservation::where('status', 'pending')->count()) > 0)
                            <span class="ms-1 inline-flex min-w-5 items-center justify-center rounded-full bg-amber-500 px-1.5 text-xs font-bold text-white" title="Pending requests">{{ $pendingCount }}</span>
                        @endif
                    </x-nav-link>

                    <x-nav-link
                        :href="route('admin.calendar')"
                        :active="request()->routeIs('admin.calendar*')">
                        Calendar of Activities
                    </x-nav-link>

                    <x-nav-link
                        :href="route('notifications.index')"
                        :active="request()->routeIs('notifications.index')">
                        Notifications
                        @if (($adminUnread = auth()->user()->unreadNotifications()->count()) > 0)
                            <span class="ms-1 inline-flex min-w-5 items-center justify-center rounded-full bg-red-600 px-1.5 text-xs font-bold text-white">{{ $adminUnread }}</span>
                        @endif
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

                    <x-nav-link
                        :href="route('notifications.index')"
                        :active="request()->routeIs('notifications.index')">
                        Notifications
                        @if (($unreadCount = auth()->user()->unreadNotifications()->count()) > 0)
                            <span class="ms-1 inline-flex min-w-5 items-center justify-center rounded-full bg-red-600 px-1.5 text-xs font-bold text-white">{{ $unreadCount }}</span>
                        @endif
                    </x-nav-link>
                @endif
            </div>

            {{-- Account dropdown --}}
            <div class="hidden items-center sm:flex">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center gap-2 rounded-md border border-[#F1C9CC] bg-white px-3 py-2 text-sm font-medium text-[#1A202C] transition-colors hover:border-[#B21F24] hover:bg-[#FFF9F9] focus:outline-none focus:ring-2 focus:ring-[#B21F24] focus:ring-offset-2">

                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-[#FFF1F2] text-[#B21F24]" aria-hidden="true">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 21a8 8 0 0 0-16 0" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg>
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
                            <p class="mt-1 text-xs font-semibold text-[#B21F24]">
                                {{ Auth::user()->role === 'cpdo_admin' ? 'CPDO staff' : 'Student' }}
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
                    class="inline-flex items-center justify-center rounded-md border border-[#F1C9CC] p-2 text-[#B21F24] transition hover:bg-[#FFF1F2] focus:outline-none focus:ring-2 focus:ring-[#B21F24] focus:ring-offset-2"
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
    <div x-show="open" x-cloak class="border-t border-[#F1C9CC] bg-white sm:hidden">

        <div class="space-y-1 px-4 py-3">
            @if (auth()->user()->role === 'cpdo_admin')
                <x-responsive-nav-link
                    :href="route('admin.reservations.index')"
                    :active="request()->routeIs('admin.reservations.*')">
                    Admin Dashboard
                </x-responsive-nav-link>

                <x-responsive-nav-link
                    :href="route('admin.calendar')"
                    :active="request()->routeIs('admin.calendar*')">
                    Calendar of Activities
                </x-responsive-nav-link>

                <x-responsive-nav-link
                    :href="route('notifications.index')"
                    :active="request()->routeIs('notifications.index')">
                    Notifications
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

                <x-responsive-nav-link
                    :href="route('notifications.index')"
                    :active="request()->routeIs('notifications.index')">
                    Notifications
                </x-responsive-nav-link>
            @endif
        </div>

        {{-- Mobile account information --}}
        <div class="border-t border-[#F1C9CC] px-4 py-4">
            <p class="text-sm font-semibold text-[#1A202C]">
                {{ Auth::user()->name }}
            </p>
            <p class="break-all text-xs text-[#4A5568]">
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
