<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
    
        if (($validated['department'] ?? null) === 'Others') {
            $validated['department'] = $validated['department_other'];
        }
    
        unset($validated['department_other']);
    
        $request->user()->fill($validated);
    
        $request->user()->save();
    
        return Redirect::route('profile.edit')
            ->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
       /**
     * Complete the student's required profile information.
     */
    public function completeStudentProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'department'       => ['required', 'string', Rule::in([...config('departments'), 'Others'])],
            'department_other' => ['required_if:department,Others', 'nullable', 'string', 'max:255'],
            'mobile_number'    => ['required', 'string', 'max:30'],
        ]);

        if ($validated['department'] === 'Others') {
            $validated['department'] = trim($validated['department_other']);
        }
        unset($validated['department_other']);

        $request->user()->update($validated);

        return Redirect::route('dashboard')
            ->with('status', 'Student profile completed successfully.');
    }
}
