<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">
            CPDO Reservation Management
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4">

            @if (session('success'))
                <div class="mb-4 rounded bg-green-100 p-4 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="overflow-x-auto rounded-lg bg-white shadow-sm">
                <table class="w-full text-left">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-3">Applicant</th>
                            <th class="p-3">Department</th>
                            <th class="p-3">Venue</th>
                            <th class="p-3">Date</th>
                            <th class="p-3">Purpose</th>
                            <th class="p-3">Status</th>
                            <th class="p-3">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($reservations as $reservation)
                            <tr class="border-t align-top">
                                <td class="p-3">
                                    {{ $reservation->user->name ?? 'Deleted user' }}
                                    <div class="text-sm text-gray-500">
                                        {{ $reservation->user->email ?? '' }}
                                    </div>
                                </td>

                                <td class="p-3">{{ $reservation->department }}</td>

                                <td class="p-3">
                                    {{ $reservation->venue }}
                                    @if ($reservation->other_venue)
                                        <div class="text-sm text-gray-500">
                                            {{ $reservation->other_venue }}
                                        </div>
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

                                <td class="p-3">{{ $reservation->purpose }}</td>

                                <td class="p-3">{{ ucfirst($reservation->status) }}</td>

                                <td class="p-3">
                                    @if ($reservation->status === 'pending')
                                        <div class="flex flex-col gap-2">
                                            <form method="POST"
                                                action="{{ route('admin.reservations.updateStatus', [$reservation, 'approved']) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="rounded bg-green-600 px-3 py-1 text-white">
                                                    Approve
                                                </button>
                                            </form>

                                            
                                            <button
                                                type="button"
                                                onclick="document.getElementById('reject-modal-{{ $reservation->id }}').classList.remove('hidden')"
                                                class="rounded bg-red-600 px-3 py-1 text-white hover:bg-red-700"
                                            >
                                                Reject
                                            </button>
                                            
                                            <!-- Rejection reason pop-up -->
                                            <div
                                                id="reject-modal-{{ $reservation->id }}"
                                                class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                                            >
                                                <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                                                    <h2 class="text-lg font-semibold text-gray-900">
                                                        Reject Reservation
                                                    </h2>
                                                
                                                    <p class="mt-2 text-sm text-gray-600">
                                                        Please explain why this reservation is being rejected.
                                                    </p>
                                                
                                                    <form
                                                        method="POST"
                                                        action="{{ route('admin.reservations.updateStatus', [
                                                            'reservation' => $reservation->id,
                                                            'status' => 'rejected',
                                                        ]) }}"
                                                        class="mt-4"
                                                    >
                                                        @csrf
                                                        @method('PATCH')
                                                
                                                        <label
                                                            for="rejection_reason_{{ $reservation->id }}"
                                                            class="block text-sm font-medium text-gray-700"
                                                        >
                                                            Reason for rejection
                                                        </label>
                                                    
                                                        <textarea
                                                            id="rejection_reason_{{ $reservation->id }}"
                                                            name="rejection_reason"
                                                            required
                                                            maxlength="2000"
                                                            rows="4"
                                                            placeholder="Enter the reason..."
                                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                                        ></textarea>
                                                    
                                                        @error('rejection_reason')
                                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                        @enderror
                                                    
                                                        <div class="mt-4 flex justify-end gap-2">
                                                            <button
                                                                type="button"
                                                                onclick="document.getElementById('reject-modal-{{ $reservation->id }}').classList.add('hidden')"
                                                                class="rounded border border-gray-300 px-3 py-2 text-gray-700"
                                                            >
                                                                Cancel
                                                            </button>
                                                        
                                                            <button
                                                                type="submit"
                                                                class="rounded bg-red-600 px-3 py-2 text-white hover:bg-red-700"
                                                            >
                                                                Confirm Rejection
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    @else
                                        <span class="text-gray-500">Reviewed</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-6 text-center text-gray-500">
                                    No reservations have been submitted yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>