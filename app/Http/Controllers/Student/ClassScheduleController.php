<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ClassSchedule;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClassScheduleController extends Controller
{
    public function __invoke(Request $request): View
    {
        $student = $request->user()->student;

        $schedules = $student
            ? ClassSchedule::query()
                ->with(['enrollmentSubject.enrollment', 'enrollmentSubject.subject'])
                ->whereHas('enrollmentSubject.enrollment', function ($query) use ($student) {
                    $query->where('student_id', $student->id)
                        ->where('status', 'enrolled');
                })
                ->orderByRaw("CASE day_of_week
                WHEN 'Monday' THEN 1
                WHEN 'Tuesday' THEN 2
                WHEN 'Wednesday' THEN 3
                WHEN 'Thursday' THEN 4
                WHEN 'Friday' THEN 5
                WHEN 'Saturday' THEN 6
                WHEN 'Sunday' THEN 7
                ELSE 8
            END")
                ->orderBy('start_time')
                ->get()
            : collect();

        return view('student.schedules.index', compact('schedules'));
    }
}
