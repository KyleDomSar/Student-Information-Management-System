<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSchedule;
use App\Models\EnrollmentSubject;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClassScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $schedules = ClassSchedule::query()
            ->with(['enrollmentSubject.enrollment.student.user', 'enrollmentSubject.subject'])
            ->whereHas('enrollmentSubject.enrollment', fn ($query) => $query->where('status', 'enrolled'))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($scheduleQuery) use ($search) {
                    $scheduleQuery
                        ->where('day_of_week', 'like', "%{$search}%")
                        ->orWhere('room', 'like', "%{$search}%")
                        ->orWhereHas('enrollmentSubject.subject', function ($subjectQuery) use ($search) {
                            $subjectQuery
                                ->where('code', 'like', "%{$search}%")
                                ->orWhere('title', 'like', "%{$search}%");
                        })
                        ->orWhereHas('enrollmentSubject.enrollment.student', function ($studentQuery) use ($search) {
                            $studentQuery
                                ->where('student_number', 'like', "%{$search}%")
                                ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
                        });
                });
            })
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->paginate(15)
            ->withQueryString();

        return view('admin.schedules.index', compact('schedules', 'search'));
    }

    public function create(): View
    {
        $enrollmentSubjects = EnrollmentSubject::query()
            ->with(['enrollment.student.user', 'subject'])
            ->whereHas('enrollment', fn ($query) => $query->where('status', 'enrolled'))
            ->latest()
            ->get();

        return view('admin.schedules.create', compact('enrollmentSubjects'));
    }

    public function store(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validate([
            'enrollment_subject_id' => [
                'required',
                'integer',
                Rule::exists('enrollment_subjects', 'id'),
            ],
            'day_of_week' => ['required', Rule::in(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'room' => ['nullable', 'string', 'max:100'],
        ]);

        $enrollmentSubject = EnrollmentSubject::with('enrollment')
            ->findOrFail($data['enrollment_subject_id']);

        if ($enrollmentSubject->enrollment->status !== 'enrolled') {
            return back()
                ->withErrors(['enrollment_subject_id' => 'A class schedule can only be assigned to an approved enrollment.'])
                ->withInput();
        }

        $schedule = ClassSchedule::create($data);

        $auditLogger->record(
            $request->user(),
            'schedule.created',
            $schedule,
            'Created class schedule for enrollment subject #'.$enrollmentSubject->id.'.',
            $request,
        );

        return redirect()
            ->route('admin.schedules.index')
            ->with('success', 'Class schedule created successfully.');
    }

    public function edit(ClassSchedule $schedule): View
    {
        abort_unless(
            $schedule->enrollmentSubject()->whereHas('enrollment', fn ($query) => $query->where('status', 'enrolled'))->exists(),
            404
        );

        $schedule->load(['enrollmentSubject.enrollment.student.user', 'enrollmentSubject.subject']);

        $enrollmentSubjects = EnrollmentSubject::query()
            ->with(['enrollment.student.user', 'subject'])
            ->whereHas('enrollment', fn ($query) => $query->where('status', 'enrolled'))
            ->latest()
            ->get();

        return view('admin.schedules.edit', compact('schedule', 'enrollmentSubjects'));
    }

    public function update(Request $request, ClassSchedule $schedule, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless(
            $schedule->enrollmentSubject()->whereHas('enrollment', fn ($query) => $query->where('status', 'enrolled'))->exists(),
            404
        );

        $data = $request->validate([
            'enrollment_subject_id' => [
                'required',
                'integer',
                Rule::exists('enrollment_subjects', 'id'),
            ],
            'day_of_week' => ['required', Rule::in(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'room' => ['nullable', 'string', 'max:100'],
        ]);

        $enrollmentSubject = EnrollmentSubject::with('enrollment')
            ->findOrFail($data['enrollment_subject_id']);

        if ($enrollmentSubject->enrollment->status !== 'enrolled') {
            return back()
                ->withErrors(['enrollment_subject_id' => 'A class schedule can only be assigned to an approved enrollment.'])
                ->withInput();
        }

        $schedule->update($data);

        $auditLogger->record(
            $request->user(),
            'schedule.updated',
            $schedule,
            'Updated class schedule #'.$schedule->id.'.',
            $request,
        );

        return redirect()
            ->route('admin.schedules.index')
            ->with('success', 'Class schedule updated successfully.');
    }

    public function destroy(Request $request, ClassSchedule $schedule, AuditLogger $auditLogger): RedirectResponse
    {
        $scheduleId = $schedule->id;
        $auditLogger->record(
            $request->user(),
            'schedule.deleted',
            $schedule,
            'Deleted class schedule #'.$scheduleId.'.',
            $request,
        );

        $schedule->delete();

        return redirect()
            ->route('admin.schedules.index')
            ->with('success', 'Class schedule deleted successfully.');
    }
}
