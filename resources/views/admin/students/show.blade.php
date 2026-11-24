@extends('layouts.portal')

@section('title', 'Student Record')
@section('topbar-title', 'Student record')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div>
        <a href="{{ route('admin.students.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Back to student records</a>
        <div class="eyebrow mt-3 mb-1">OFFICIAL RECORD</div>
        <h1 class="page-title mb-1">{{ $student->user->name }}</h1>
        <p class="muted mb-0">{{ $student->student_number }}</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-primary"><i class="bi bi-pencil me-1"></i> Edit record</a>
        @if(auth()->user()->isAdministrator())
            <form method="POST" action="{{ route('admin.students.account-status', $student) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="is_active" value="{{ $student->user->is_active ? '0' : '1' }}">
                <button class="btn btn-outline-warning" type="submit">
                    {{ $student->user->is_active ? 'Deactivate portal account' : 'Activate portal account' }}
                </button>
            </form>
            @if($student->enrollments->isEmpty())
                <form method="POST" action="{{ route('admin.students.destroy', $student) }}" onsubmit="return confirm('Permanently delete this student record and its login account? This cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger" type="submit">Delete record</button>
                </form>
            @endif
        @endif
    </div>
</div>

<div class="surface p-4 mb-3">
    <div class="d-flex align-items-center gap-3 mb-4">
        <div class="brand-mark" style="width:58px;height:58px;border-radius:18px;font-size:1.3rem">{{ strtoupper(substr($student->user->name, 0, 1)) }}</div>
        <div>
            <div class="h5 fw-bold mb-1">{{ $student->user->name }}</div>
            <div class="muted small">{{ $student->user->email }}</div>
        </div>
        <div class="ms-auto text-end">
            <span class="badge {{ $student->user->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $student->user->is_active ? 'Portal active' : 'Portal inactive' }}</span>
            <div class="small muted mt-1 text-capitalize">{{ $student->status }} student status</div>
        </div>
    </div>
    <div class="row g-4 border-top pt-4">
        <div class="col-md-4"><div class="small muted">Student number</div><div class="fw-semibold">{{ $student->student_number }}</div></div>
        <div class="col-md-4"><div class="small muted">Program</div><div class="fw-semibold">{{ $student->program?->name ?? 'Not assigned' }}</div></div>
        <div class="col-md-4"><div class="small muted">Year level</div><div class="fw-semibold">{{ $student->year_level ?? 'Not assigned' }}</div></div>
        <div class="col-md-4"><div class="small muted">Email</div><div class="fw-semibold text-break">{{ $student->user->email }}</div></div>
        <div class="col-md-4"><div class="small muted">Account created</div><div class="fw-semibold">{{ $student->created_at?->format('M d, Y') }}</div></div>
    </div>
</div>

<div class="surface overflow-hidden">
    <div class="p-3 p-md-4 border-bottom"><h2 class="h5 fw-bold mb-1">Enrollment history</h2><p class="small muted mb-0">Academic history is preserved when a portal account is deactivated. Records with enrollment history cannot be permanently deleted.</p></div>
    @if($student->enrollments->isEmpty())
        <div class="p-4 text-center muted">No enrollment records have been added.</div>
    @else
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light"><tr><th>Academic year</th><th>Semester</th><th>Status</th><th>Subjects</th></tr></thead>
                <tbody>
                @foreach($student->enrollments as $enrollment)
                    <tr>
                        <td class="fw-semibold">{{ $enrollment->academic_year }}</td>
                        <td>{{ $enrollment->semester }}</td>
                        <td><span class="badge text-bg-light text-capitalize">{{ $enrollment->status }}</span></td>
                        <td>{{ $enrollment->enrollmentSubjects->count() }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
