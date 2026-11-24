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

        abort_unless($student, 404);

        $enrollments = $student->enrollments()
            ->latest()
            ->paginate(10);

        return view('student.enrollments.index', compact('enrollments'));
    }

    public function show(Request $request, int $enrollment): View
    {
        $student = $request->user()->student;

        abort_unless($student, 404);

        $enrollmentRecord = $student->enrollments()->findOrFail($enrollment);

        return view('student.enrollments.show', [
            'enrollment' => $enrollmentRecord,
        ]);
    }
}
