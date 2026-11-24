<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    public function index(Request $request): View
    {
        $student = $request->user()->student;

        $enrollments = $student
            ? $student->enrollments()
                ->withCount('enrollmentSubjects')
                ->latest()
                ->paginate(10)
            : collect();

        return view('student.enrollments.index', compact('enrollments'));
    }

    public function show(Request $request, int $enrollment): View
    {
        $student = $request->user()->student;

        abort_unless($student, 404);

        // Query through the authenticated student's relationship to prevent IDOR.
        $enrollmentRecord = $student->enrollments()
            ->with([
                'student',
                'enrollmentSubjects.subject.program',
                'enrollmentSubjects.classSchedules',
            ])
            ->findOrFail($enrollment);

        return view('student.enrollments.show', [
            'enrollment' => $enrollmentRecord,
        ]);
    }
}
