@extends('layouts.portal')

@section('title', 'Subjects')
@section('topbar-title', 'Subject catalog')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div>
        <div class="eyebrow mb-1">ACADEMIC SETUP</div>
        <h1 class="page-title mb-1">Subjects</h1>
        <p class="muted mb-0">Maintain the subject catalog used for student enrollment.</p>
    </div>
    <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add subject</a>
</div>

<div class="surface p-3 mb-3">
    <form method="GET" action="{{ route('admin.subjects.index') }}" class="row g-2">
        <div class="col-md">
            <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search by subject code or title">
        </div>
        <div class="col-auto d-grid"><button class="btn btn-outline-primary" type="submit"><i class="bi bi-search me-1"></i> Search</button></div>
        @if($search !== '')<div class="col-auto d-grid"><a href="{{ route('admin.subjects.index') }}" class="btn btn-light">Clear</a></div>@endif
    </form>
</div>

<div class="surface overflow-hidden">
    <div class="p-3 p-md-4 border-bottom d-flex justify-content-between align-items-center">
        <div><h2 class="h5 fw-bold mb-1">Subject directory</h2><p class="muted small mb-0">{{ $subjects->total() }} subject(s) found</p></div>
        <span class="badge badge-soft rounded-pill">Admin / Staff</span>
    </div>
    @if($subjects->isEmpty())
        <div class="text-center p-5">
            <div class="stat-icon mx-auto mb-3"><i class="bi bi-book"></i></div>
            <h2 class="h6 fw-bold">No subjects found</h2>
            <p class="muted small mb-3">{{ $search !== '' ? 'Try another search term.' : 'Add subjects to build your academic catalog.' }}</p>
            @if($search === '')<a href="{{ route('admin.subjects.create') }}" class="btn btn-primary btn-sm">Add subject</a>@endif
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Code</th><th>Subject title</th><th>Program</th><th>Units</th><th>Enrollments</th><th>Status</th><th class="text-end">Action</th></tr></thead>
                <tbody>
                @foreach($subjects as $subject)
                    <tr>
                        <td class="fw-semibold">{{ $subject->code }}</td>
                        <td>{{ $subject->title }}</td>
                        <td>{{ $subject->program?->code ?? 'General' }}</td>
                        <td>{{ $subject->units }}</td>
                        <td>{{ number_format($subject->enrollment_subjects_count) }}</td>
                        <td><span class="badge {{ $subject->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $subject->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            @if($subject->is_active)
                                <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}" class="d-inline" onsubmit="return confirm('Deactivate this subject? Existing enrollment history will be preserved.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Deactivate</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $subjects->links() }}</div>
    @endif
</div>
@endsection
