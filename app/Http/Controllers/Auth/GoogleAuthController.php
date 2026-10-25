<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->user();

        $email = $googleUser->getEmail();

        if (!$email || !($googleUser->user['email_verified'] ?? false)) {
            abort(403, 'A verified Google email address is required.');
        }

        // Find an existing account using Google's verified email.
        $user = User::where('email', $email)->first();

        if ($user) {
            // Keep CPDO admin accounts separate from student Google login.
            if ($user->role === 'cpdo_admin') {
                return redirect()
                    ->route('login')
                    ->withErrors([
                        'email' => 'Please use the CPDO administrator login.',
                    ]);
            }
        } else {
            // New Google accounts are students by default.
            $user = User::create([
                'name' => $googleUser->getName() ?: 'Student',
                'email' => $email,
                'password' => bcrypt(Str::random(32)),
                'role' => 'student',
                'email_verified_at' => now(),
            ]);
        }

        Auth::login($user);

        request()->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }
}
