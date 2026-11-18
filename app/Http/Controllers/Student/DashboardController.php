<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $student = $request->user()
            ->student()
            ->with([
                'program',
                'enrollments' => fn ($query) => $query->latest()->limit(5),
                'enrollments.enrollmentSubjects.subject',
                'enrollments.enrollmentSubjects.classSchedules',
            ])
            ->first();

        return view('student.dashboard', compact('student'));
    }
}
