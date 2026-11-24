@extends('layouts.portal')

@section('title', 'Student Reports')
@section('topbar-title', 'Student information reports')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div>
        <div class="eyebrow mb-1">REPORTING</div>
        <h1 class="page-title mb-1">Student Information Report</h1>
        <p class="muted mb-0">Search and generate authorized student lists using academic and account details.</p>
    </div>
    <a href="{{ route('admin.reports.students.export', $filters) }}" class="btn btn-success">
        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
    </a>
</div>

<div class="surface p-3 p-md-4 mb-3">
    <form method="GET" action="{{ route('admin.reports.students') }}" class="row g-3">
        <div class="col-lg-4 col-md-6">
            <label for="search" class="form-label small fw-semibold">Search student</label>
            <input id="search" type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Student number, name, or email">
        </div>
        <div class="col-lg-2 col-md-6">
            <label for="college_id" class="form-label small fw-semibold">College</label>
            <select id="college_id" name="college_id" class="form-select">
                <option value="">All colleges</option>
                @foreach($colleges as $college)
                    <option value="{{ $college->id }}" @selected((string) ($filters['college_id'] ?? '') === (string) $college->id)>{{ $college->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-2 col-md-6">
            <label for="program_id" class="form-label small fw-semibold">Program</label>
            <select id="program_id" name="program_id" class="form-select">
                <option value="">All programs</option>
                @foreach($programs as $program)
                    <option value="{{ $program->id }}" @selected((string) ($filters['program_id'] ?? '') === (string) $program->id)>{{ $program->code }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-2 col-md-6">
            <label for="year_level" class="form-label small fw-semibold">Year level</label>
            <select id="year_level" name="year_level" class="form-select">
                <option value="">All year levels</option>
                @foreach($yearLevels as $yearLevel)
                    <option value="{{ $yearLevel->level_number }}" @selected((string) ($filters['year_level'] ?? '') === (string) $yearLevel->level_number)>{{ $yearLevel->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-2 col-md-6">
            <label for="section_id" class="form-label small fw-semibold">Section</label>
            <select id="section_id" name="section_id" class="form-select">
                <option value="">All sections</option>
                @foreach($sections as $section)
                    <option value="{{ $section->id }}" @selected((string) ($filters['section_id'] ?? '') === (string) $section->id)>{{ $section->program->code }} · {{ $section->yearLevel->name }} · {{ $section->code }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-2 col-md-6">
            <label for="status" class="form-label small fw-semibold">Student status</label>
            <select id="status" name="status" class="form-select">
                <option value="">All statuses</option>
                @foreach(['active' => 'Active', 'inactive' => 'Inactive', 'graduated' => 'Graduated'] as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i> Apply filters</button>
            <a href="{{ route('admin.reports.students') }}" class="btn btn-light">Clear filters</a>
        </div>
    </form>
</div>

<div class="surface overflow-hidden">
    <div class="p-3 p-md-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h2 class="h5 fw-bold mb-1">Student list</h2>
            <p class="muted small mb-0">{{ number_format($students->total()) }} matching student record(s)</p>
        </div>
        <span class="badge badge-soft rounded-pill">Administrator / Staff</span>
    </div>
    @if($students->isEmpty())
        <div class="p-5 text-center">
            <div class="stat-icon mx-auto mb-3"><i class="bi bi-file-earmark-text"></i></div>
            <h2 class="h6 fw-bold">No matching students</h2>
            <p class="muted small mb-0">Adjust the filters and try again.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Student</th><th>Student number</th><th>College</th><th>Department</th><th>Program</th><th>Major</th><th>Year</th><th>Section</th><th>Student status</th><th>Portal account</th></tr>
                </thead>
                <tbody>
                @foreach($students as $student)
                    <tr>
                        <td><div class="fw-semibold">{{ $student->user?->name ?? 'Account unavailable' }}</div><div class="small muted">{{ $student->user?->email }}</div></td>
                        <td class="fw-semibold">{{ $student->student_number }}</td>
                        <td>{{ $student->program?->college?->name ?? 'Not assigned' }}</td>
                        <td>{{ $student->program?->department?->name ?? 'Not assigned' }}</td>
                        <td>{{ $student->program?->code ?? 'Not assigned' }}</td>
                        <td>{{ $student->major?->name ?? '—' }}</td>
                        <td>{{ $student->yearLevelRecord?->name ?? 'Not assigned' }}</td>
                        <td>{{ $student->section?->code ?? 'Not assigned' }}</td>
                        <td><span class="badge text-bg-light text-capitalize">{{ $student->status }}</span></td>
                        <td><span class="badge {{ $student->user?->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $student->user?->is_active ? 'Active' : 'Inactive' }}</span></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $students->links() }}</div>
    @endif
</div>
@endsection
