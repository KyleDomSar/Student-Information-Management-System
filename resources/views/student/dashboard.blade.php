@extends('layouts.portal')

@section('title', 'Student Dashboard')
@section('topbar-title', 'My student space')

@section('content')
<div class="mb-4">
    <div class="eyebrow mb-1">STUDENT OVERVIEW</div>
    <h1 class="page-title mb-1">Welcome, {{ auth()->user()->name }}</h1>
    <p class="muted mb-0">Your academic information in one place.</p>
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
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div>
                        <div class="eyebrow mb-2">MY STUDENT PROFILE</div>
                        <h2 class="h4 fw-bold mb-1">{{ $student->user->name }}</h2>
                        <div class="muted">{{ $student->student_number }}</div>
                    </div>
                    <span class="badge rounded-pill text-bg-success-subtle text-success">{{ ucfirst($student->status) }}</span>
                </div>
                <hr class="my-4">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="small muted">Program</div>
                        <div class="fw-semibold">{{ $student->program?->name ?? 'Not assigned' }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="small muted">Year level</div>
                        <div class="fw-semibold">{{ $student->year_level ? $student->year_level . (['st','nd','rd','th'][$student->year_level - 1] ?? 'th') . ' Year' : 'Not assigned' }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="small muted">Email</div>
                        <div class="fw-semibold text-break">{{ $student->user->email }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="small muted">Account status</div>
                        <div class="fw-semibold">{{ $student->user->is_active ? 'Active' : 'Inactive' }}</div>
                    </div>
                </div>
                <a href="{{ route('student.profile') }}" class="btn btn-outline-primary mt-4">View full profile <i class="bi bi-arrow-right ms-1"></i></a>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="surface p-4 h-100">
                <div class="eyebrow mb-2">ENROLLMENT SUMMARY</div>
                <div class="stat-value">{{ $student->enrollments->count() }}</div>
                <div class="muted small mb-4">Recent enrollment records shown</div>
                <div class="d-flex justify-content-between border-top pt-3">
                    <span class="muted small">Current student status</span>
                    <span class="fw-semibold text-capitalize">{{ $student->status }}</span>
                </div>
                <div class="d-flex justify-content-between border-top pt-3 mt-3">
                    <span class="muted small">Student number</span>
                    <span class="fw-semibold">{{ $student->student_number }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="surface overflow-hidden">
        <div class="p-3 p-md-4 border-bottom">
            <h2 class="h5 fw-bold mb-1">Enrollment history</h2>
            <p class="muted small mb-0">Read-only view of your recorded enrollment terms.</p>
        </div>
        @if($student->enrollments->isEmpty())
            <div class="p-4 text-center muted">No enrollment records available yet.</div>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light"><tr><th>Academic year</th><th>Semester</th><th>Status</th><th class="text-end">Subjects</th></tr></thead>
                    <tbody>
                    @foreach($student->enrollments as $enrollment)
                        <tr>
                            <td class="fw-semibold">{{ $enrollment->academic_year }}</td>
                            <td>{{ $enrollment->semester }}</td>
                            <td><span class="badge text-bg-light text-capitalize">{{ $enrollment->status }}</span></td>
                            <td class="text-end">{{ $enrollment->enrollmentSubjects->count() }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endif
@endsection
