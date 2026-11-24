<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentSubject;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GradeController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $grades = EnrollmentSubject::query()
            ->with(['enrollment.student.user', 'subject'])
            ->whereHas('enrollment', fn ($query) => $query->where('status', 'enrolled'))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($gradeQuery) use ($search) {
                    $gradeQuery
                        ->whereHas('subject', function ($subjectQuery) use ($search) {
                            $subjectQuery
                                ->where('code', 'like', "%{$search}%")
                                ->orWhere('title', 'like', "%{$search}%");
                        })
                        ->orWhereHas('enrollment.student', function ($studentQuery) use ($search) {
                            $studentQuery
                                ->where('student_number', 'like', "%{$search}%")
                                ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
                        });
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.grades.index', compact('grades', 'search'));
    }

    public function edit(EnrollmentSubject $enrollmentSubject): View
    {
        abort_unless(
            $enrollmentSubject->enrollment()->where('status', 'enrolled')->exists(),
            404
        );

        $enrollmentSubject->load(['enrollment.student.user', 'subject']);

        return view('admin.grades.edit', compact('enrollmentSubject'));
    }

    public function update(
        Request $request,
        EnrollmentSubject $enrollmentSubject,
        AuditLogger $auditLogger
    ): RedirectResponse {
        abort_unless(
            $enrollmentSubject->enrollment()->where('status', 'enrolled')->exists(),
            404
        );

        $data = $request->validate([
            'final_grade' => [
                'required',
                'numeric',
                'min:'.config('sims.grade_scale.minimum'),
                'max:'.config('sims.grade_scale.maximum'),
            ],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $enrollmentSubject->update($data);

        $auditLogger->record(
            $request->user(),
            'grade.updated',
            $enrollmentSubject,
            'Updated grade for '.$enrollmentSubject->subject()->value('code').' (enrollment #'.$enrollmentSubject->enrollment_id.').',
            $request,
        );

        return redirect()
            ->route('admin.grades.index')
            ->with('success', 'Grade saved successfully.');
    }
}
