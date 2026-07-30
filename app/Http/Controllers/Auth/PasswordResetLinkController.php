<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $status = Password::sendResetLink($request->only('email'));

        /*
        | Always report success, whatever the broker returns. Surfacing
        | "we can't find a user with that address" turns the form into an
        | account enumeration oracle.
        */
        return back()->with(
            $status === Password::RESET_LINK_SENT ? 'success' : 'info',
            'If that email matches an account, a reset link has been sent to it.'
        );
    }
}
