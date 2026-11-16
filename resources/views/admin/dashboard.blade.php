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