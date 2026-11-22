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

    /** True when the email belongs to one of the configured UB domains (or any email if none are configured). */
    private function isUbEmail(string $email): bool
    {
        $domains = config('reservation.ub_email_domains', []);

        if ($domains === []) {
            return true;
        }

        $host = strtolower(Str::after($email, '@'));

        foreach ($domains as $domain) {
            $domain = strtolower($domain);
            if ($host === $domain || str_ends_with($host, '.'.$domain)) {
                return true;
            }
        }

        return false;
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->user();

        $email = $googleUser->getEmail();

        if (!$email || !($googleUser->user['email_verified'] ?? false)) {
            abort(403, 'A verified Google email address is required.');
        }

        if (! $this->isUbEmail($email)) {
            return redirect('/')->with('login_error', 'Please log in with your University of Baguio account (for example name@s.ubaguio.edu). Other Google accounts cannot be used.');
        }

        $user = User::where('email', $email)->first();

        if ($user && $user->role === 'cpdo_admin') {
            return redirect('/')->with('login_error', 'This account is a CPDO staff account. Please use "CPDO sign in" with your staff email and password.');
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

        request()->session()->forget('url.intended');

        return redirect()->route('dashboard');
    }
}
