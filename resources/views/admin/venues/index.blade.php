<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Manage Venues</h2>
                <p class="mt-1 text-sm font-normal text-gray-500">
                    Set venue details and whether each venue is active, under maintenance or deactivated.
                </p>
            </div>

            <a href="{{ route('admin.venues.create') }}"
                class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                + Add venue
            </a>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="mb-4 rounded bg-green-100 p-4 text-green-800" role="status">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded bg-red-100 p-4 text-red-800" role="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- Status summary --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        @foreach (\App\Enums\VenueStatus::cases() as $case)
            <div class="rounded-xl bg-white p-5 shadow-sm">
                <x-venue-status-badge :status="$case" />
                <p class="mt-3 text-3xl font-bold text-gray-900">{{ $counts[$case->value] ?? 0 }}</p>
            </div>
        @endforeach
    </div>

    <form method="GET" action="{{ route('admin.venues.index') }}" class="mt-6 flex gap-2">
        <input type="search" name="q" value="{{ $search }}" placeholder="Search by name or location..."
            class="w-full rounded-md border-gray-300 text-sm shadow-sm sm:w-80" aria-label="Search venues">
        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Search</button>
        @if ($search !== '')
            <a href="{{ route('admin.venues.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Clear</a>
        @endif
    </form>

    <div class="mt-4 overflow-x-auto rounded-lg bg-white shadow-sm">
        <table class="w-full text-left">
            <thead class="bg-gray-100 text-sm">
                <tr>
                    <th class="p-3">Venue</th>
                    <th class="p-3">Capacity</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Change status</th>
                    <th class="p-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($venues as $venue)
                    <tr class="border-t align-top">
                        <td class="p-3">
                            <p class="font-medium text-gray-900">{{ $venue->name }}</p>
                            @if ($venue->location)
                                <p class="text-sm text-gray-500">{{ $venue->location }}</p>
                            @endif
                            @if ($venue->status_note)
                                <p class="mt-1 text-sm text-amber-700">{{ $venue->status_note }}
                                    @if ($venue->available_from)
                                        (back {{ $venue->available_from->format('M d, Y') }})
                                    @endif
                                </p>
                            @endif
                        </td>

                        <td class="p-3 text-gray-700">{{ $venue->capacityLabel() }}</td>

                        <td class="p-3"><x-venue-status-badge :status="$venue->status" /></td>

                        <td class="p-3">
                            <form method="POST" action="{{ route('admin.venues.status', $venue) }}" class="flex gap-2">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="rounded-md border-gray-300 text-sm shadow-sm" aria-label="Status for {{ $venue->name }}">
                                    @foreach (\App\Enums\VenueStatus::cases() as $case)
                                        <option value="{{ $case->value }}" @selected($venue->status === $case)>{{ $case->label() }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="rounded-md bg-gray-800 px-3 py-1 text-sm font-semibold text-white hover:bg-gray-700">Save</button>
                            </form>
                        </td>

                        <td class="p-3">
                            <a href="{{ route('admin.venues.edit', $venue) }}" class="text-sm font-semibold text-blue-800 underline">
                                Edit details
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-6 text-center text-gray-500">
                            @if ($search !== '')
                                No venues match your search.
                            @else
                                No venues yet. Add one, or run the venue seeder.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>
