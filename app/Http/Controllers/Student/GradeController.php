<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentSubject;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GradeController extends Controller
{
    public function __invoke(Request $request): View
    {
        $student = $request->user()->student;

        $grades = $student
            ? EnrollmentSubject::query()
                ->with(['enrollment', 'subject'])
                ->whereHas('enrollment', function ($query) use ($student) {
                    $query->where('student_id', $student->id)
                        ->where('status', 'enrolled');
                })
                ->latest()
                ->get()
            : collect();

        return view('student.grades.index', compact('grades'));
    }
}
