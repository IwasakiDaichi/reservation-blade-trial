<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">
            CPDO Reservation Management
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4">

            @if (session('error'))
                <div class="mb-4 rounded bg-red-100 p-4 text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('success'))
                <div class="mb-4 rounded bg-green-100 p-4 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Search and filter --}}
            <form method="GET" action="{{ route('admin.reservations.index') }}"
                class="mb-4 flex flex-col gap-3 rounded-lg bg-white p-4 shadow-sm sm:flex-row sm:items-end">
                <div class="flex-1">
                    <label for="q" class="block text-sm font-medium text-gray-700">Search</label>
                    <input id="q" type="search" name="q" value="{{ $search }}"
                        placeholder="Reservation ID, applicant name or email, department, venue, purpose..."
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">All</option>
                        <option value="pending" @selected($status === 'pending')>Pending</option>
                        <option value="approved" @selected($status === 'approved')>Approved</option>
                        <option value="rejected" @selected($status === 'rejected')>Rejected</option>
                        <option value="cancelled" @selected($status === 'cancelled')>Cancelled</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="rounded-md bg-[#B21F24] px-4 py-2 text-sm font-semibold text-white hover:bg-[#8A151B]">
                        Search
                    </button>
                    @if ($search !== '' || $status !== '')
                        <a href="{{ route('admin.reservations.index') }}"
                            class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            Clear
                        </a>
                    @endif
                </div>
            </form>

            @if ($search !== '' || $status !== '')
                <p class="mb-3 text-sm text-gray-600">
                    {{ $reservations->count() }} {{ \Illuminate\Support\Str::plural('result', $reservations->count()) }}
                    @if ($search !== '') for "<strong>{{ $search }}</strong>" @endif
                    @if ($status !== '') · status: {{ ucfirst($status) }} @endif
                </p>
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
                            @php
                                $overlaps = $reservation->overlappingOthers();
                                $blockedByApproved = $overlaps->contains('status', 'approved');
                            @endphp
                            <tr class="border-t align-top">
                                <td class="p-3">
                                    {{ $reservation->applicantName() }}
                                    <div class="text-sm text-gray-500">
                                        {{ $reservation->user?->email }}
                                    </div>
                                    <div class="text-xs text-gray-400">Reservation #{{ $reservation->id }}</div>
                                </td>

                                <td class="p-3">{{ $reservation->department?->name }}</td>

                                <td class="p-3">
                                    {{ $reservation->venue?->name ?? 'Other venue' }}
                                    @if (! $reservation->venue && $reservation->other_venue)
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

                                <td class="p-3">
                                    {{ ucfirst($reservation->status) }}
                                    @if ($reservation->status === 'cancelled')
                                        <div class="text-xs text-gray-500">by {{ $reservation->cancelled_by === 'cpdo' ? 'CPDO' : 'the student' }}</div>
                                    @endif
                                    @if ($reservation->affectedByMaintenance())
                                        <div class="mt-1 text-xs font-semibold text-amber-700">Venue under maintenance</div>
                                    @endif
                                    @if ($overlaps->isNotEmpty())
                                        <div class="mt-1 max-w-[14rem] rounded border border-amber-300 bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-800">
                                            Overlaps with
                                            @foreach ($overlaps as $other)
                                                #{{ $other->id }} ({{ $other->status }}){{ $loop->last ? '' : ',' }}
                                            @endforeach
                                        </div>
                                    @endif
                                </td>

                                <td class="p-3">
                                    @if ($reservation->status === 'pending')
                                        <div class="flex flex-col gap-2">
                                            <form method="POST"
                                                action="{{ route('admin.reservations.updateStatus', [$reservation, 'approved']) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="rounded bg-green-600 px-3 py-1 text-white {{ $blockedByApproved ? 'cursor-not-allowed opacity-50' : '' }}"
                                                    @if ($blockedByApproved) disabled title="Overlaps an approved reservation" @endif>
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

                                    @if ($reservation->status === 'approved')
                                        <details class="mt-2">
                                            <summary class="cursor-pointer text-sm font-semibold text-red-700 underline">Cancel reservation</summary>
                                            <form method="POST" action="{{ route('admin.reservations.cancel', $reservation) }}" class="mt-2 space-y-2">
                                                @csrf
                                                @method('PATCH')
                                                <textarea name="cancellation_reason" required maxlength="2000" rows="3"
                                                    placeholder="Reason shown to the student..."
                                                    class="block w-full rounded-md border-gray-300 text-sm shadow-sm"></textarea>
                                                <button type="submit" class="rounded bg-red-600 px-3 py-1 text-sm text-white hover:bg-red-700">
                                                    Cancel and notify student
                                                </button>
                                            </form>
                                        </details>
                                    @endif
                                    <div class="mt-3 flex flex-col items-start gap-2">
                                        <a href="{{ route('reservations.print', $reservation) }}" target="_blank" rel="noopener"
                                            class="text-sm font-semibold text-[#B21F24] underline">
                                            Print form
                                        </a>
                                        <a href="{{ route('admin.reservations.signatures.edit', $reservation) }}"
                                            class="text-sm font-semibold text-[#B21F24] underline">
                                            Upload staff signatures
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-6 text-center text-gray-500">
                                    @if ($search !== '' || $status !== '')
                                        No reservations match your search.
                                    @else
                                        No reservations have been submitted yet.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>