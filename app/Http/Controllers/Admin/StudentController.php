<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $students = Student::query()
            ->with(['user', 'program'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($studentQuery) use ($search) {
                    $studentQuery
                        ->where('student_number', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.students.index', compact('students', 'search'));
    }

    public function create(): View
    {
        $programs = Program::where('is_active', true)->orderBy('name')->get();

        return view('admin.students.create', compact('programs'));
    }

    public function store(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:10', 'confirmed'],
            'student_number' => ['required', 'string', 'max:50', Rule::unique('students', 'student_number')],
            'program_id' => ['nullable', 'integer', Rule::exists('programs', 'id')->where('is_active', true)],
            'year_level' => ['nullable', 'integer', 'between:1,6'],
            'status' => ['required', Rule::in(['active', 'inactive', 'graduated'])],
            'account_active' => ['required', 'boolean'],
        ]);

        $student = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => User::ROLE_STUDENT,
                'is_active' => $data['account_active'],
            ]);

            return Student::create([
                'user_id' => $user->id,
                'student_number' => $data['student_number'],
                'program_id' => $data['program_id'] ?? null,
                'year_level' => $data['year_level'] ?? null,
                'status' => $data['status'],
            ]);
        });

        $auditLogger->record(
            $request->user(),
            'student.created',
            $student,
            'Created student record '.$student->student_number.'.',
            $request,
        );

        return redirect()
            ->route('admin.students.show', $student)
            ->with('success', 'Student account and profile created successfully.');
    }

    public function show(Student $student): View
    {
        $student->load(['user', 'program', 'enrollments.enrollmentSubjects.subject']);

        return view('admin.students.show', compact('student'));
    }

    public function edit(Student $student): View
    {
        $student->load('user');
        $programs = Program::where('is_active', true)
            ->orWhereKey($student->program_id)
            ->orderBy('name')
            ->get();

        return view('admin.students.edit', compact('student', 'programs'));
    }

    public function update(Request $request, Student $student, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($student->user_id),
            ],
            'password' => ['nullable', 'string', 'min:10', 'confirmed'],
            'student_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('students', 'student_number')->ignore($student->id),
            ],
            'program_id' => [
                'nullable',
                'integer',
                Rule::exists('programs', 'id')->where(
                    fn ($query) => $query->where('is_active', true)->orWhere('id', $student->program_id)
                ),
            ],
            'year_level' => ['nullable', 'integer', 'between:1,6'],
            'status' => ['required', Rule::in(['active', 'inactive', 'graduated'])],
            'account_active' => ['required', 'boolean'],
        ]);

        DB::transaction(function () use ($data, $student) {
            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'is_active' => $data['account_active'],
            ];

            if (! empty($data['password'])) {
                $userData['password'] = $data['password'];
            }

            $student->user->update($userData);

            $student->update([
                'student_number' => $data['student_number'],
                'program_id' => $data['program_id'] ?? null,
                'year_level' => $data['year_level'] ?? null,
                'status' => $data['status'],
            ]);
        });

        $auditLogger->record(
            $request->user(),
            'student.updated',
            $student,
            'Updated student record '.$student->student_number.'.',
            $request,
        );

        return redirect()
            ->route('admin.students.show', $student)
            ->with('success', 'Student record updated successfully.');
    }

    /**
     * Deactivate instead of deleting official student history.
     */
    public function destroy(Request $request, Student $student, AuditLogger $auditLogger): RedirectResponse
    {
        DB::transaction(function () use ($student) {
            $student->user->update(['is_active' => false]);

            if ($student->status === 'active') {
                $student->update(['status' => 'inactive']);
            }
        });

        $auditLogger->record(
            $request->user(),
            'student.deactivated',
            $student,
            'Deactivated student account '.$student->student_number.'.',
            $request,
        );

        return redirect()
            ->route('admin.students.index')
            ->with('success', 'Student account deactivated. The academic record was preserved.');
    }
}
