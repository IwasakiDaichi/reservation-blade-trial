<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <a href="{{ route('venues.index') }}" class="text-sm font-normal text-gray-500 hover:text-gray-800">&larr; All venues</a>
                <h2 class="mt-1 text-xl font-semibold text-gray-800">{{ $venue->name }}</h2>
            </div>
            <x-venue-status-badge :status="$venue->status" />
        </div>
    </x-slot>

    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
        @if ($venue->imageUrl())
            <img src="{{ $venue->imageUrl() }}" alt="{{ $venue->name }}" class="h-64 w-full object-cover sm:h-80">
        @endif

        <div class="grid gap-8 p-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                @unless ($venue->isReservable())
                    <div class="rounded-lg bg-amber-50 p-4 text-sm text-amber-800">
                        <p class="font-semibold">Not available for reservations right now</p>
                        @if ($venue->status_note)
                            <p class="mt-1">{{ $venue->status_note }}</p>
                        @endif
                        @if ($venue->available_from)
                            <p class="mt-1 font-medium">Expected back: {{ $venue->available_from->format('F d, Y') }}</p>
                        @endif
                    </div>
                @endunless

                <section>
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">About this venue</h3>
                    <p class="mt-2 whitespace-pre-line text-gray-700">{{ $venue->description ?: 'No description has been added yet.' }}</p>
                </section>

                @if (count($venue->amenityList()))
                    <section>
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Amenities</h3>
                        <ul class="mt-2 grid list-inside list-disc gap-1 text-gray-700 sm:grid-cols-2">
                            @foreach ($venue->amenityList() as $amenity)
                                <li>{{ $amenity }}</li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if ($venue->rules)
                    <section>
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Rules and notes</h3>
                        <p class="mt-2 whitespace-pre-line text-gray-700">{{ $venue->rules }}</p>
                    </section>
                @endif
            </div>

            <aside class="h-fit space-y-4 rounded-xl border border-gray-200 p-5">
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="font-medium text-gray-500">Capacity</dt>
                        <dd class="text-lg font-semibold text-gray-900">{{ $venue->capacityLabel() }}</dd>
                    </div>
                    @if ($venue->location)
                        <div>
                            <dt class="font-medium text-gray-500">Location</dt>
                            <dd class="text-gray-900">{{ $venue->location }}</dd>
                        </div>
                    @endif
                </dl>

                @if ($venue->isReservable())
                    <a href="{{ route('reservations.create') }}"
                        class="block rounded-md bg-indigo-600 px-4 py-3 text-center text-sm font-semibold text-white hover:bg-indigo-700">
                        Reserve this venue
                    </a>
                @else
                    <span class="block cursor-not-allowed rounded-md bg-gray-100 px-4 py-3 text-center text-sm font-semibold text-gray-400">
                        Unavailable
                    </span>
                @endif
            </aside>
        </div>
    </div>
</x-app-layout>
