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
               class="text-sm font-medium text-[#B21F24] hover:text-[#8A151B]">
                My Reservations
            </a>

            <a href="{{ route('reservations.create') }}"
               class="text-sm font-medium text-[#B21F24] hover:text-[#8A151B]">
                Make a Reservation
            </a>
        </div>
    </div>

   {{-- Student Profile Completion Popup --}}
@if (
    auth()->user()->role === 'student' &&
    (blank(auth()->user()->department_id) || blank(auth()->user()->mobile_number))
)
    @php
        $departments = \App\Models\Department::listedNames();

        $currentDepartment = old(
            'department',
            auth()->user()->department?->name
        );

        $isOther = old('department') === 'Others'
            || (
                filled($currentDepartment)
                && !in_array($currentDepartment, $departments, true)
            );

        $otherValue = old(
            'department_other',
            $isOther && $currentDepartment !== 'Others'
                ? $currentDepartment
                : ''
        );
    @endphp

    <div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 px-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">

            <h2 class="text-xl font-bold text-slate-800">
                Complete Your Student Profile
            </h2>

            <p class="mt-2 text-sm leading-6 text-slate-600">
                Please provide your department and mobile number before making a reservation.
            </p>

            <form
                action="{{ route('profile.complete') }}"
                method="POST"
                class="mt-6 space-y-6"
            >
                @csrf
                @method('PATCH')

                {{-- Department --}}
                <div x-data="{ dept: @js($isOther ? 'Others' : ($currentDepartment ?? '')) }">

                    <label
                        for="department"
                        class="block text-sm font-medium text-slate-700"
                    >
                        Department / Local No.
                    </label>

                    <select
                        id="department"
                        name="department"
                        x-model="dept"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 bg-white shadow-sm focus:border-[#B21F24] focus:ring-[#B21F24]"
                    >
                        <option value="" disabled>Select your department</option>

                        @foreach ($departments as $dept)
                            <option value="{{ $dept }}">
                                {{ $dept }}
                            </option>
                        @endforeach

                        <option value="Others">Others (please specify)</option>
                    </select>

                    @error('department')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                    {{-- Other Department --}}
                    <div
                        x-show="dept === 'Others'"
                        x-cloak
                        class="mt-3"
                    >
                        <label
                            for="department_other"
                            class="block text-sm font-medium text-slate-700"
                        >
                            Specify your department / local no.
                        </label>

                        <input
                            id="department_other"
                            name="department_other"
                            type="text"
                            value="{{ $otherValue }}"
                            maxlength="255"
                            placeholder="Type your department"
                            x-bind:required="dept === 'Others'"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#B21F24] focus:ring-[#B21F24]"
                        >

                        @error('department_other')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                {{-- Mobile Number --}}
                <div>
                    <label
                        for="mobile_number"
                        class="block text-sm font-medium text-slate-700"
                    >
                        Mobile Number
                    </label>

                    <input
                        id="mobile_number"
                        name="mobile_number"
                        type="tel"
                        value="{{ old('mobile_number', auth()->user()->mobile_number) }}"
                        required
                        maxlength="30"
                        placeholder="09XXXXXXXXX"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#B21F24] focus:ring-[#B21F24]"
                    >

                    @error('mobile_number')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Submit --}}
                <button
                    type="submit"
                    class="w-full rounded-lg bg-[#B21F24] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#8A151B]"
                >
                    Save Profile
                </button>
            </form>
        </div>
    </div>
@endif
</x-app-layout>
