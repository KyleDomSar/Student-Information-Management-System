<?php

namespace App\Http\Controllers\Auth;

use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminLoginController
{
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return Auth::user()->isStudent()
                ? redirect()->route('student.dashboard')
                : redirect()->route('admin.dashboard');
        }

        return view('auth.admin-login');
    }

    public function store(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $credentials = [
            'email' => $validated['email'],
            'password' => $validated['password'],
            'is_active' => true,
        ];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            $auditLogger->record(null, 'auth.login_failed', description: 'Failed Admin/Staff portal login.', request: $request);

            return back()
                ->withErrors(['email' => 'The provided credentials are invalid or the account is inactive.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = $request->user();

        if (! $user->canAccessStaffPortal()) {
            $auditLogger->record($user, 'auth.portal_access_denied', description: 'Student account attempted to sign in through the Admin/Staff portal.', request: $request);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['email' => 'This account is not authorized to access the Admin/Staff portal.'])
                ->onlyInput('email');
        }

        $auditLogger->record($user, 'auth.login', description: 'Signed in through the Admin/Staff portal.', request: $request);

        return redirect()->intended(route('admin.dashboard'));
    }
}
