@extends('layouts.portal')

@section('title', 'Student Records')
@section('topbar-title', 'Student records')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div>
        <div class="eyebrow mb-1">RECORDS MANAGEMENT</div>
        <h1 class="page-title mb-1">Students</h1>
        <p class="muted mb-0">Create, review, and maintain official student profiles.</p>
    </div>
    <a href="{{ route('admin.students.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add student</a>
</div>

<div class="surface p-3 mb-3">
    <form method="GET" action="{{ route('admin.students.index') }}" class="row g-2">
        <div class="col-md">
            <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search by student number, name, or email">
        </div>
        <div class="col-auto d-grid">
            <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search me-1"></i> Search</button>
        </div>
        @if($search !== '')
            <div class="col-auto d-grid"><a href="{{ route('admin.students.index') }}" class="btn btn-light">Clear</a></div>
        @endif
    </form>
</div>

<div class="surface overflow-hidden">
    <div class="p-3 p-md-4 border-bottom d-flex justify-content-between align-items-center">
        <div><h2 class="h5 fw-bold mb-1">Student directory</h2><p class="muted small mb-0">{{ $students->total() }} record(s) found</p></div>
        <span class="badge badge-soft rounded-pill">Admin / Staff</span>
    </div>
    @if($students->isEmpty())
        <div class="text-center p-5">
            <div class="stat-icon mx-auto mb-3"><i class="bi bi-people"></i></div>
            <h2 class="h6 fw-bold">No student records found</h2>
            <p class="muted small mb-3">{{ $search !== '' ? 'Try another search term.' : 'Create the first student profile to get started.' }}</p>
            @if($search === '')
                <a href="{{ route('admin.students.create') }}" class="btn btn-primary btn-sm">Add student</a>
            @endif
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Student</th><th>Student number</th><th>Program</th><th>Year</th><th>Status</th><th class="text-end">Action</th></tr></thead>
                <tbody>
                @foreach($students as $student)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $student->user->name }}</div>
                            <div class="small muted">{{ $student->user->email }}</div>
                        </td>
                        <td class="fw-semibold">{{ $student->student_number }}</td>
                        <td>{{ $student->program?->code ?? 'Not assigned' }}</td>
                        <td>{{ $student->year_level ?? '—' }}</td>
                        <td>
                            <span class="badge {{ $student->status === 'active' && $student->user->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ $student->user->is_active ? ucfirst($student->status) : 'Account inactive' }}
                            </span>
                        </td>
                        <td class="text-end"><a href="{{ route('admin.students.show', $student) }}" class="btn btn-sm btn-outline-primary">View record</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $students->links() }}</div>
    @endif
</div>
@endsection
