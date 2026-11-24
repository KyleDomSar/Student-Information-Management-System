<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Student;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

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
                        ->orWhere('semester', 'like', "%{$search}%")
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
            'academic_year' => [
                'required',
                'regex:/^\d{4}-\d{4}$/',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    [$startYear, $endYear] = array_map('intval', explode('-', $value));

                    if ($endYear !== $startYear + 1) {
                        $fail('The academic year must contain consecutive years, such as 2026-2027.');
                    }
                },
            ],
            'semester' => ['required', Rule::in(['First Semester', 'Second Semester', 'Midyear'])],
        ], [
            'student_id.unique' => 'This student already has an enrollment record for the selected academic year and semester.',
        ]);

        $student = Student::with('user')->findOrFail($data['student_id']);

        if (! $student->user->is_active) {
            return back()->withErrors(['student_id' => 'The selected student account is inactive.'])->withInput();
        }

        $enrollment = DB::transaction(function () use ($data, $request, $auditLogger, $student) {
            $record = Enrollment::create([
                ...$data,
                'created_by' => $request->user()->id,
            ]);

            $auditLogger->record(
                $request->user(),
                'enrollment.created',
                $record,
                'Recorded enrollment for '.$student->student_number.' ('.$record->academic_year.', '.$record->semester.').',
                $request,
            );

            return $record;
        });

        return redirect()->route('admin.enrollments.show', $enrollment)
            ->with('success', 'Enrollment record created successfully.');
    }

    public function show(Enrollment $enrollment): View
    {
        $enrollment->load(['student.user', 'student.program', 'student.yearLevelRecord', 'student.section', 'creator']);

        return view('admin.enrollments.show', compact('enrollment'));
    }
}
