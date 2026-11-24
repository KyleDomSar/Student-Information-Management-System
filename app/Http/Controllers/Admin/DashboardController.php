<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Student;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $canViewAuditLogs = auth()->user()->isAdministrator();
        $recentLogs = $canViewAuditLogs
            ? AuditLog::with('user')->latest('created_at')->limit(8)->get()
            : collect();

        $totalStudents = Student::count();

        return view('admin.dashboard', compact('totalStudents', 'recentLogs', 'canViewAuditLogs'));
    }
}
