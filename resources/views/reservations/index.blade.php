<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-900">
            My Reservations
        </h2>
    </x-slot>

    <div class="py-2 sm:py-4">
        <div class="space-y-6">

            @if (session('success'))
                <div class="flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-700" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.78-9.78a.75.75 0 0 0-1.06-1.06L9 10.94 7.28 9.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.06 0l4.25-4.31Z" clip-rule="evenodd" />
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            @if ($reservations->contains(fn ($r) => $r->affectedByMaintenance()))
                <div class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900" role="alert">
                    <p class="font-semibold">One or more of your venues is under maintenance.</p>
                    <p class="mt-1">CPDO will get in touch about rescheduling. You can also cancel the affected reservation below at any time.</p>
                </div>
            @endif

            <div class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm text-slate-600">
                        Review the status of your venue requests and print submitted forms.
                    </p>
                </div>

                <a href="{{ route('reservations.create') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-md bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-700 focus:ring-offset-2">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M10 4.75a.75.75 0 0 1 .75.75v3.75h3.75a.75.75 0 0 1 0 1.5h-3.75v3.75a.75.75 0 0 1-1.5 0V10.75H5.5a.75.75 0 0 1 0-1.5h3.75V5.5A.75.75 0 0 1 10 4.75Z" />
                    </svg>
                    New reservation
                </a>
            </div>

            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                    <h3 class="text-base font-semibold text-slate-900">Submitted requests</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-600">
                        <tr>
                            <th scope="col" class="px-5 py-3 sm:px-6">Venue</th>
                            <th scope="col" class="px-5 py-3 sm:px-6">Date</th>
                            <th scope="col" class="px-5 py-3 sm:px-6">Time</th>
                            <th scope="col" class="px-5 py-3 sm:px-6">Status</th>
                            <th scope="col" class="px-5 py-3 text-right sm:px-6">Form</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200 text-slate-700">
                        @forelse ($reservations as $reservation)
                            <tr class="hover:bg-slate-50">
                                <td class="whitespace-nowrap px-5 py-4 font-medium text-slate-900 sm:px-6">{{ $reservation->venueName() }}</td>
                                <td class="whitespace-nowrap px-5 py-4 sm:px-6">
                                    {{ $reservation->reserved_date->format('M d, Y') }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 sm:px-6">{{ $reservation->time }}</td>
                                <td class="px-5 py-4 sm:px-6">
                                    <span class="inline-flex rounded-md px-2.5 py-1 text-xs font-semibold
                                        {{ $reservation->status === 'approved'
                                            ? 'bg-emerald-100 text-emerald-800'
                                            : ($reservation->status === 'rejected'
                                                ? 'bg-red-100 text-red-800'
                                                : ($reservation->status === 'cancelled'
                                                    ? 'bg-slate-200 text-slate-700'
                                                    : 'bg-amber-100 text-amber-800')) }}">
                                        {{ ucfirst($reservation->status) }}
                                    </span>
                                    
                                    @if ($reservation->affectedByMaintenance())
                                        <p class="mt-2 text-xs font-semibold text-amber-700">Venue under maintenance</p>
                                    @endif

                                    @if ($reservation->status === 'cancelled' && $reservation->cancellation_reason)
                                        <div class="mt-3 max-w-sm rounded-md border border-slate-200 bg-slate-50 p-3 text-sm text-slate-700">
                                            <p class="font-semibold">Cancelled by {{ $reservation->cancelled_by === 'cpdo' ? 'CPDO' : 'you' }}</p>
                                            <p class="mt-1">{{ $reservation->cancellation_reason }}</p>
                                        </div>
                                    @endif

                                    @if ($reservation->status === 'rejected' && $reservation->rejection_reason)
                                        <div class="mt-3 max-w-sm rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                                            <p class="font-semibold">Reason for rejection</p>
                                            <p class="mt-1">{{ $reservation->rejection_reason }}</p>
                                        </div>
                                    @endif

                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-right sm:px-6">
                                    @if ($reservation->canBeCancelledByOwner())
                                        <form method="POST" action="{{ route('reservations.cancel', $reservation) }}" class="mb-2 inline"
                                            onsubmit="return confirm('Cancel this reservation?');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="inline-flex items-center rounded-md border border-red-600 px-3 py-2 text-sm font-semibold text-red-700 transition-colors hover:bg-red-50">
                                                Cancel
                                            </button>
                                        </form>
                                    @elseif (in_array($reservation->status, ['pending', 'approved'], true))
                                        <span class="mr-2 text-xs text-slate-500">Cancellation closed {{ $reservation->cancellationDeadline()->format('M d') }}</span>
                                    @endif
                                    <a href="{{ route('reservations.print', $reservation) }}"
                                        class="inline-flex items-center rounded-md border border-blue-700 px-3 py-2 text-sm font-semibold text-blue-800 transition-colors hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-700 focus:ring-offset-2"
                                        target="_blank" rel="noopener">
                                        Print form
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center text-slate-600 sm:px-6">
                                    <p class="font-medium text-slate-800">No reservations submitted</p>
                                    <p class="mt-1 text-sm">Select “New reservation” to submit your first venue request.</p>
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
