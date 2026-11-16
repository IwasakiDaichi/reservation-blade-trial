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

            <a href="{{ route('admin.calendar') }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z" />
                </svg>
                Calendar of Activities
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Summary Cards --}}
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

            {{-- Recent Reservations --}}
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

                    <div class="flex items-center gap-5">
                        <a href="{{ route('admin.calendar') }}"
                            class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                            View Calendar →
                        </a>

                        <a href="{{ route('admin.reservations.index') }}"
                            class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                            Manage Reservations →
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 text-sm text-gray-500">
                            <tr>
                                <th class="px-6 py-3 font-medium">Applicant</th>
                                <th class="px-6 py-3 font-medium">Venue</th>
                                <th class="px-6 py-3 font-medium">Date</th>
                                <th class="px-6 py-3 font-medium">Status</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse ($recentReservations as $reservation)
                                <tr>
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-gray-900">
                                            {{ $reservation->user->name ?? 'Deleted user' }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            {{ $reservation->department }}
                                        </p>
                                    </td>

                                    <td class="px-6 py-4 text-gray-700">
                                        {{ $reservation->venue }}
                                        @if ($reservation->other_venue)
                                            <p class="text-sm text-gray-500">
                                                {{ $reservation->other_venue }}
                                            </p>
                                        @endif
                                    </td>

                                    <td class="p-3">
                                        @if ($reservation->start_date && $reservation->end_date)
                                            {{ $reservation->start_date->format('M d, Y') }}

                                            @if (!$reservation->start_date->isSameDay($reservation->end_date))
                                                - {{ $reservation->end_date->format('M d, Y') }}
                                            @endif
                                        @else
                                            {{ $reservation->reserved_date->format('M d, Y') }}
                                        @endif

                                        <div class="text-sm text-gray-500">
                                            {{ $reservation->time }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        @if ($reservation->status === 'pending')
                                            <span class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-800">
                                                Pending
                                            </span>
                                        @elseif ($reservation->status === 'approved')
                                            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800">
                                                Approved
                                            </span>
                                        @elseif ($reservation->status === 'rejected')
                                            <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-800">
                                                Rejected
                                            </span>
                                        @else
                                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                                                {{ ucfirst($reservation->status) }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                        No reservations have been submitted yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Upcoming Reservations --}}
            <div class="mt-8 rounded-xl bg-white shadow-sm">

                <div class="border-b border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Upcoming Approved Reservations
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Approved reservations scheduled for today or a future date.
                    </p>
                </div>

                <div class="divide-y divide-gray-100">
                    @forelse ($upcomingReservations as $reservation)
                        <div class="flex flex-col gap-3 p-6 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-medium text-gray-900">
                                    {{ $reservation->venue }}
                                    @if ($reservation->other_venue)
                                        — {{ $reservation->other_venue }}
                                    @endif
                                </p>

                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $reservation->user->name ?? 'Deleted user' }}
                                    · {{ $reservation->department }}
                                </p>
                            </div>

                            <div class="sm:text-right">
                                <p class="font-medium text-gray-900">
                                    @if ($reservation->start_date && $reservation->end_date)
                                        {{ $reservation->start_date->format('M d, Y') }}

                                        @if (!$reservation->start_date->isSameDay($reservation->end_date))
                                            - {{ $reservation->end_date->format('M d, Y') }}
                                        @endif
                                    @else
                                        {{ $reservation->reserved_date->format('M d, Y') }}
                                    @endif
                                </p>
                                <p class="text-sm text-gray-500">
                                    {{ $reservation->time }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="p-6 text-center text-gray-500">
                            There are no upcoming approved reservations.
                        </p>
                    @endforelse
                </div>

            </div>

        </div>
    </div>
</x-app-layout>