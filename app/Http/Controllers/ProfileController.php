<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users')->ignore($user->id)->whereNull('deleted_at'),
            ],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return back()->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // current_password verifies against the authenticated user's hash,
            // so a hijacked session still cannot silently change the password.
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'current_password.current_password' => 'That is not your current password.',
        ]);

        $request->user()->update(['password' => $validated['password']]);

        Log::channel('security')->notice('Password changed', [
            'user_id' => $request->user()->id,
            'ip' => $request->ip(),
        ]);

        return back()->with('success', 'Password changed.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->isAdmin() && User::where('role', 'admin')->active()->count() <= 1) {
            return back()->with('error', 'You are the only administrator — promote someone else first.');
        }

        $request->validate(['password' => ['required', 'current_password']]);

        Log::channel('security')->notice('Account self-deleted', ['user_id' => $user->id]);

        auth()->logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Your account has been closed.');
    }
}
