<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Enrollment;
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
            'staff' => User::whereIn('role', ['admin', 'staff'])->count(),
            'enrollments' => Enrollment::count(),
            'pending_enrollments' => Enrollment::where('status', 'pending')->count(),
        ];

        // Audit activity is restricted to administrators.
        $canViewAuditLogs = $user->isAdministrator();
        $recentLogs = $canViewAuditLogs
            ? AuditLog::with('user')->latest('created_at')->limit(8)->get()
            : collect();

        return view('admin.dashboard', compact('stats', 'recentLogs', 'canViewAuditLogs'));
    }
}
