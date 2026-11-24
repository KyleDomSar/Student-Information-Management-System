<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\Department;
use App\Models\Program;
use App\Models\ProgramMajor;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AcademicStructureController extends Controller
{
    public function index(): View
    {
        $colleges = College::withCount('departments')->with('departments')->orderBy('name')->get();
        $departments = Department::with('college')->withCount('programs')->orderBy('name')->get();
        $programs = Program::with(['college', 'department'])->orderBy('name')->get();
        $majors = ProgramMajor::with('program')->orderBy('name')->get();

        return view('admin.academic-structure.index', compact('colleges', 'departments', 'programs', 'majors'));
    }

    public function storeCollege(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($request->user()->isAdministrator(), 403);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('colleges', 'name')->where('campus', $request->input('campus', 'Main Campus')),
            ],
            'campus' => ['required', 'string', 'max:100'],
        ]);

        $college = College::create([...$data, 'is_active' => true]);
        $auditLogger->record($request->user(), 'college.created', $college, 'Created college '.$college->name.'.', $request);

        return back()->with('success', 'College added successfully.');
    }

    public function storeDepartment(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($request->user()->isAdministrator(), 403);

        $data = $request->validate([
            'college_id' => ['required', 'integer', Rule::exists('colleges', 'id')->where('is_active', true)],
            'code' => ['required', 'string', 'max:30', Rule::unique('departments', 'code')],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('departments', 'name')->where('college_id', $request->input('college_id')),
            ],
        ]);

        $department = Department::create([...$data, 'is_active' => true]);
        $auditLogger->record($request->user(), 'department.created', $department, 'Created department '.$department->code.'.', $request);

        return back()->with('success', 'Department added successfully.');
    }

    public function updateCollegeStatus(Request $request, College $college, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($request->user()->isAdministrator(), 403);
        $data = $request->validate(['is_active' => ['required', 'boolean']]);
        $college->update($data);
        $auditLogger->record($request->user(), 'college.status_updated', $college, 'Updated college status for '.$college->name.'.', $request);

        return back()->with('success', 'College status updated. Related records were preserved.');
    }

    public function updateDepartmentStatus(Request $request, Department $department, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($request->user()->isAdministrator(), 403);
        $data = $request->validate(['is_active' => ['required', 'boolean']]);
        $department->update($data);
        $auditLogger->record($request->user(), 'department.status_updated', $department, 'Updated department status for '.$department->code.'.', $request);

        return back()->with('success', 'Department status updated. Related programs were preserved.');
    }

    public function updateMajorStatus(Request $request, ProgramMajor $major, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($request->user()->isAdministrator(), 403);
        $data = $request->validate(['is_active' => ['required', 'boolean']]);
        $major->update($data);
        $auditLogger->record($request->user(), 'program_major.status_updated', $major, 'Updated major status for '.$major->name.'.', $request);

        return back()->with('success', 'Program major status updated. Existing student references were preserved.');
    }
}
