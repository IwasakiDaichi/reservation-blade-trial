<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')
            ->scopes([
                'openid',
                'email',
                'profile',
                'https://www.googleapis.com/auth/user.phonenumbers.read',
            ])
            ->with([
                'include_granted_scopes' => 'true',
            ])
            ->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->user();

        $email = $googleUser->getEmail();

        if (!$email || !($googleUser->user['email_verified'] ?? false)) {
            abort(403, 'A verified Google email address is required.');
        }

        $user = User::where('email', $email)->first();

        if ($user && $user->role === 'cpdo_admin') {
            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Please use the CPDO administrator login.',
                ]);
        }

        if (!$user) {
            $user = User::create([
                'name' => $googleUser->getName() ?: 'Student',
                'email' => $email,
                'password' => bcrypt(Str::random(32)),
                'role' => 'student',
                'email_verified_at' => now(),
            ]);
        }

        // Retrieve the phone number from Google People API.
        $response = Http::withToken($googleUser->token)
            ->get('https://people.googleapis.com/v1/people/me', [
                'personFields' => 'phoneNumbers',
            ]);

        if ($response->successful() && blank($user->mobile_number)) {
            $phoneNumbers = collect(
                $response->json('phoneNumbers', [])
            );

            $selectedPhone = $phoneNumbers->first(
                fn ($phone) => data_get($phone, 'metadata.primary')
            ) ?? $phoneNumbers->first();

            $googlePhone = data_get($selectedPhone, 'canonicalForm')
                ?: data_get($selectedPhone, 'value');

            if (filled($googlePhone)) {
                $user->mobile_number = $googlePhone;
                $user->save();
            }
        }

        Auth::login($user);
        request()->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }
}
