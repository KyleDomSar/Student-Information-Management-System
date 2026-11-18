<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PortalLoginController
{
    public function __invoke(): View|RedirectResponse
    {
        if (Auth::check()) {
            return Auth::user()->isStudent()
                ? redirect()->route('student.dashboard')
                : redirect()->route('admin.dashboard');
        }

        return view('auth.choose-login');
    }
}
