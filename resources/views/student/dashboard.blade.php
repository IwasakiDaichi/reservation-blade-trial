<x-app-layout>
    <x-slot name="header">
        Student Dashboard
    </x-slot>

    <div class="border-t-4 border-[#B21F24] bg-[#FFF9F9]">
        <section class="border border-[#F1C9CC] bg-white">
            <div class="px-5 py-6 sm:px-6">
                <h2 class="text-2xl font-bold tracking-tight text-[#1A202C]">
                    {{ auth()->user()->name }}
                </h2>
                <p class="mt-3 max-w-3xl text-sm leading-6 text-[#4A5568]">
                    Use the navigation above to make a reservation, review your requests, or browse campus venues.
                </p>

                <div class="mt-7 grid gap-6 border-t border-[#F1C9CC] pt-6 lg:grid-cols-[11rem_1fr]">
                    <div>
                        <h3 class="text-base font-semibold text-[#B21F24]">Before you submit</h3>
                        <p class="mt-1 text-sm leading-6 text-[#4A5568]">Prepare the details CPDO needs to review your request.</p>
                    </div>

                    <ol class="grid gap-4 sm:grid-cols-3">
                        <li class="border-t-2 border-[#B21F24] pt-3">
                            <p class="text-xs font-bold uppercase tracking-[0.12em] text-[#B21F24]">01</p>
                            <p class="mt-2 text-sm font-semibold text-[#1A202C]">Select a venue</p>
                            <p class="mt-1 text-sm leading-5 text-[#4A5568]">Choose a space suited to your event.</p>
                        </li>
                        <li class="border-t-2 border-[#F1C9CC] pt-3">
                            <p class="text-xs font-bold uppercase tracking-[0.12em] text-[#B21F24]">02</p>
                            <p class="mt-2 text-sm font-semibold text-[#1A202C]">Add request details</p>
                            <p class="mt-1 text-sm leading-5 text-[#4A5568]">Include your schedule, purpose, and setup.</p>
                        </li>
                        <li class="border-t-2 border-[#F1C9CC] pt-3">
                            <p class="text-xs font-bold uppercase tracking-[0.12em] text-[#B21F24]">03</p>
                            <p class="mt-2 text-sm font-semibold text-[#1A202C]">Track CPDO review</p>
                            <p class="mt-1 text-sm leading-5 text-[#4A5568]">Check My Reservations for updates.</p>
                        </li>
                    </ol>
                </div>
            </div>
        </section>
    </div>

    {{-- Student Profile Completion Popup --}}
    @if (
        auth()->user()->role === 'student' &&
        (blank(auth()->user()->department_id) || blank(auth()->user()->mobile_number))
    )
        @php
            $departments = \App\Models\Department::listedNames();
            $currentDepartment = old('department', auth()->user()->department?->name);
            $isOther = old('department') === 'Others'
                || (filled($currentDepartment) && ! in_array($currentDepartment, $departments, true));
            $otherValue = old('department_other', $isOther && $currentDepartment !== 'Others' ? $currentDepartment : '');
        @endphp

        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 px-4">

            <div class="w-full max-w-md rounded-xl border border-[#E2E8F0] bg-white p-6 shadow-xl">

                <div class="border-b border-[#E2E8F0] pb-4">
                    <h2 class="text-xl font-semibold text-[#1A202C]">
                        Complete your student profile
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-[#718096]">
                        Please provide your department and mobile number before making a reservation.
                    </p>
                </div>

                <form action="{{ route('profile.complete') }}" method="POST" class="mt-6">
                    @csrf
                    @method('PATCH')

                    <div x-data="{ dept: @js($isOther ? 'Others' : ($currentDepartment ?? '')) }">
                        <label for="department" class="block text-sm font-semibold text-[#1A202C]">
                            Department / Local No.
                        </label>

                        <select
                            id="department"
                            name="department"
                            x-model="dept"
                            required
                            class="mt-1.5 block w-full rounded-md border-[#CBD5E1] text-[#1A202C] shadow-sm focus:border-[#B21F24] focus:ring-[#B21F24]"
                        >
                            <option value="" disabled>Select your department</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department }}">{{ $department }}</option>
                            @endforeach
                            <option value="Others">Others (please specify)</option>
                        </select>

                        @error('department')
                            <p class="mt-1 text-sm text-[#B91C1C]">{{ $message }}</p>
                        @enderror

                        <div x-show="dept === 'Others'" x-cloak class="mt-3">
                            <label for="department_other" class="block text-sm font-semibold text-[#1A202C]">
                                Specify your department / local no.
                            </label>

                            <input
                                type="text"
                                id="department_other"
                                name="department_other"
                                value="{{ $otherValue }}"
                                maxlength="255"
                                placeholder="Type your department"
                                x-bind:required="dept === 'Others'"
                                class="mt-1.5 block w-full rounded-md border-[#CBD5E1] text-[#1A202C] shadow-sm focus:border-[#B21F24] focus:ring-[#B21F24]"
                            >

                            @error('department_other')
                                <p class="mt-1 text-sm text-[#B91C1C]">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="mobile_number" class="block text-sm font-semibold text-[#1A202C]">
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
                            class="mt-1.5 block w-full rounded-md border-[#CBD5E1] text-[#1A202C] shadow-sm focus:border-[#B21F24] focus:ring-[#B21F24]"
                        >

                        @error('mobile_number')
                            <p class="mt-1 text-sm text-[#B91C1C]">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="mt-6 w-full rounded-md bg-[#B21F24] px-4 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#8A151B] focus:outline-none focus:ring-2 focus:ring-[#B21F24] focus:ring-offset-2"
                    >
                        Save profile
                    </button>
                </form>

            </div>
        </div>
    @endif

</x-app-layout>
