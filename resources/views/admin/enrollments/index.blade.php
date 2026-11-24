@extends('layouts.portal')

@section('title', 'Enrollments')
@section('topbar-title', 'Enrollment management')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div>
        <div class="eyebrow mb-1">REGISTRAR WORKSPACE</div>
        <h1 class="page-title mb-1">Enrollments</h1>
        <p class="muted mb-0">Create enrollment records and review pending approvals.</p>
    </div>
    <a href="{{ route('admin.enrollments.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Create enrollment</a>
</div>

<div class="surface p-3 mb-3">
    <form method="GET" action="{{ route('admin.enrollments.index') }}" class="row g-2">
        <div class="col-md">
            <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search student, student number, or academic year">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                @foreach(['pending' => 'Pending', 'enrolled' => 'Approved / Enrolled', 'rejected' => 'Rejected'] as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto d-grid"><button class="btn btn-outline-primary" type="submit"><i class="bi bi-filter me-1"></i> Filter</button></div>
        @if($search !== '' || $status)<div class="col-auto d-grid"><a href="{{ route('admin.enrollments.index') }}" class="btn btn-light">Clear</a></div>@endif
    </form>
</div>

<div class="surface overflow-hidden">
    <div class="p-3 p-md-4 border-bottom d-flex justify-content-between align-items-center">
        <div><h2 class="h5 fw-bold mb-1">Enrollment records</h2><p class="muted small mb-0">{{ $enrollments->total() }} record(s) found</p></div>
        <span class="badge badge-soft rounded-pill">Admin / Staff</span>
    </div>
    @if($enrollments->isEmpty())
        <div class="text-center p-5">
            <div class="stat-icon mx-auto mb-3"><i class="bi bi-journal-check"></i></div>
            <h2 class="h6 fw-bold">No enrollment records found</h2>
            <p class="muted small mb-3">Create an enrollment to get started.</p>
            <a href="{{ route('admin.enrollments.create') }}" class="btn btn-primary btn-sm">Create enrollment</a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Student</th><th>Academic year</th><th>Semester</th><th>Subjects</th><th>Status</th><th class="text-end">Action</th></tr></thead>
                <tbody>
                @foreach($enrollments as $enrollment)
                    <tr>
                        <td><div class="fw-semibold">{{ $enrollment->student->user->name }}</div><div class="small muted">{{ $enrollment->student->student_number }}</div></td>
                        <td class="fw-semibold">{{ $enrollment->academic_year }}</td>
                        <td>{{ $enrollment->semester }}</td>
                        <td>{{ $enrollment->enrollment_subjects_count }}</td>
                        <td>
                            @php($statusClass = ['pending' => 'text-bg-warning', 'enrolled' => 'text-bg-success', 'rejected' => 'text-bg-danger'][$enrollment->status] ?? 'text-bg-secondary')
                            <span class="badge {{ $statusClass }} text-capitalize">{{ $enrollment->status }}</span>
                        </td>
                        <td class="text-end"><a href="{{ route('admin.enrollments.show', $enrollment) }}" class="btn btn-sm btn-outline-primary">Review</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $enrollments->links() }}</div>
    @endif
</div>
@endsection
