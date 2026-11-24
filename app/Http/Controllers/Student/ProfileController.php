<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __invoke(Request $request): View
    {
        $student = $request->user()
            ->student()
            ->with(['program', 'section.program', 'yearLevelRecord'])
            ->first();

        return view('student.profile', compact('student'));
    }

    public function update(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->student()->exists(), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update($data);

        $auditLogger->record(
            $user,
            'student.profile_updated',
            $user,
            'Student updated permitted personal profile information.',
            $request,
        );

        return back()->with('success', 'Your personal information has been updated.');
    }

    public function updatePassword(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->student()->exists(), 404);

        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:10', 'confirmed', 'different:current_password'],
        ]);

        $user->update(['password' => $data['password']]);

        $auditLogger->record(
            $user,
            'student.password_changed',
            $user,
            'Student changed their own account password.',
            $request,
        );

        return back()->with('success', 'Your password has been changed successfully.');
    }
}
