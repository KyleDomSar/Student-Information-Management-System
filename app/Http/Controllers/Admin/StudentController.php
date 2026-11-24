<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use App\Models\YearLevel;
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
            ->with(['user', 'program', 'section'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($studentQuery) use ($search) {
                    $studentQuery
                        ->where('student_number', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
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
        $yearLevels = YearLevel::where('is_active', true)->orderBy('level_number')->get();
        $sections = Section::with(['program', 'yearLevel'])
            ->where('is_active', true)
            ->orderBy('year_level')
            ->orderBy('code')
            ->get();

        return view('admin.students.create', compact('programs', 'yearLevels', 'sections'));
    }

    public function store(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:10', 'confirmed'],
            'student_number' => ['required', 'string', 'max:50', Rule::unique('students', 'student_number')],
            'program_id' => ['nullable', 'integer', Rule::exists('programs', 'id')->where('is_active', true)],
            'year_level' => ['nullable', 'integer', Rule::exists('year_levels', 'level_number')->where('is_active', true)],
            'section_id' => [
                'nullable',
                'integer',
                Rule::exists('sections', 'id')->where(fn ($query) => $query
                    ->where('is_active', true)
                    ->where('program_id', $request->input('program_id'))
                    ->where('year_level', $request->input('year_level'))),
            ],
            'status' => ['required', Rule::in(['active', 'inactive', 'graduated'])],
        ];

        if ($request->user()->isAdministrator()) {
            $rules['account_active'] = ['required', 'boolean'];
        }

        $data = $request->validate($rules);
        $accountIsActive = $request->user()->isAdministrator()
            ? (bool) $data['account_active']
            : true;

        $student = DB::transaction(function () use ($data, $accountIsActive) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => User::ROLE_STUDENT,
                'is_active' => $accountIsActive,
            ]);

            return Student::create([
                'user_id' => $user->id,
                'student_number' => $data['student_number'],
                'program_id' => $data['program_id'] ?? null,
                'year_level' => $data['year_level'] ?? null,
                'section_id' => $data['section_id'] ?? null,
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

        return redirect()->route('admin.students.show', $student)
            ->with('success', 'Student account and profile created successfully.');
    }

    public function show(Student $student): View
    {
        $student->load([
            'user',
            'program',
            'yearLevelRecord',
            'section',
            'visitorTimeouts' => fn ($query) => $query->latest('time_out_at')->limit(10),
            'enrollments' => fn ($query) => $query->latest(),
        ]);

        return view('admin.students.show', compact('student'));
    }

    public function edit(Student $student): View
    {
        $student->load(['user', 'section']);

        $programs = Program::where('is_active', true)
            ->orWhereKey($student->program_id)
            ->orderBy('name')
            ->get();

        $yearLevels = YearLevel::where('is_active', true)
            ->orWhere('level_number', $student->year_level)
            ->orderBy('level_number')
            ->get();

        $sections = Section::with(['program', 'yearLevel'])
            ->where('is_active', true)
            ->orWhereKey($student->section_id)
            ->orderBy('year_level')
            ->orderBy('code')
            ->get();

        return view('admin.students.edit', compact('student', 'programs', 'yearLevels', 'sections'));
    }

    public function update(Request $request, Student $student, AuditLogger $auditLogger): RedirectResponse
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($student->user_id)],
            'student_number' => ['required', 'string', 'max:50', Rule::unique('students', 'student_number')->ignore($student->id)],
            'program_id' => [
                'nullable',
                'integer',
                Rule::exists('programs', 'id')->where(fn ($query) => $query
                    ->where('is_active', true)
                    ->orWhere('id', $student->program_id)),
            ],
            'year_level' => [
                'nullable',
                'integer',
                Rule::exists('year_levels', 'level_number')->where(fn ($query) => $query
                    ->where('is_active', true)
                    ->orWhere('level_number', $student->year_level)),
            ],
            'section_id' => [
                'nullable',
                'integer',
                Rule::exists('sections', 'id')->where(fn ($query) => $query
                    ->where(fn ($active) => $active->where('is_active', true)->orWhere('id', $student->section_id))
                    ->where('program_id', $request->input('program_id'))
                    ->where('year_level', $request->input('year_level'))),
            ],
            'status' => ['required', Rule::in(['active', 'inactive', 'graduated'])],
        ];

        if ($request->user()->isAdministrator()) {
            $rules['password'] = ['nullable', 'string', 'min:10', 'confirmed'];
        }

        $data = $request->validate($rules);

        DB::transaction(function () use ($data, $student) {
            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
            ];

            if (! empty($data['password'])) {
                $userData['password'] = $data['password'];
            }

            $student->user->update($userData);
            $student->update([
                'student_number' => $data['student_number'],
                'program_id' => $data['program_id'] ?? null,
                'year_level' => $data['year_level'] ?? null,
                'section_id' => $data['section_id'] ?? null,
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

        return redirect()->route('admin.students.show', $student)
            ->with('success', 'Student record updated successfully.');
    }

    public function updateAccountStatus(Request $request, Student $student, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($request->user()->isAdministrator(), 403);

        $data = $request->validate(['is_active' => ['required', 'boolean']]);
        $student->user->update(['is_active' => (bool) $data['is_active']]);

        $auditLogger->record(
            $request->user(),
            'student.account_status_updated',
            $student,
            ($data['is_active'] ? 'Activated' : 'Deactivated').' student portal account '.$student->student_number.'.',
            $request,
        );

        return redirect()->route('admin.students.show', $student)
            ->with('success', 'Student portal account status updated. The academic record was preserved.');
    }

    public function destroy(Request $request, Student $student, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($request->user()->isAdministrator(), 403);

        if ($student->enrollments()->exists()) {
            return back()->withErrors([
                'student' => 'This student has enrollment history and cannot be permanently deleted. Deactivate the portal account instead.',
            ]);
        }

        if ($student->visitorTimeouts()->exists()) {
            return back()->withErrors([
                'student' => 'This student has visitor time-out history and cannot be permanently deleted. Deactivate the portal account instead.',
            ]);
        }

        $studentNumber = $student->student_number;
        $user = $student->user;

        DB::transaction(function () use ($student, $user) {
            $student->delete();
            $user->delete();
        });

        $auditLogger->record(
            $request->user(),
            'student.deleted',
            $student,
            'Permanently deleted student record '.$studentNumber.'.',
            $request,
        );

        return redirect()->route('admin.students.index')
            ->with('success', 'Student record and its login account were deleted.');
    }
}
