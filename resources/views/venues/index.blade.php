<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Venues</h2>
                <p class="mt-1 text-sm font-normal text-gray-500">
                    Browse the venues you can reserve and see their details.
                </p>
            </div>

            <form method="GET" action="{{ route('venues.index') }}" class="flex gap-2">
                <input type="search" name="q" value="{{ $search }}"
                    placeholder="Search venues..."
                    class="w-full rounded-md border-gray-300 text-sm font-normal shadow-sm sm:w-64"
                    aria-label="Search venues">
                <button type="submit"
                    class="rounded-md bg-[#B21F24] px-4 py-2 text-sm font-semibold text-white hover:bg-[#8A151B]">
                    Search
                </button>
                @if ($search !== '')
                    <a href="{{ route('venues.index') }}"
                        class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Clear
                    </a>
                @endif
            </form>
        </div>
    </x-slot>

    @if ($venues->isEmpty())
        <div class="rounded-xl bg-white p-10 text-center text-sm text-gray-500 shadow-sm">
            @if ($search !== '')
                No venues match "{{ $search }}".
            @else
                There are no venues to show yet.
            @endif
        </div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($venues as $venue)
                <article class="flex flex-col overflow-hidden rounded-xl bg-white shadow-sm">
                    @if ($venue->imageUrl())
                        <img src="{{ $venue->imageUrl() }}" alt="{{ $venue->name }}" class="h-44 w-full object-cover">
                    @else
                        <div class="flex h-44 w-full items-center justify-center bg-slate-100 text-4xl font-black text-slate-300">
                            {{ strtoupper(mb_substr($venue->name, 0, 2)) }}
                        </div>
                    @endif

                    <div class="flex flex-1 flex-col p-5">
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $venue->name }}</h3>
                            <x-venue-status-badge :status="$venue->status" class="shrink-0" />
                        </div>

                        <dl class="mt-3 space-y-1 text-sm text-gray-600">
                            <div class="flex gap-2">
                                <dt class="font-medium text-gray-700">Capacity:</dt>
                                <dd>{{ $venue->capacityLabel() }}</dd>
                            </div>
                            @if ($venue->location)
                                <div class="flex gap-2">
                                    <dt class="font-medium text-gray-700">Location:</dt>
                                    <dd>{{ $venue->location }}</dd>
                                </div>
                            @endif
                        </dl>

                        @if ($venue->description)
                            <p class="mt-3 text-sm text-gray-500">{{ \Illuminate\Support\Str::limit($venue->description, 120) }}</p>
                        @endif

                        @unless ($venue->isReservable())
                            <p class="mt-3 rounded-lg bg-amber-50 p-3 text-sm text-amber-800">
                                {{ $venue->status_note ?: 'This venue is not available for reservations right now.' }}
                                @if ($venue->available_from)
                                    <span class="block font-medium">Expected back: {{ $venue->available_from->format('M d, Y') }}</span>
                                @endif
                            </p>
                        @endunless

                        <div class="mt-auto flex items-center gap-3 pt-5">
                            <a href="{{ route('venues.show', $venue) }}"
                                class="rounded-md border border-[#F1C9CC] bg-[#FFF1F2] px-4 py-2 text-sm font-semibold text-[#B21F24] hover:bg-[#FCEDEE]">
                                View details
                            </a>

                            @if ($venue->isReservable())
                                <a href="{{ route('reservations.create', ['venue' => $venue->id]) }}"
                                    class="rounded-md bg-[#B21F24] px-4 py-2 text-sm font-semibold text-white hover:bg-[#8A151B]">
                                    Reserve
                                </a>
                            @else
                                <span class="cursor-not-allowed rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-400">
                                    Unavailable
                                </span>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</x-app-layout>
