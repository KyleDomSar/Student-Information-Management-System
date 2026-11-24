@extends('layouts.portal')

@section('title', 'Student Dashboard')
@section('topbar-title', 'My student space')

@section('content')
<div class="mb-4">
    <div class="eyebrow mb-1">STUDENT OVERVIEW</div>
    <h1 class="page-title mb-1">Welcome, {{ auth()->user()->name }}</h1>
    <p class="muted mb-0">Your personal and academic information in one place.</p>
</div>

@if(!$student)
    <div class="surface p-4 p-md-5 text-center">
        <div class="stat-icon mx-auto mb-3"><i class="bi bi-person-exclamation"></i></div>
        <h2 class="h5 fw-bold">Student profile not yet linked</h2>
        <p class="muted mb-0">Your account is active, but a student profile has not been assigned. Please contact the registrar.</p>
    </div>
@else
    <div class="row g-3 mb-4">
        <div class="col-lg-7">
            <div class="surface p-4 h-100">
                <div class="eyebrow mb-2">MY STUDENT PROFILE</div>
                <h2 class="h4 fw-bold mb-1">{{ $student->user->name }}</h2>
                <div class="muted">{{ $student->student_number }}</div>
                <hr class="my-4">
                <div class="row g-3">
                    <div class="col-sm-6"><div class="small muted">Program / Course</div><div class="fw-semibold">{{ $student->program?->name ?? 'Not assigned' }}</div></div>
                    <div class="col-sm-6"><div class="small muted">Year level</div><div class="fw-semibold">{{ $student->yearLevelRecord?->name ?? 'Not assigned' }}</div></div>
                    <div class="col-sm-6"><div class="small muted">Assigned section</div><div class="fw-semibold">{{ $student->section?->code ?? 'Not assigned' }}</div></div>
                    <div class="col-sm-6"><div class="small muted">Email</div><div class="fw-semibold text-break">{{ $student->user->email }}</div></div>
                </div>
                <a href="{{ route('student.profile') }}" class="btn btn-outline-primary mt-4">View full profile <i class="bi bi-arrow-right ms-1"></i></a>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="surface p-4 h-100">
                <div class="eyebrow mb-2">ENROLLMENT SUMMARY</div>
                <div class="stat-value">{{ number_format($student->enrollments_count) }}</div>
                <div class="muted small mb-4">Recorded enrollment terms</div>
                <div class="d-flex justify-content-between border-top pt-3"><span class="muted small">Student status</span><span class="fw-semibold text-capitalize">{{ $student->status }}</span></div>
                <div class="d-flex justify-content-between border-top pt-3 mt-3"><span class="muted small">Portal account</span><span class="fw-semibold">{{ $student->user->is_active ? 'Active' : 'Inactive' }}</span></div>
                <a href="{{ route('student.enrollments.index') }}" class="btn btn-outline-primary w-100 mt-4">View enrollment history <i class="bi bi-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>

    <div class="surface p-3 p-md-4 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <div class="eyebrow mb-1">STUDENT MODULE</div>
                <h2 class="h5 fw-bold mb-1">Visitor time-out</h2>
                @if($student->visitorTimeouts->isNotEmpty())
                    <p class="muted small mb-0">Most recent: {{ $student->visitorTimeouts->first()->time_out_at->format('M d, Y · h:i A') }}</p>
                @else
                    <p class="muted small mb-0">No time-out records have been added yet.</p>
                @endif
            </div>
            <a href="{{ route('student.visitor-timeouts.index') }}" class="btn btn-outline-primary">Open visitor time-out <i class="bi bi-arrow-right ms-1"></i></a>
        </div>
    </div>

    <div class="surface overflow-hidden">
        <div class="p-3 p-md-4 border-bottom"><h2 class="h5 fw-bold mb-1">Enrollment history</h2><p class="muted small mb-0">Read-only records maintained by the registrar.</p></div>
        @if($student->enrollments->isEmpty())
            <div class="p-4 text-center muted">No enrollment records available yet.</div>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light"><tr><th>Academic year</th><th>Semester</th><th>Recorded on</th><th class="text-end">Details</th></tr></thead>
                    <tbody>
                    @foreach($student->enrollments as $enrollment)
                        <tr><td class="fw-semibold">{{ $enrollment->academic_year }}</td><td>{{ $enrollment->semester }}</td><td>{{ $enrollment->created_at?->format('M d, Y') }}</td><td class="text-end"><a href="{{ route('student.enrollments.show', $enrollment) }}" class="btn btn-sm btn-outline-primary">View</a></td></tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endif
@endsection
