<section>
    <header>
        <h2 class="text-lg font-semibold text-slate-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm leading-6 text-slate-600">
            {{ __("Update your account's profile information.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

                <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" type="text" class="mt-1 block w-full bg-gray-100 text-gray-500 cursor-not-allowed" :value="$user->name" disabled />

        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" class="mt-1 block w-full bg-gray-100 text-gray-500 cursor-not-allowed" :value="$user->email" disabled />



















        </div>
        @if ($user->role === 'student')
                        @php
                $departments = \App\Models\Department::listedNames();
                $currentDepartment = old('department', $user->department?->name);
                $isOther = old('department') === 'Others'
                    || (filled($currentDepartment) && ! in_array($currentDepartment, $departments, true));
                $otherValue = old('department_other', $isOther && $currentDepartment !== 'Others' ? $currentDepartment : '');
            @endphp

            <div x-data="{ dept: @js($isOther ? 'Others' : ($currentDepartment ?? '')) }">
                <x-input-label for="department" :value="__('Department / Local No.')" />

                <select
                    id="department"
                    name="department"
                    x-model="dept"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                >
                    <option value="">Select your department</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept }}">{{ $dept }}</option>
                    @endforeach
                    <option value="Others">Others (please specify)</option>
                </select>

                <x-input-error class="mt-2" :messages="$errors->get('department')" />

                <div x-show="dept === 'Others'" x-cloak class="mt-3">
                    <x-input-label for="department_other" :value="__('Specify your department / local no.')" />

                    <x-text-input
                        id="department_other"
                        name="department_other"
                        type="text"
                        class="mt-1 block w-full"
                        :value="$otherValue"
                        maxlength="255"
                        placeholder="Type your department"
                    />

                    <x-input-error class="mt-2" :messages="$errors->get('department_other')" />
                </div>
            </div>

            <div>
                <x-input-label for="mobile_number" :value="__('Mobile Number')" />

                <x-text-input
                    id="mobile_number"
                    name="mobile_number"
                    type="tel"
                    class="mt-1 block w-full"
                    :value="old('mobile_number', $user->mobile_number)"

                    placeholder="09XXXXXXXXX"
                />

                <x-input-error
                    class="mt-2"
                    :messages="$errors->get('mobile_number')"
                />
            </div>
        @endif

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-slate-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
