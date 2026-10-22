<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">
            My Reservations
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4">

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-5">
                <a href="{{ route('reservations.create') }}"
                    class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    + New Reservation
                </a>
            </div>

            <div class="bg-white shadow-sm rounded-lg overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-3">Venue</th>
                            <th class="p-3">Date</th>
                            <th class="p-3">Time</th>
                            <th class="p-3">Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($reservations as $reservation)
                            <tr class="border-t">
                                <td class="p-3">{{ $reservation->venue }}</td>
                                <td class="p-3">
                                    {{ $reservation->reserved_date->format('M d, Y') }}
                                </td>
                                <td class="p-3">{{ $reservation->time }}</td>
                                <td class="p-3">
                                    <span class="px-2 py-1 rounded text-sm
                                        {{ $reservation->status === 'approved'
                                            ? 'bg-green-100 text-green-800'
                                            : ($reservation->status === 'rejected'
                                                ? 'bg-red-100 text-red-800'
                                                : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ ucfirst($reservation->status) }}
                                    </span>
                                    
                                    @if ($reservation->status === 'rejected' && $reservation->rejection_reason)
                                        <div class="mt-3 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                                            <p class="font-semibold">Reason for rejection</p>
                                            <p class="mt-1">{{ $reservation->rejection_reason }}</p>
                                        </div>
                                    @endif

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-6 text-center text-gray-500">
                                    You have not submitted any reservations yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>