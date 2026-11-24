<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Subject;
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
        $status = $request->query('status');

        $enrollments = Enrollment::query()
            ->with(['student.user', 'student.program'])
            ->withCount('enrollmentSubjects')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($enrollmentQuery) use ($search) {
                    $enrollmentQuery
                        ->where('academic_year', 'like', "%{$search}%")
                        ->orWhereHas('student', function ($studentQuery) use ($search) {
                            $studentQuery
                                ->where('student_number', 'like', "%{$search}%")
                                ->orWhereHas('user', function ($userQuery) use ($search) {
                                    $userQuery->where('name', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->when(in_array($status, ['pending', 'enrolled', 'rejected'], true), fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.enrollments.index', compact('enrollments', 'search', 'status'));
    }

    public function create(): View
    {
        $students = Student::query()
            ->with(['user', 'program'])
            ->where('status', 'active')
            ->whereHas('user', fn ($query) => $query->where('is_active', true))
            ->orderBy('student_number')
            ->get();

        $subjects = Subject::query()
            ->with('program')
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('admin.enrollments.create', compact('students', 'subjects'));
    }

    public function store(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => [
                'required',
                'integer',
                Rule::exists('students', 'id')->where('status', 'active'),
                Rule::unique('enrollments', 'student_id')->where(
                    fn ($query) => $query
                        ->where('academic_year', $request->input('academic_year'))
                        ->where('semester', $request->input('semester'))
                ),
            ],
            'academic_year' => ['required', 'regex:/^\d{4}-\d{4}$/'],
            'semester' => ['required', Rule::in(['First', 'Second', 'Summer'])],
            'subjects' => ['required', 'array', 'min:1', 'max:20'],
            'subjects.*' => ['required', 'integer', 'distinct', Rule::exists('subjects', 'id')->where('is_active', true)],
        ], [
            'student_id.unique' => 'This student already has an enrollment record for the selected academic year and semester.',
            'subjects.required' => 'Select at least one subject for this enrollment.',
        ]);

        $student = Student::with('user')->findOrFail($data['student_id']);

        if (! $student->user->is_active) {
            return back()
                ->withErrors(['student_id' => 'The selected student account is inactive.'])
                ->withInput();
        }

        $validSubjectCount = Subject::query()
            ->whereIn('id', $data['subjects'])
            ->where('is_active', true)
            ->where(function ($query) use ($student) {
                $query->whereNull('program_id')
                    ->orWhere('program_id', $student->program_id);
            })
            ->count();

        if ($validSubjectCount !== count($data['subjects'])) {
            return back()
                ->withErrors(['subjects' => 'One or more selected subjects are inactive or do not belong to the student program.'])
                ->withInput();
        }

        $enrollment = DB::transaction(function () use ($data, $request) {
            $enrollment = Enrollment::create([
                'student_id' => $data['student_id'],
                'academic_year' => $data['academic_year'],
                'semester' => $data['semester'],
                'status' => 'pending',
                'created_by' => $request->user()->id,
            ]);

            $enrollment->enrollmentSubjects()->createMany(
                collect($data['subjects'])
                    ->map(fn ($subjectId) => ['subject_id' => $subjectId])
                    ->all()
            );

            return $enrollment;
        });

        $auditLogger->record(
            $request->user(),
            'enrollment.created',
            $enrollment,
            'Created pending enrollment for '.$student->student_number.' ('.$enrollment->academic_year.', '.$enrollment->semester.').',
            $request,
        );

        return redirect()
            ->route('admin.enrollments.show', $enrollment)
            ->with('success', 'Enrollment created and marked as pending approval.');
    }

    public function show(Enrollment $enrollment): View
    {
        $enrollment->load([
            'student.user',
            'student.program',
            'enrollmentSubjects.subject.program',
            'creator',
        ]);

        return view('admin.enrollments.show', compact('enrollment'));
    }

    public function updateStatus(Request $request, Enrollment $enrollment, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['enrolled', 'rejected'])],
        ]);

        if ($enrollment->status !== 'pending') {
            return back()->withErrors([
                'status' => 'Only pending enrollments can be approved or rejected.',
            ]);
        }

        $enrollment->update(['status' => $data['status']]);

        $auditLogger->record(
            $request->user(),
            'enrollment.status_updated',
            $enrollment,
            'Enrollment '.$enrollment->id.' was '.$data['status'].'.',
            $request,
        );

        return redirect()
            ->route('admin.enrollments.show', $enrollment)
            ->with('success', 'Enrollment status updated to '.$data['status'].'.');
    }
}
