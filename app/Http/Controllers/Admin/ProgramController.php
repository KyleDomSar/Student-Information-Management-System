<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\Department;
use App\Models\Program;
use App\Models\ProgramMajor;
use App\Models\Section;
use App\Models\Student;
use App\Models\YearLevel;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProgramController extends Controller
{
    public function index(): View
    {
        $programs = Program::with(['college', 'department'])
            ->withCount(['students', 'subjects', 'majors', 'yearLevels'])
            ->orderBy('name')
            ->paginate(10);

        return view('admin.programs.index', compact('programs'));
    }

    public function create(): View
    {
        return view('admin.programs.create', $this->formData());
    }

    public function store(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $this->validatedData($request);
        $program = DB::transaction(function () use ($data) {
            $yearLevels = $data['year_levels'];
            $majorNames = $this->majorNames($data['majors'] ?? '');
            unset($data['year_levels'], $data['majors']);

            $program = Program::create([...$data, 'is_active' => true]);
            $this->syncYearLevels($program, $yearLevels);
            $this->storeMajors($program, $majorNames);

            return $program;
        });

        $auditLogger->record(
            $request->user(),
            'program.created',
            $program,
            'Created academic program '.$program->code.'.',
            $request,
        );

        return redirect()
            ->route('admin.programs.index')
            ->with('success', 'Academic program created successfully.');
    }

    public function edit(Program $program): View
    {
        return view('admin.programs.edit', $this->formData($program) + ['program' => $program]);
    }

    public function update(Request $request, Program $program, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $this->validatedData($request, $program);
        DB::transaction(function () use ($data, $program) {
            $yearLevels = $data['year_levels'];
            $majorNames = $this->majorNames($data['majors'] ?? '');
            unset($data['year_levels'], $data['majors']);

            $program->update($data);
            $program->yearLevels()->sync($yearLevels);
            $this->storeMajors($program, $majorNames);
        });

        $auditLogger->record(
            $request->user(),
            'program.updated',
            $program,
            'Updated academic program '.$program->code.'.',
            $request,
        );

        return redirect()
            ->route('admin.programs.index')
            ->with('success', 'Academic program updated successfully.');
    }

    public function destroy(Request $request, Program $program, AuditLogger $auditLogger): RedirectResponse
    {
        $program->update(['is_active' => false]);

        $auditLogger->record(
            $request->user(),
            'program.deactivated',
            $program,
            'Deactivated academic program '.$program->code.'.',
            $request,
        );

        return redirect()
            ->route('admin.programs.index')
            ->with('success', 'Program deactivated. Existing student and subject references were preserved.');
    }

    private function formData(?Program $program = null): array
    {
        $colleges = College::query()
            ->where('is_active', true)
            ->when($program, fn ($query) => $query->orWhereKey($program->college_id))
            ->orderBy('name')
            ->get();

        $departments = Department::query()
            ->where('is_active', true)
            ->when($program, fn ($query) => $query->orWhereKey($program->department_id))
            ->orderBy('name')
            ->get();

        $yearLevels = YearLevel::query()
            ->where('is_active', true)
            ->when($program, fn ($query) => $query->orWhereIn(
                'level_number',
                $program->yearLevels()->pluck('year_levels.level_number'),
            ))
            ->orderBy('level_number')
            ->get();

        $selectedYearLevels = $program
            ? $program->yearLevels()->pluck('year_levels.level_number')->map(fn ($value) => (int) $value)->all()
            : [];

        $majorText = $program
            ? $program->majors()->where('is_active', true)->orderBy('name')->pluck('name')->implode("\n")
            : '';

        return compact('colleges', 'departments', 'yearLevels', 'selectedYearLevels', 'majorText');
    }

    private function validatedData(Request $request, ?Program $program = null): array
    {
        return $request->validate([
            'college_id' => [
                'required',
                'integer',
                Rule::exists('colleges', 'id')->where(
                    fn ($query) => $query->where('is_active', true)->when(
                        $program,
                        fn ($nested) => $nested->orWhere('id', $program->college_id),
                    ),
                ),
            ],
            'department_id' => [
                'nullable',
                'integer',
                Rule::exists('departments', 'id')->where(
                    fn ($query) => $query
                        ->where('college_id', $request->input('college_id'))
                        ->where(fn ($nested) => $nested->where('is_active', true)->when(
                            $program,
                            fn ($active) => $active->orWhere('id', $program->department_id),
                        )),
                ),
            ],
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('programs', 'code')->ignore($program?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => [$program ? 'required' : 'nullable', 'boolean'],
            'year_levels' => ['required', 'array', 'min:1'],
            'year_levels.*' => [
                'required',
                'integer',
                Rule::exists('year_levels', 'level_number')->where(fn ($query) => $query
                    ->where('is_active', true)
                    ->when($program, fn ($active) => $active->orWhereIn(
                        'level_number',
                        $program->yearLevels()->pluck('year_levels.level_number'),
                    ))),
            ],
            'majors' => ['nullable', 'string', 'max:5000'],
        ]);
    }

    private function majorNames(string $input): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $input))
            ->map(fn ($name) => trim($name))
            ->filter()
            ->unique(fn ($name) => mb_strtolower($name))
            ->values()
            ->all();
    }

    private function syncYearLevels(Program $program, array $yearLevels): void
    {
        $existing = $program->yearLevels()->pluck('year_levels.level_number')->map(fn ($level) => (int) $level);
        $removed = $existing->diff(array_map('intval', $yearLevels));

        if ($removed->isNotEmpty()) {
            $hasAssignments = Section::where('program_id', $program->id)
                ->whereIn('year_level', $removed)
                ->exists()
                || Student::where('program_id', $program->id)
                    ->whereIn('year_level', $removed)
                    ->exists();

            if ($hasAssignments) {
                throw ValidationException::withMessages([
                    'year_levels' => 'A year level cannot be removed while students or sections are assigned to it.',
                ]);
            }
        }

        $program->yearLevels()->sync($yearLevels);
    }

    private function storeMajors(Program $program, array $majorNames): void
    {
        $program->majors()
            ->when(
                $majorNames !== [],
                fn ($query) => $query->whereNotIn('name', $majorNames),
            )
            ->update(['is_active' => false]);

        foreach ($majorNames as $name) {
            ProgramMajor::updateOrCreate(
                ['program_id' => $program->id, 'name' => $name],
                ['is_active' => true],
            );
        }
    }
}
