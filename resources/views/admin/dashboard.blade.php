
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">
                    CPDO Dashboard
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Overview of venue reservations and activities.
                </p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row">
                <a href="{{ route('admin.venues.index') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-indigo-200 bg-white px-4 py-2 text-sm font-semibold text-indigo-700 shadow-sm hover:bg-indigo-50">
                    Manage Venues
                </a>

                <a href="{{ route('admin.calendar') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                    Calendar of Activities
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">

                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">
                        Total Reservations
                    </p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">
                        {{ $totalReservations }}
                    </p>
                    <p class="mt-1 text-sm text-gray-500">
                        All submitted requests
                    </p>
                </div>

                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">
                        Pending
                    </p>
                    <p class="mt-2 text-3xl font-bold text-yellow-600">
                        {{ $pendingReservations }}
                    </p>
                    <p class="mt-1 text-sm text-gray-500">
                        Awaiting review
                    </p>
                </div>

                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">
                        Approved
                    </p>
                    <p class="mt-2 text-3xl font-bold text-green-600">
                        {{ $approvedReservations }}
                    </p>
                    <p class="mt-1 text-sm text-gray-500">
                        Accepted requests
                    </p>
                </div>

                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">
                        Rejected
                    </p>
                    <p class="mt-2 text-3xl font-bold text-red-600">
                        {{ $rejectedReservations }}
                    </p>
                    <p class="mt-1 text-sm text-gray-500">
                        Declined requests
                    </p>
                </div>

            </div>

            <div class="mt-8 rounded-xl bg-white shadow-sm">

                <div class="flex flex-col gap-3 border-b border-gray-100 p-6 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            Recent Reservations
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            The latest reservation requests submitted.
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-5">
                        {{-- Search: results open on the Manage Reservations page --}}
                        <form method="GET" action="{{ route('admin.reservations.index') }}" class="flex gap-2">
                            <input type="search" name="q"
                                placeholder="Search ID, name, department, venue..."
                                class="w-full rounded-md border-gray-300 text-sm shadow-sm sm:w-72"
                                aria-label="Search reservations">
                            <button type="submit"
                                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                                Search
                            </button>
                        </form>

                        <a href="{{ route('admin.reservations.index') }}"
                            class="whitespace-nowrap text-sm font-medium text-indigo-600 hover:text-indigo-800">
                            Manage Reservations →
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">

                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Reservation ID
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Venue
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Reserved Date
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Submitted
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 bg-white">

                            @forelse ($recentReservations as $reservation)
                                <tr class="hover:bg-gray-50">

                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                        #{{ $reservation->id }}
                                    </td>

                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                        {{ $reservation->venueName() }}
                                    </td>

                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                        {{ $reservation->reserved_date
                                            ? \Carbon\Carbon::parse($reservation->reserved_date)->format('M d, Y')
                                            : 'N/A' }}
                                    </td>

                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        @if ($reservation->status === 'approved')
                                            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                                Approved
                                            </span>
                                        @elseif ($reservation->status === 'pending')
                                            <span class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-700">
                                                Pending
                                            </span>
                                        @elseif ($reservation->status === 'rejected')
                                            <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                                Rejected
                                            </span>
                                        @elseif ($reservation->status === 'cancelled')
                                            <span class="rounded-full bg-gray-200 px-3 py-1 text-xs font-semibold text-gray-700">
                                                Cancelled
                                            </span>
                                        @else
                                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                                                {{ ucfirst($reservation->status ?? 'Unknown') }}
                                            </span>
                                        @endif
                                    </td>

                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                        {{ $reservation->created_at
                                            ? $reservation->created_at->format('M d, Y')
                                            : 'N/A' }}
                                    </td>

                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        @if ($reservation->status === 'pending')
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('reservations.print', $reservation) }}"
                                                    target="_blank" rel="noopener"
                                                    class="rounded-md border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">
                                                    View form
                                                </a>
                                                <a href="{{ route('admin.reservations.index', ['q' => '#'.$reservation->id]) }}"
                                                    class="rounded-md bg-indigo-600 px-3 py-1 text-xs font-semibold text-white hover:bg-indigo-700">
                                                    Review →
                                                </a>
                                            </div>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">
                                        No reservations have been submitted yet.
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
