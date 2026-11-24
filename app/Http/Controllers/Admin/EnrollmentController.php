<?php

namespace App\\Http\\Controllers\\Admin;

use App\\Http\\Controllers\\Controller;
use App\\Models\\Enrollment;
use App\\Models\\Student;
use App\\Support\\AuditLogger;
use Illuminate\\Http\\RedirectResponse;
use Illuminate\\Http\\Request;
use Illuminate\\Validation\\Rule;
use Illuminate\\View\\View;

class EnrollmentController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $enrollments = Enrollment::query()
            ->with(['student.user', 'student.program', 'student.section'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($enrollmentQuery) use ($search) {
                    $enrollmentQuery
                        ->where('academic_year', 'like', "%{$search}%")
                        ->orWhereHas('student', function ($studentQuery) use ($search) {
                            $studentQuery->where('student_number', 'like', "%{$search}%")
                                ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.enrollments.index', compact('enrollments', 'search'));
    }

    public function create(): View
    {
        $students = Student::query()
            ->with(['user', 'program', 'section'])
            ->where('status', 'active')
            ->whereHas('user', fn ($query) => $query->where('is_active', true))
            ->orderBy('student_number')
            ->get();

        return view('admin.enrollments.create', compact('students'));
    }

    public function store(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => [
                'required',
                'integer',
                Rule::exists('students', 'id')->where('status', 'active'),
                Rule::unique('enrollments', 'student_id')->where(fn ($query) => $query
                    ->where('academic_year', $request->input('academic_year'))
                    ->where('semester', $request->input('semester'))),
            ],
            'academic_year' => ['required', 'regex:/^\\d{4}-\\d{4}$/'],
            'semester' => ['required', Rule::in(['First Semester', 'Second Semester', 'Midyear'])],
        ], [
            'student_id.unique' => 'This student already has an enrollment record for the selected academic year and semester.',
        ]);

        $student = Student::with('user')->findOrFail($data['student_id']);

        if (! $student->user->is_active) {
            return back()->withErrors(['student_id' => 'The selected student account is inactive.'])->withInput();
        }

        $enrollment = Enrollment::create([
            ...$data,
            'created_by' => $request->user()->id,
        ]);

        $auditLogger->record(
            $request->user(),
            'enrollment.created',
            $enrollment,
            'Recorded enrollment for '.$student->student_number.' ('.$enrollment->academic_year.', '.$enrollment->semester.').',
            $request,
        );

        return redirect()->route('admin.enrollments.show', $enrollment)
            ->with('success', 'Enrollment record created successfully.');
    }

    public function show(Enrollment $enrollment): View
    {
        $enrollment->load(['student.user', 'student.program', 'student.yearLevelRecord', 'student.section', 'creator']);

        return view('admin.enrollments.show', compact('enrollment'));
    }
}
