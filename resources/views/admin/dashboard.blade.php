
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
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-[#F1C9CC] bg-white px-4 py-2 text-sm font-semibold text-[#B21F24] shadow-sm hover:bg-[#FFF1F2]">
                    Manage Venues
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            <section class="border border-[#F1C9CC] bg-white">
                <div class="border-b border-[#F1C9CC] bg-[#FFF8F8] px-5 py-3">
                    <h3 class="text-sm font-semibold text-[#1A202C]">Reservation overview</h3>
                </div>
                <div class="grid divide-y divide-[#F1C9CC] sm:grid-cols-2 sm:divide-x sm:divide-y-0 lg:grid-cols-4">
                <div class="px-5 py-4">
                    <p class="text-sm font-medium text-[#4A5568]">
                        Total Reservations
                    </p>
                    <p class="mt-2 text-3xl font-bold text-[#1A202C]">
                        {{ $totalReservations }}
                    </p>
                    <p class="mt-1 text-sm text-[#718096]">
                        All submitted requests
                    </p>
                </div>

                <div class="px-5 py-4">
                    <p class="text-sm font-medium text-[#4A5568]">
                        Pending
                    </p>
                    <p class="mt-2 text-3xl font-bold text-yellow-600">
                        {{ $pendingReservations }}
                    </p>
                    <p class="mt-1 text-sm text-[#718096]">
                        Awaiting review
                    </p>
                </div>

                <div class="px-5 py-4">
                    <p class="text-sm font-medium text-[#4A5568]">
                        Approved
                    </p>
                    <p class="mt-2 text-3xl font-bold text-green-600">
                        {{ $approvedReservations }}
                    </p>
                    <p class="mt-1 text-sm text-[#718096]">
                        Accepted requests
                    </p>
                </div>

                <div class="px-5 py-4">
                    <p class="text-sm font-medium text-[#4A5568]">
                        Rejected
                    </p>
                    <p class="mt-2 text-3xl font-bold text-red-600">
                        {{ $rejectedReservations }}
                    </p>
                    <p class="mt-1 text-sm text-[#718096]">
                        Declined requests
                    </p>
                </div>

                </div>
            </section>

            <section class="border border-[#F1C9CC] bg-white">

                <div class="flex flex-col gap-3 border-b border-[#F1C9CC] p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-[#1A202C]">
                            Recent Reservations
                        </h3>
                        <p class="mt-1 text-sm text-[#718096]">
                            The latest reservation requests submitted.
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-5">
                        {{-- Search: results open on the Manage Reservations page --}}
                        <form method="GET" action="{{ route('admin.reservations.index') }}" class="flex gap-2">
                            <input type="search" name="q"
                                placeholder="Search ID, name, department, venue..."
                                class="w-full rounded-md border-[#D9A6AA] text-sm shadow-sm focus:border-[#B21F24] focus:ring-[#B21F24] sm:w-72"
                                aria-label="Search reservations">
                            <button type="submit"
                                class="rounded-md bg-[#B21F24] px-4 py-2 text-sm font-semibold text-white hover:bg-[#8A151B]">
                                Search
                            </button>
                        </form>

                        <a href="{{ route('admin.reservations.index') }}"
                            class="whitespace-nowrap text-sm font-medium text-[#B21F24] hover:text-[#8A151B]">
                            Manage Reservations →
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#E2E8F0]">

                        <thead class="bg-[#F8F9FA]">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#4A5568]">
                                    Reservation ID
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#4A5568]">
                                    Venue
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#4A5568]">
                                    Reserved Date
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#4A5568]">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#4A5568]">
                                    Submitted
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#4A5568]">
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-[#E2E8F0] bg-white">

                            @forelse ($recentReservations as $reservation)
                                <tr class="hover:bg-[#FFF8F8]">

                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-[#1A202C]">
                                        #{{ $reservation->id }}
                                    </td>

                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-[#4A5568]">
                                        {{ $reservation->venueName() }}
                                    </td>

                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-[#4A5568]">
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

                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-[#718096]">
                                        {{ $reservation->created_at
                                            ? $reservation->created_at->format('M d, Y')
                                            : 'N/A' }}
                                    </td>

                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        @if ($reservation->status === 'pending')
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('reservations.print', $reservation) }}"
                                                    target="_blank" rel="noopener"
                                                    class="rounded-md border border-[#F1C9CC] bg-[#FFF1F2] px-3 py-1 text-xs font-semibold text-[#B21F24] hover:bg-[#FCEDEE]">
                                                    View form
                                                </a>
                                                <a href="{{ route('admin.reservations.index', ['q' => '#'.$reservation->id]) }}"
                                                    class="rounded-md bg-[#B21F24] px-3 py-1 text-xs font-semibold text-white hover:bg-[#8A151B]">
                                                    Review →
                                                </a>
                                            </div>
                                        @else
                                            <span class="text-[#A0AEC0]">—</span>
                                        @endif
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-sm text-[#718096]">
                                        No reservations have been submitted yet.
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>
                    </table>
                </div>

            </section>

        </div>
    </div>
</x-app-layout>
