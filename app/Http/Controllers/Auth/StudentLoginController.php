<?php

namespace App\Http\Controllers\Auth;

use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentLoginController
{
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return Auth::user()->isStudent()
                ? redirect()->route('student.dashboard')
                : redirect()->route('admin.dashboard');
        }

        return view('auth.student-login');
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
            $auditLogger->record(null, 'auth.login_failed', description: 'Failed Student Portal login.', request: $request);

            return back()
                ->withErrors(['email' => 'The provided credentials are invalid or the account is inactive.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = $request->user();

        if (! $user->isStudent()) {
            $auditLogger->record($user, 'auth.portal_access_denied', description: 'Non-student account attempted to sign in through the Student Portal.', request: $request);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['email' => 'This account is not authorized to access the Student Portal.'])
                ->onlyInput('email');
        }

        $auditLogger->record($user, 'auth.login', description: 'Signed in through the Student Portal.', request: $request);

        return redirect()->intended(route('student.dashboard'));
    }
}
