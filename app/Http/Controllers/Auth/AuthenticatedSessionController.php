<?php

namespace App\Http\Controllers\Auth;

use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController
{
    public function destroy(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $user = $request->user();
        $loginRoute = $user?->isStudent() ? 'student.login' : 'admin.login';

        $auditLogger->record($user, 'auth.logout', description: 'User signed out.', request: $request);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route($loginRoute);
    }
}
