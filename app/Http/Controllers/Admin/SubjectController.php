<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\Subject;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $subjects = Subject::query()
            ->with('program')
            ->withCount('enrollmentSubjects')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subjectQuery) use ($search) {
                    $subjectQuery
                        ->where('code', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%");
                });
            })
            ->orderBy('code')
            ->paginate(10)
            ->withQueryString();

        return view('admin.subjects.index', compact('subjects', 'search'));
    }

    public function create(): View
    {
        $programs = Program::where('is_active', true)->orderBy('name')->get();

        return view('admin.subjects.create', compact('programs'));
    }

    public function store(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validate([
            'program_id' => ['nullable', 'integer', Rule::exists('programs', 'id')->where('is_active', true)],
            'code' => ['required', 'string', 'max:40', Rule::unique('subjects', 'code')],
            'title' => ['required', 'string', 'max:255'],
            'units' => ['required', 'integer', 'between:1,12'],
        ]);

        $subject = Subject::create([
            ...$data,
            'is_active' => true,
        ]);

        $auditLogger->record(
            $request->user(),
            'subject.created',
            $subject,
            'Created subject '.$subject->code.'.',
            $request,
        );

        return redirect()
            ->route('admin.subjects.index')
            ->with('success', 'Subject created successfully.');
    }

    public function edit(Subject $subject): View
    {
        $programs = Program::where('is_active', true)
            ->orWhereKey($subject->program_id)
            ->orderBy('name')
            ->get();

        return view('admin.subjects.edit', compact('subject', 'programs'));
    }

    public function update(Request $request, Subject $subject, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validate([
            'program_id' => [
                'nullable',
                'integer',
                Rule::exists('programs', 'id')->where(
                    fn ($query) => $query->where('is_active', true)->orWhere('id', $subject->program_id)
                ),
            ],
            'code' => [
                'required',
                'string',
                'max:40',
                Rule::unique('subjects', 'code')->ignore($subject->id),
            ],
            'title' => ['required', 'string', 'max:255'],
            'units' => ['required', 'integer', 'between:1,12'],
            'is_active' => ['required', 'boolean'],
        ]);

        $subject->update($data);

        $auditLogger->record(
            $request->user(),
            'subject.updated',
            $subject,
            'Updated subject '.$subject->code.'.',
            $request,
        );

        return redirect()
            ->route('admin.subjects.index')
            ->with('success', 'Subject updated successfully.');
    }

    /**
     * Deactivate instead of deleting subjects referenced by academic history.
     */
    public function destroy(Request $request, Subject $subject, AuditLogger $auditLogger): RedirectResponse
    {
        $subject->update(['is_active' => false]);

        $auditLogger->record(
            $request->user(),
            'subject.deactivated',
            $subject,
            'Deactivated subject '.$subject->code.'.',
            $request,
        );

        return redirect()
            ->route('admin.subjects.index')
            ->with('success', 'Subject deactivated. Existing enrollment references were preserved.');
    }
}
