@extends('layouts.portal')

@section('title', 'Sections')
@section('topbar-title', 'Section management')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div>
        <div class="eyebrow mb-1">ACADEMIC SETUP</div>
        <h1 class="page-title mb-1">Sections</h1>
        <p class="muted mb-0">Manage sections by academic program and year level.</p>
    </div>
    @if(auth()->user()->isAdministrator())
        <a href="{{ route('admin.sections.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add section</a>
    @endif
</div>
<div class="surface p-3 mb-3">
    <form method="GET" action="{{ route('admin.sections.index') }}" class="row g-2">
        <div class="col-md"><input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search section or program"></div>
        <div class="col-auto d-grid"><button class="btn btn-outline-primary" type="submit"><i class="bi bi-search me-1"></i> Search</button></div>
        @if($search !== '')<div class="col-auto d-grid"><a href="{{ route('admin.sections.index') }}" class="btn btn-light">Clear</a></div>@endif
    </form>
</div>
<div class="surface overflow-hidden">
    <div class="p-3 p-md-4 border-bottom d-flex justify-content-between align-items-center">
        <div><h2 class="h5 fw-bold mb-1">Section directory</h2><p class="muted small mb-0">{{ $sections->total() }} section(s)</p></div>
        <span class="badge badge-soft rounded-pill">{{ auth()->user()->isAdministrator() ? 'Administrator' : 'Staff · View only' }}</span>
    </div>
    @if($sections->isEmpty())
        <div class="p-5 text-center"><h2 class="h6 fw-bold">No sections found</h2><p class="muted small mb-0">{{ $search !== '' ? 'Try another search term.' : 'Add a section after configuring programs and year levels.' }}</p></div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Program</th><th>Year level</th><th>Section</th><th>Students assigned</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                @foreach($sections as $section)
                    <tr>
                        <td><div class="fw-semibold">{{ $section->program->code }}</div><div class="small muted">{{ $section->program->name }}</div></td>
                        <td>{{ $section->yearLevel->name }}</td>
                        <td class="fw-semibold">{{ $section->code }}</td>
                        <td>{{ number_format($section->students_count) }}</td>
                        <td><span class="badge {{ $section->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $section->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="text-end text-nowrap">
                            @if(auth()->user()->isAdministrator())
                                <a href="{{ route('admin.sections.edit', $section) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                @if($section->is_active)
                                    <form method="POST" action="{{ route('admin.sections.destroy', $section) }}" class="d-inline" onsubmit="return confirm('Deactivate this section? Existing student assignments will be preserved.');">
                                        @csrf
                                        @method('DELETE')
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
        <div class="p-3">{{ $sections->links() }}</div>
    @endif
</div>
@endsection
