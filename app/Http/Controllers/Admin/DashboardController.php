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
        $stats = [
            'students' => Student::count(),
            'staff' => User::whereIn('role', ['admin', 'staff', 'instructor'])->count(),
            'enrollments' => Enrollment::count(),
            'pending_enrollments' => Enrollment::where('status', 'pending')->count(),
        ];

        $recentLogs = AuditLog::with('user')
            ->latest('created_at')
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentLogs'));
    }
}
