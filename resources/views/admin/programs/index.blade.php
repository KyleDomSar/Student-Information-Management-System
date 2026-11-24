@extends('layouts.portal')

@section('title', 'Programs / Courses')
@section('topbar-title', 'Programs / Courses')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div>
        <div class="eyebrow mb-1">ACADEMIC SETUP</div>
        <h1 class="page-title mb-1">Programs / Courses</h1>
        <p class="muted mb-0">Maintain the programs assigned to student records.</p>
    </div>
    @if(auth()->user()->isAdministrator())
        <a href="{{ route('admin.programs.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add program</a>
    @endif
</div>
<div class="surface p-3 mb-3">
    <form method="GET" action="{{ route('admin.programs.index') }}" class="row g-2 align-items-center">
        <div class="col-md">
            <label for="program-search" class="visually-hidden">Search programs</label>
            <input id="program-search" type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search by program code, name, or description">
        </div>
        <div class="col-sm-5 col-md-3">
            <label for="program-status" class="visually-hidden">Filter by status</label>
            <select id="program-status" name="status" class="form-select">
                <option value="">All statuses</option>
                <option value="active" @selected($status === 'active')>Active</option>
                <option value="inactive" @selected($status === 'inactive')>Inactive</option>
            </select>
        </div>
        <div class="col-auto d-grid"><button class="btn btn-outline-primary" type="submit"><i class="bi bi-search me-1"></i> Search</button></div>
        @if($search !== '' || $status !== '')
            <div class="col-auto d-grid"><a href="{{ route('admin.programs.index') }}" class="btn btn-light">Clear filters</a></div>
        @endif
    </form>
</div>
<div class="surface overflow-hidden">
    <div class="p-3 p-md-4 border-bottom d-flex justify-content-between align-items-center">
        <div><h2 class="h5 fw-bold mb-1">Program directory</h2><p class="muted small mb-0">{{ $programs->total() }} program(s)</p></div>
        <span class="badge badge-soft rounded-pill">{{ auth()->user()->isAdministrator() ? 'Administrator' : 'Staff · View only' }}</span>
    </div>
    @if($programs->isEmpty())
        <div class="text-center p-5">
            <div class="stat-icon mx-auto mb-3"><i class="bi bi-book"></i></div>
            <h2 class="h6 fw-bold">{{ $search !== '' || $status !== '' ? 'No matching programs found' : 'No programs added yet' }}</h2>
            <p class="muted small mb-3">{{ $search !== '' || $status !== '' ? 'Try a different search term or clear the filters.' : 'Add the programs used by the department.' }}</p>
            @if($search !== '' || $status !== '')
                <a href="{{ route('admin.programs.index') }}" class="btn btn-light btn-sm">Clear filters</a>
            @elseif(auth()->user()->isAdministrator())
                <a href="{{ route('admin.programs.create') }}" class="btn btn-primary btn-sm">Add program</a>
            @endif
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Program code</th><th>Program / Course</th><th>Students</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                @foreach($programs as $program)
                    <tr>
                        <td class="fw-semibold">{{ $program->code }}</td><td>{{ $program->name }}</td><td>{{ number_format($program->students_count) }}</td>
                        <td><span class="badge {{ $program->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $program->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="text-end text-nowrap">
                            @if(auth()->user()->isAdministrator())
                                <a href="{{ route('admin.programs.edit', $program) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                @if($program->is_active)
                                    <form method="POST" action="{{ route('admin.programs.destroy', $program) }}" class="d-inline" onsubmit="return confirm('Deactivate this program? Existing records will be preserved.');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Deactivate</button>
                                    </form>
                                @endif
                            @else
                                <span class="small muted">View only</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $programs->links() }}</div>
    @endif
</div>
@endsection
