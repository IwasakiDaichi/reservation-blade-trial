<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-900">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="profile-page py-8 sm:py-10">
        <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="border-b border-slate-200 pb-6">
                <p class="text-sm leading-6 text-slate-600">
                    {{ auth()->user()->role === 'cpdo_admin' ? 'Update your contact details and password settings.' : 'Update your contact details.' }}
                </p>
            </div>
                @if (session('error'))
                <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
                    {{ session('error') }}
                </div>
                @endif
            <section class="profile-card">
                <div class="max-w-2xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </section>

            @if (auth()->user()->role === 'cpdo_admin')
                <section class="profile-card">
                    <div class="max-w-2xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </section>

                <section class="profile-card">
                    <div class="max-w-2xl">
                        @include('profile.partials.delete-user-form')
                    </div>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
