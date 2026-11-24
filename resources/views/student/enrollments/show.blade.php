@extends('layouts.portal')

@section('title', 'Enrollment Details')
@section('topbar-title', 'Enrollment details')

@section('content')
<div class="mb-4">
    <a href="{{ route('student.enrollments.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Back to my enrollments</a>
    <div class="eyebrow mt-3 mb-1">ACADEMIC RECORD #{{ $enrollment->id }}</div>
    <h1 class="page-title mb-1">{{ $enrollment->academic_year }} · {{ $enrollment->semester }} Semester</h1>
    <p class="muted mb-0">Official enrollment information provided by the registrar.</p>
</div>

<div class="surface p-4 mb-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div><div class="small muted mb-1">Enrollment status</div><div class="h5 fw-bold text-capitalize mb-0">{{ $enrollment->status }}</div></div>
        @php($statusClass = ['pending' => 'text-bg-warning', 'enrolled' => 'text-bg-success', 'rejected' => 'text-bg-danger'][$enrollment->status] ?? 'text-bg-secondary')
        <span class="badge {{ $statusClass }} text-capitalize px-3 py-2">{{ $enrollment->status }}</span>
    </div>
    <hr>
    <div class="row g-3">
        <div class="col-md-4"><div class="small muted">Student number</div><div class="fw-semibold">{{ $enrollment->student->student_number }}</div></div>
        <div class="col-md-4"><div class="small muted">Academic year</div><div class="fw-semibold">{{ $enrollment->academic_year }}</div></div>
        <div class="col-md-4"><div class="small muted">Semester</div><div class="fw-semibold">{{ $enrollment->semester }}</div></div>
    </div>
</div>

<div class="surface overflow-hidden">
    <div class="p-3 p-md-4 border-bottom"><h2 class="h5 fw-bold mb-1">Enrolled subjects</h2><p class="muted small mb-0">{{ $enrollment->enrollmentSubjects->count() }} subject(s) · Read-only</p></div>
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

<div class="alert alert-info border-0 small mt-3"><i class="bi bi-info-circle me-1"></i> Student Portal access is read-only. To ask for an enrollment correction, contact the registrar.</div>
@endsection
