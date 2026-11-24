@extends('layouts.portal')

@section('title', 'Academic Structure')
@section('topbar-title', 'CvSU academic structure')

@section('content')
<div class="mb-4">
    <div class="eyebrow mb-1">ACADEMIC SETUP · MAIN CAMPUS</div>
    <h1 class="page-title mb-1">Academic structure</h1>
    <p class="muted mb-0">Manage colleges, departments, programs, majors, and curriculum year levels.</p>
</div>

@if(auth()->user()->isAdministrator())
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <form method="POST" action="{{ route('admin.academic-structure.colleges.store') }}" class="surface p-3 p-md-4 h-100">
                @csrf
                <h2 class="h6 fw-bold mb-3">Add college</h2>
                <label class="form-label small fw-semibold" for="college_name">College name *</label>
                <input id="college_name" name="name" class="form-control mb-3" maxlength="255" required>
                <label class="form-label small fw-semibold" for="college_campus">Campus *</label>
                <input id="college_campus" name="campus" class="form-control mb-3" maxlength="100" value="Main Campus" required>
                <button class="btn btn-primary btn-sm">Add college</button>
            </form>
        </div>
        <div class="col-lg-6">
            <form method="POST" action="{{ route('admin.academic-structure.departments.store') }}" class="surface p-3 p-md-4 h-100">
                @csrf
                <h2 class="h6 fw-bold mb-3">Add department</h2>
                <label class="form-label small fw-semibold" for="department_college_id">College *</label>
                <select id="department_college_id" name="college_id" class="form-select mb-3" required>
                    <option value="">Select college</option>
                    @foreach($colleges->where('is_active', true) as $college)
                        <option value="{{ $college->id }}">{{ $college->name }}</option>
                    @endforeach
                </select>
                <div class="row g-2">
                    <div class="col-sm-4">
                        <label class="form-label small fw-semibold" for="department_code">Code *</label>
                        <input id="department_code" name="code" class="form-control mb-3" maxlength="30" placeholder="e.g. DIT" required>
                    </div>
                    <div class="col-sm-8">
                        <label class="form-label small fw-semibold" for="department_name">Department name *</label>
                        <input id="department_name" name="name" class="form-control mb-3" maxlength="255" required>
                    </div>
                </div>
                <button class="btn btn-primary btn-sm">Add department</button>
            </form>
        </div>
    </div>
@endif

<div class="surface overflow-hidden mb-4">
    <div class="p-3 border-bottom"><h2 class="h6 fw-bold mb-0">Colleges</h2></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>College</th><th>Campus</th><th>Departments</th><th>Status</th>@if(auth()->user()->isAdministrator())<th class="text-end">Action</th>@endif</tr></thead>
            <tbody>
            @forelse($colleges as $college)
                <tr>
                    <td class="fw-semibold">{{ $college->name }}</td><td>{{ $college->campus }}</td><td>{{ $college->departments_count }}</td>
                    <td><span class="badge {{ $college->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $college->is_active ? 'Active' : 'Inactive' }}</span></td>
                    @if(auth()->user()->isAdministrator())
                        <td class="text-end">
                            <form method="POST" action="{{ route('admin.academic-structure.colleges.status', $college) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="is_active" value="{{ $college->is_active ? 0 : 1 }}">
                                <button class="btn btn-sm btn-outline-secondary">{{ $college->is_active ? 'Deactivate' : 'Activate' }}</button>
                            </form>
                        </td>
                    @endif
                </tr>
            @empty
                <tr><td colspan="5" class="text-center muted p-4">No colleges found. Run the database seeder to load the CvSU Main Campus list.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="surface overflow-hidden mb-4">
    <div class="p-3 border-bottom"><h2 class="h6 fw-bold mb-0">Departments</h2></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Code</th><th>Department</th><th>College</th><th>Programs</th><th>Status</th>@if(auth()->user()->isAdministrator())<th class="text-end">Action</th>@endif</tr></thead>
            <tbody>
            @forelse($departments as $department)
                <tr>
                    <td class="fw-semibold">{{ $department->code }}</td><td>{{ $department->name }}</td><td>{{ $department->college->name }}</td><td>{{ $department->programs_count }}</td>
                    <td><span class="badge {{ $department->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $department->is_active ? 'Active' : 'Inactive' }}</span></td>
                    @if(auth()->user()->isAdministrator())
                        <td class="text-end">
                            <form method="POST" action="{{ route('admin.academic-structure.departments.status', $department) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="is_active" value="{{ $department->is_active ? 0 : 1 }}">
                                <button class="btn btn-sm btn-outline-secondary">{{ $department->is_active ? 'Deactivate' : 'Activate' }}</button>
                            </form>
                        </td>
                    @endif
                </tr>
            @empty
                <tr><td colspan="6" class="text-center muted p-4">No departments configured yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="surface overflow-hidden mb-4">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center"><h2 class="h6 fw-bold mb-0">Programs</h2><a href="{{ route('admin.programs.index') }}" class="small text-decoration-none">Open program directory</a></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Program</th><th>College</th><th>Department</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($programs as $program)
                <tr><td><span class="fw-semibold">{{ $program->code }}</span><div class="small muted">{{ $program->name }}</div></td><td>{{ $program->college?->name ?? 'Not assigned' }}</td><td>{{ $program->department?->code ?? 'Not assigned' }}</td><td><span class="badge {{ $program->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $program->is_active ? 'Active' : 'Inactive' }}</span></td></tr>
            @empty
                <tr><td colspan="4" class="text-center muted p-4">No programs configured yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="surface overflow-hidden">
    <div class="p-3 border-bottom"><h2 class="h6 fw-bold mb-0">Program majors and specializations</h2></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Major / specialization</th><th>Program</th><th>Status</th>@if(auth()->user()->isAdministrator())<th class="text-end">Action</th>@endif</tr></thead>
            <tbody>
            @forelse($majors as $major)
                <tr><td class="fw-semibold">{{ $major->name }}</td><td>{{ $major->program->code }} · {{ $major->program->name }}</td><td><span class="badge {{ $major->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $major->is_active ? 'Active' : 'Inactive' }}</span></td>
                    @if(auth()->user()->isAdministrator())
                        <td class="text-end">
                            <form method="POST" action="{{ route('admin.academic-structure.majors.status', $major) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="is_active" value="{{ $major->is_active ? 0 : 1 }}">
                                <button class="btn btn-sm btn-outline-secondary">{{ $major->is_active ? 'Deactivate' : 'Activate' }}</button>
                            </form>
                        </td>
                    @endif
                </tr>
            @empty
                <tr><td colspan="4" class="text-center muted p-4">No majors configured. Add them in the program create or edit page.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger mt-3"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
@endsection
