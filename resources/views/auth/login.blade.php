<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />
        <a href="{{ url('/') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-[#4A5568] hover:text-[#B21F24]">
        <span aria-hidden="true">&larr;</span> Back
    </a>
    <div class="mb-4">
        <h1 class="text-lg font-semibold text-[#1A202C]">CPDO staff sign in</h1>
        <p class="mt-1 text-sm text-[#4A5568]">Students do not sign in here. Go back to the home page and choose <strong>Log in with your student account</strong>.</p>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
                <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <div x-data="{ show: false }" class="relative mt-1">
                <x-text-input id="password" class="block w-full pe-16"
                                x-bind:type="show ? 'text' : 'password'"
                                name="password"
                                required autocomplete="current-password" />

                <button type="button"
                        @click="show = !show"
                        class="absolute inset-y-0 end-0 px-3 text-sm font-medium text-[#4A5568] hover:text-[#B21F24] focus:outline-none"
                        x-text="show ? 'Hide' : 'Show'"
                        x-bind:aria-label="show ? 'Hide password' : 'Show password'"></button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-[#D9A6AA] text-[#B21F24] shadow-sm focus:ring-[#B21F24]" name="remember">
                <span class="ms-2 text-sm text-[#4A5568]">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="rounded-md text-sm text-[#4A5568] underline hover:text-[#B21F24] focus:outline-none focus:ring-2 focus:ring-[#B21F24] focus:ring-offset-2" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
