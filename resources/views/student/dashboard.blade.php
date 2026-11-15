<x-app-layout>
    <x-slot name="header">
        Student Dashboard
    </x-slot>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-800">
            Welcome, {{ auth()->user()->name }}!
        </h2>

        <p class="mt-2 text-sm text-slate-600">
            Welcome to the CPDO Venue Reservation System.
        </p>

        <div class="mt-4 flex flex-wrap gap-4">
            <a href="{{ route('reservations.index') }}"
               class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                My Reservations
            </a>

            <a href="{{ route('reservations.create') }}"
               class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                Make a Reservation
            </a>
        </div>
    </div>

    {{-- Student Profile Completion Popup --}}
    @if (
        auth()->user()->role === 'student' &&
        (blank(auth()->user()->department) || blank(auth()->user()->mobile_number))
    )
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 px-4">

            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">

                <h2 class="text-xl font-bold text-slate-800">
                    Complete Your Student Profile
                </h2>

                <p class="mt-2 text-sm text-slate-600">
                    Please provide your department and mobile number before making a reservation.
                </p>

                <form action="{{ route('profile.complete') }}" method="POST" class="mt-6">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="department" class="block text-sm font-medium text-slate-700">
                            Department / Local No.
                        </label>

                        <input
                            type="text"
                            id="department"
                            name="department"
                            value="{{ old('department', auth()->user()->department) }}"
                            required
                            maxlength="255"
                            class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >

                        @error('department')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-4">
                        <label for="mobile_number" class="block text-sm font-medium text-slate-700">
                            Mobile Number
                        </label>

                        <input
                            type="tel"
                            id="mobile_number"
                            name="mobile_number"
                            value="{{ old('mobile_number', auth()->user()->mobile_number) }}"
                            required
                            maxlength="30"
                            placeholder="09XXXXXXXXX"
                            class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >

                        @error('mobile_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="mt-6 w-full rounded-lg bg-indigo-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700"
                    >
                        Save Profile
                    </button>
                </form>

            </div>
        </div>
    @endif
</x-app-layout>