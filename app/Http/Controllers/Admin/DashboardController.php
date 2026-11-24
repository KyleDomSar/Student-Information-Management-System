<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Enrollment;
use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();

        $stats = [
            'students' => Student::count(),
            'staff' => User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_STAFF])->count(),
            'programs' => Program::where('is_active', true)->count(),
            'enrollments' => Enrollment::count(),
        ];

        $canViewAuditLogs = $user->isAdministrator();
        $recentLogs = $canViewAuditLogs
            ? AuditLog::with('user')->latest('created_at')->limit(8)->get()
            : collect();

        return view('admin.dashboard', compact('stats', 'recentLogs', 'canViewAuditLogs'));
    }
}
