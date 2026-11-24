@extends('layouts.portal')

@section('title', 'Enrollment Review')
@section('topbar-title', 'Enrollment review')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div>
        <a href="{{ route('admin.enrollments.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Back to enrollments</a>
        <div class="eyebrow mt-3 mb-1">ENROLLMENT #{{ $enrollment->id }}</div>
        <h1 class="page-title mb-1">{{ $enrollment->student->user->name }}</h1>
        <p class="muted mb-0">{{ $enrollment->student->student_number }} · {{ $enrollment->academic_year }} · {{ $enrollment->semester }}</p>
    </div>
    <span class="badge rounded-pill text-bg-light border px-3 py-2 text-capitalize">{{ $enrollment->status }}</span>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="surface p-4 h-100">
            <h2 class="h6 fw-bold mb-3">Student and enrollment information</h2>
            <div class="row g-3">
                <div class="col-md-6"><div class="small muted">Student number</div><div class="fw-semibold">{{ $enrollment->student->student_number }}</div></div>
                <div class="col-md-6"><div class="small muted">Program</div><div class="fw-semibold">{{ $enrollment->student->program?->name ?? 'Not assigned' }}</div></div>
                <div class="col-md-6"><div class="small muted">Academic year</div><div class="fw-semibold">{{ $enrollment->academic_year }}</div></div>
                <div class="col-md-6"><div class="small muted">Semester</div><div class="fw-semibold">{{ $enrollment->semester }}</div></div>
                <div class="col-md-6"><div class="small muted">Created by</div><div class="fw-semibold">{{ $enrollment->creator?->name ?? 'Unknown' }}</div></div>
                <div class="col-md-6"><div class="small muted">Created at</div><div class="fw-semibold">{{ $enrollment->created_at?->format('M d, Y h:i A') }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="surface p-4 h-100">
            <div class="eyebrow mb-2">WORKFLOW STATUS</div>
            <div class="h4 fw-bold text-capitalize">{{ $enrollment->status }}</div>
            @if($enrollment->status === 'pending')
                <p class="muted small">Review the subject list, then approve or reject this enrollment.</p>
                <form method="POST" action="{{ route('admin.enrollments.update-status', $enrollment) }}" class="d-grid gap-2">
                    @csrf
                    @method('PATCH')
                    <button type="submit" name="status" value="enrolled" class="btn btn-success"><i class="bi bi-check-circle me-1"></i> Approve enrollment</button>
                    <button type="submit" name="status" value="rejected" class="btn btn-outline-danger" onclick="return confirm('Reject this enrollment?')"><i class="bi bi-x-circle me-1"></i> Reject enrollment</button>
                </form>
                @error('status')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
            @else
                <p class="muted small mb-0">This enrollment has already been reviewed. Its status cannot be changed through this screen.</p>
            @endif
        </div>
    </div>
</div>

<div class="surface overflow-hidden">
    <div class="p-3 p-md-4 border-bottom"><h2 class="h5 fw-bold mb-1">Selected subjects</h2><p class="muted small mb-0">{{ $enrollment->enrollmentSubjects->count() }} subject(s)</p></div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light"><tr><th>Subject code</th><th>Subject</th><th>Program</th><th class="text-end">Units</th></tr></thead>
            <tbody>
            @forelse($enrollment->enrollmentSubjects as $item)
                <tr>
                    <td class="fw-semibold">{{ $item->subject->code }}</td>
                    <td>{{ $item->subject->title }}</td>
                    <td>{{ $item->subject->program?->code ?? 'General' }}</td>
                    <td class="text-end">{{ $item->subject->units }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center muted p-4">No subjects attached to this enrollment.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
