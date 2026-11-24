<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\Section;
use App\Models\Student;
use App\Models\YearLevel;
use App\Support\AuditLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentReportController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->validatedFilters($request);
        $students = $this->studentQuery($filters)
            ->paginate(20)
            ->withQueryString();

        $programs = Program::orderBy('name')->get();
        $yearLevels = YearLevel::orderBy('level_number')->get();
        $sections = Section::with('program', 'yearLevel')->orderBy('code')->get();

        return view('admin.reports.students', compact(
            'students',
            'filters',
            'programs',
            'yearLevels',
            'sections',
        ));
    }

    public function export(Request $request, AuditLogger $auditLogger): StreamedResponse
    {
        $filters = $this->validatedFilters($request);
        $students = $this->studentQuery($filters)->get();

        $auditLogger->record(
            $request->user(),
            'student.report_exported',
            null,
            'Exported a student information report containing '.$students->count().' record(s).',
            $request,
        );

        return response()->streamDownload(function () use ($students): void {
            $output = fopen('php://output', 'w');

            if ($output === false) {
                return;
            }

            // UTF-8 BOM improves compatibility with spreadsheet applications.
            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, [
                'Student Number',
                'Full Name',
                'Email',
                'Program',
                'Year Level',
                'Section',
                'Student Status',
                'Portal Account Status',
            ]);

            foreach ($students as $student) {
                $values = [
                    $student->student_number,
                    $student->user?->name ?? '',
                    $student->user?->email ?? '',
                    $student->program?->code ?? '',
                    $student->yearLevelRecord?->name ?? $student->year_level ?? '',
                    $student->section?->code ?? '',
                    $student->status,
                    $student->user?->is_active ? 'Active' : 'Inactive',
                ];

                fputcsv($output, array_map(
                    fn ($value) => $this->safeCsvValue((string) $value),
                    $values,
                ));
            }

            fclose($output);
        }, 'student-information-report-'.now()->format('Ymd-His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'program_id' => ['nullable', 'integer', Rule::exists('programs', 'id')],
            'year_level' => ['nullable', 'integer', Rule::exists('year_levels', 'level_number')],
            'section_id' => ['nullable', 'integer', Rule::exists('sections', 'id')],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'graduated'])],
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function studentQuery(array $filters): Builder
    {
        return Student::query()
            ->with(['user', 'program', 'section', 'yearLevelRecord'])
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $studentQuery) use ($search): void {
                    $studentQuery
                        ->where('student_number', 'like', '%'.$search.'%')
                        ->orWhereHas('user', function (Builder $userQuery) use ($search): void {
                            $userQuery
                                ->where('name', 'like', '%'.$search.'%')
                                ->orWhere('email', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when($filters['program_id'] ?? null, fn (Builder $query, int $programId) => $query->where('program_id', $programId))
            ->when($filters['year_level'] ?? null, fn (Builder $query, int $yearLevel) => $query->where('year_level', $yearLevel))
            ->when($filters['section_id'] ?? null, fn (Builder $query, int $sectionId) => $query->where('section_id', $sectionId))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->orderBy('student_number');
    }

    private function safeCsvValue(string $value): string
    {
        // Prevent spreadsheet formula execution for user-controlled text cells.
        if (preg_match('/^[\s]*[=+@-]/u', $value) === 1) {
            return "'".$value;
        }

        return $value;
    }
}
