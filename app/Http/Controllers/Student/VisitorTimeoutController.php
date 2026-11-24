<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VisitorTimeoutController extends Controller
{
    public function index(Request $request): View
    {
        $student = $request->user()->student;

        abort_unless($student, 404);

        $visitorTimeouts = $student->visitorTimeouts()
            ->latest('time_out_at')
            ->paginate(15);

        return view('student.visitor-timeouts.index', compact('student', 'visitorTimeouts'));
    }

    public function store(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $student = $request->user()->student;

        abort_unless($student, 404);

        DB::transaction(function () use ($student, $request, $auditLogger): void {
            $visitorTimeout = $student->visitorTimeouts()->create([
                'time_out_at' => now(),
            ]);

            $auditLogger->record(
                $request->user(),
                'student.visitor_time_out_recorded',
                $visitorTimeout,
                'Recorded visitor time-out for student '.$student->student_number.'.',
                $request,
            );
        });

        return redirect()
            ->route('student.visitor-timeouts.index')
            ->with('success', 'Visitor time-out recorded successfully.');
    }
}
