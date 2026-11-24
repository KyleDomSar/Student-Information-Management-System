@extends('layouts.portal')

@section('title', 'Year Levels')
@section('topbar-title', 'Year level management')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div>
        <div class="eyebrow mb-1">ACADEMIC SETUP</div>
        <h1 class="page-title mb-1">Year Levels</h1>
        <p class="muted mb-1">Maintain the year levels used by student records and sections.</p>
        <p class="muted small mb-0">Year levels are deactivated instead of permanently deleted so existing student and section history stays intact.</p>
    </div>
    @if(auth()->user()->isAdministrator())
        <a href="{{ route('admin.year-levels.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add year level</a>
    @endif
</div>
<div class="surface p-3 mb-3">
    <form method="GET" action="{{ route('admin.year-levels.index') }}" class="row g-2 align-items-center">
        <div class="col-md">
            <label for="year-level-search" class="visually-hidden">Search year levels</label>
            <input id="year-level-search" type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search by year level number or name">
        </div>
        <div class="col-sm-5 col-md-3">
            <label for="year-level-status" class="visually-hidden">Filter by status</label>
            <select id="year-level-status" name="status" class="form-select">
                <option value="">All statuses</option>
                <option value="active" @selected($status === 'active')>Active</option>
                <option value="inactive" @selected($status === 'inactive')>Inactive</option>
            </select>
        </div>
        <div class="col-auto d-grid"><button class="btn btn-outline-primary" type="submit"><i class="bi bi-search me-1"></i> Search</button></div>
        @if($search !== '' || $status !== '')
            <div class="col-auto d-grid"><a href="{{ route('admin.year-levels.index') }}" class="btn btn-light">Clear filters</a></div>
        @endif
    </form>
</div>
<div class="surface overflow-hidden">
    <div class="p-3 p-md-4 border-bottom d-flex justify-content-between align-items-center">
        <div><h2 class="h5 fw-bold mb-1">Year level directory</h2><p class="muted small mb-0">{{ $yearLevels->total() }} year level(s)</p></div>
        <span class="badge badge-soft rounded-pill">{{ auth()->user()->isAdministrator() ? 'Administrator' : 'Staff · View only' }}</span>
    </div>
    @if($yearLevels->isEmpty())
        <div class="p-5 text-center">
            <h2 class="h6 fw-bold">{{ $search !== '' || $status !== '' ? 'No matching year levels found' : 'No year levels configured' }}</h2>
            <p class="muted small mb-3">{{ $search !== '' || $status !== '' ? 'Try another search term or clear the filters.' : 'Run the database seeder to add the default year levels.' }}</p>
            @if($search !== '' || $status !== '')<a href="{{ route('admin.year-levels.index') }}" class="btn btn-light btn-sm">Clear filters</a>@endif
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Level</th><th>Name</th><th>Students</th><th>Sections</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                @foreach($yearLevels as $yearLevel)
                    <tr>
                        <td class="fw-semibold">{{ $yearLevel->level_number }}</td>
                        <td>{{ $yearLevel->name }}</td>
                        <td>{{ number_format($yearLevel->students_count) }}</td>
                        <td>{{ number_format($yearLevel->sections_count) }}</td>
                        <td><span class="badge {{ $yearLevel->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $yearLevel->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="text-end text-nowrap">
                            @if(auth()->user()->isAdministrator())
                                <a href="{{ route('admin.year-levels.edit', $yearLevel) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                @if($yearLevel->is_active)
                                    <form method="POST" action="{{ route('admin.year-levels.destroy', $yearLevel) }}" class="d-inline" onsubmit="return confirm('Deactivate this year level? Existing student and section records will be preserved.');">
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
        <div class="p-3">{{ $yearLevels->links() }}</div>
    @endif
</div>
@endsection
