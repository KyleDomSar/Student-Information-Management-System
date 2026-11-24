<?php

namespace App\\Http\\Controllers\\Admin;

use App\\Http\\Controllers\\Controller;
use App\\Models\\Program;
use App\\Support\\AuditLogger;
use Illuminate\\Http\\RedirectResponse;
use Illuminate\\Http\\Request;
use Illuminate\\Validation\\Rule;
use Illuminate\\View\\View;

class ProgramController extends Controller
{
    public function index(): View
    {
        $programs = Program::withCount('students')
            ->orderBy('name')
            ->paginate(10);

        return view('admin.programs.index', compact('programs'));
    }

    public function create(): View
    {
        return view('admin.programs.create');
    }

    public function store(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:30', Rule::unique('programs', 'code')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $program = Program::create([...$data, 'is_active' => true]);

        $auditLogger->record(
            $request->user(),
            'program.created',
            $program,
            'Created program '.$program->code.'.',
            $request,
        );

        return redirect()->route('admin.programs.index')
            ->with('success', 'Program created successfully.');
    }

    public function edit(Program $program): View
    {
        return view('admin.programs.edit', compact('program'));
    }

    public function update(Request $request, Program $program, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:30', Rule::unique('programs', 'code')->ignore($program->id)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
        ]);

        $program->update($data);

        $auditLogger->record(
            $request->user(),
            'program.updated',
            $program,
            'Updated program '.$program->code.'.',
            $request,
        );

        return redirect()->route('admin.programs.index')
            ->with('success', 'Program updated successfully.');
    }

    public function destroy(Request $request, Program $program, AuditLogger $auditLogger): RedirectResponse
    {
        $program->update(['is_active' => false]);

        $auditLogger->record(
            $request->user(),
            'program.deactivated',
            $program,
            'Deactivated program '.$program->code.'.',
            $request,
        );

        return redirect()->route('admin.programs.index')
            ->with('success', 'Program deactivated. Existing student records were preserved.');
    }
}
