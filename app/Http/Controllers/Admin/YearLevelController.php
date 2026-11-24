<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\YearLevel;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class YearLevelController extends Controller
{
    public function index(): View
    {
        $yearLevels = YearLevel::withCount(['students', 'sections'])
            ->orderBy('level_number')
            ->paginate(10);

        return view('admin.year-levels.index', compact('yearLevels'));
    }

    public function create(): View
    {
        return view('admin.year-levels.create');
    }

    public function store(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($request->user()->isAdministrator(), 403);

        $data = $request->validate([
            'level_number' => ['required', 'integer', 'between:1,255', Rule::unique('year_levels', 'level_number')],
            'name' => ['required', 'string', 'max:50'],
        ]);

        $yearLevel = YearLevel::create([...$data, 'is_active' => true]);

        $auditLogger->record(
            $request->user(),
            'year_level.created',
            $yearLevel,
            'Created year level '.$yearLevel->name.'.',
            $request,
        );

        return redirect()->route('admin.year-levels.index')->with('success', 'Year level created successfully.');
    }

    public function edit(YearLevel $yearLevel): View
    {
        return view('admin.year-levels.edit', compact('yearLevel'));
    }

    public function update(Request $request, YearLevel $yearLevel, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($request->user()->isAdministrator(), 403);

        $data = $request->validate([
            'level_number' => [
                'required',
                'integer',
                'between:1,255',
                Rule::unique('year_levels', 'level_number')->ignore($yearLevel->id),
            ],
            'name' => ['required', 'string', 'max:50'],
            'is_active' => ['required', 'boolean'],
        ]);

        if (
            (int) $data['level_number'] !== $yearLevel->level_number
            && ($yearLevel->sections()->exists() || $yearLevel->students()->exists())
        ) {
            return back()->withErrors([
                'level_number' => 'The year level number cannot change while student records or sections are assigned to it.',
            ])->withInput();
        }

        $yearLevel->update($data);

        $auditLogger->record(
            $request->user(),
            'year_level.updated',
            $yearLevel,
            'Updated year level '.$yearLevel->name.'.',
            $request,
        );

        return redirect()->route('admin.year-levels.index')->with('success', 'Year level updated successfully.');
    }

    public function destroy(Request $request, YearLevel $yearLevel, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($request->user()->isAdministrator(), 403);

        $yearLevel->update(['is_active' => false]);

        $auditLogger->record(
            $request->user(),
            'year_level.deactivated',
            $yearLevel,
            'Deactivated year level '.$yearLevel->name.'.',
            $request,
        );

        return redirect()->route('admin.year-levels.index')->with('success', 'Year level deactivated. Existing student records were preserved.');
    }
}
