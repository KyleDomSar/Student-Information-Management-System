<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\Section;
use App\Models\YearLevel;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SectionController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $programId = filter_var($request->query('program_id'), FILTER_VALIDATE_INT);
        $yearLevelNumber = filter_var($request->query('year_level'), FILTER_VALIDATE_INT);
        $requestedStatus = $request->query('status');
        $status = in_array($requestedStatus, ['active', 'inactive'], true) ? $requestedStatus : '';

        $programs = Program::query()->orderBy('name')->get(['id', 'code', 'name']);
        $yearLevels = YearLevel::query()->orderBy('level_number')->get(['level_number', 'name']);

        $sections = Section::with(['program', 'yearLevel'])
            ->withCount('students')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('code', 'like', '%'.$search.'%')
                        ->orWhereHas('program', fn ($programQuery) => $programQuery
                            ->where('code', 'like', '%'.$search.'%')
                            ->orWhere('name', 'like', '%'.$search.'%'))
                        ->orWhereHas('yearLevel', fn ($yearLevelQuery) => $yearLevelQuery
                            ->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->when($programId !== false, fn ($query) => $query->where('program_id', $programId))
            ->when($yearLevelNumber !== false, fn ($query) => $query->where('year_level', $yearLevelNumber))
            ->when($status !== '', fn ($query) => $query->where('is_active', $status === 'active'))
            ->orderBy('year_level')
            ->orderBy('code')
            ->paginate(10)
            ->withQueryString();

        return view('admin.sections.index', compact(
            'sections',
            'search',
            'programs',
            'yearLevels',
            'programId',
            'yearLevelNumber',
            'status',
        ));
    }

    public function create(): View
    {
        $programs = Program::where('is_active', true)->orderBy('name')->get();
        $yearLevels = YearLevel::where('is_active', true)->orderBy('level_number')->get();

        return view('admin.sections.create', compact('programs', 'yearLevels'));
    }

    public function store(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($request->user()->isAdministrator(), 403);

        $data = $request->validate([
            'program_id' => ['required', 'integer', Rule::exists('programs', 'id')->where('is_active', true)],
            'year_level' => ['required', 'integer', Rule::exists('year_levels', 'level_number')->where('is_active', true)],
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('sections', 'code')
                    ->where('program_id', $request->input('program_id'))
                    ->where('year_level', $request->input('year_level')),
            ],
        ]);

        $section = Section::create([...$data, 'is_active' => true]);

        $auditLogger->record(
            $request->user(),
            'section.created',
            $section,
            'Created section '.$section->code.'.',
            $request,
        );

        return redirect()->route('admin.sections.index')->with('success', 'Section created successfully.');
    }

    public function edit(Section $section): View
    {
        $programs = Program::where('is_active', true)
            ->orWhereKey($section->program_id)
            ->orderBy('name')
            ->get();

        $yearLevels = YearLevel::where('is_active', true)
            ->orWhere('level_number', $section->year_level)
            ->orderBy('level_number')
            ->get();

        return view('admin.sections.edit', compact('section', 'programs', 'yearLevels'));
    }

    public function update(Request $request, Section $section, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($request->user()->isAdministrator(), 403);

        $data = $request->validate([
            'program_id' => ['required', 'integer', Rule::exists('programs', 'id')->where(
                fn ($query) => $query->where('is_active', true)->orWhere('id', $section->program_id)
            )],
            'year_level' => ['required', 'integer', Rule::exists('year_levels', 'level_number')->where(
                fn ($query) => $query->where('is_active', true)->orWhere('level_number', $section->year_level)
            )],
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('sections', 'code')
                    ->where('program_id', $request->input('program_id'))
                    ->where('year_level', $request->input('year_level'))
                    ->ignore($section->id),
            ],
            'is_active' => ['required', 'boolean'],
        ]);

        $section->update($data);

        $auditLogger->record(
            $request->user(),
            'section.updated',
            $section,
            'Updated section '.$section->code.'.',
            $request,
        );

        return redirect()->route('admin.sections.index')->with('success', 'Section updated successfully.');
    }

    public function destroy(Request $request, Section $section, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($request->user()->isAdministrator(), 403);

        $section->update(['is_active' => false]);

        $auditLogger->record(
            $request->user(),
            'section.deactivated',
            $section,
            'Deactivated section '.$section->code.'.',
            $request,
        );

        return redirect()->route('admin.sections.index')->with('success', 'Section deactivated. Existing student assignments were preserved.');
    }
}
