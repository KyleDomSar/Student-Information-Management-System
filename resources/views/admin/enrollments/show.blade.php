@extends('layouts.portal')

@section('title', 'Enrollment Record')
@section('topbar-title', 'Enrollment record')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.enrollments.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Back to enrollments</a>
    <div class="eyebrow mt-3 mb-1">ENROLLMENT RECORD #{{ $enrollment->id }}</div>
    <h1 class="page-title mb-1">{{ $enrollment->student->user->name }}</h1>
    <p class="muted mb-0">{{ $enrollment->student->student_number }} · {{ $enrollment->academic_year }} · {{ $enrollment->semester }}</p>
</div>
<div class="surface p-4">
    <h2 class="h6 fw-bold mb-3">Student and enrollment information</h2>
    <div class="row g-3">
        <div class="col-md-6"><div class="small muted">Student number</div><div class="fw-semibold">{{ $enrollment->student->student_number }}</div></div>
        <div class="col-md-6"><div class="small muted">Program / Course</div><div class="fw-semibold">{{ $enrollment->student->program?->name ?? 'Not assigned' }}</div></div>
        <div class="col-md-6"><div class="small muted">Year level</div><div class="fw-semibold">{{ $enrollment->student->yearLevelRecord?->name ?? 'Not assigned' }}</div></div>
        <div class="col-md-6"><div class="small muted">Assigned section</div><div class="fw-semibold">{{ $enrollment->student->section?->code ?? 'Not assigned' }}</div></div>
        <div class="col-md-6"><div class="small muted">Academic year</div><div class="fw-semibold">{{ $enrollment->academic_year }}</div></div>
        <div class="col-md-6"><div class="small muted">Semester</div><div class="fw-semibold">{{ $enrollment->semester }}</div></div>
        <div class="col-md-6"><div class="small muted">Recorded by</div><div class="fw-semibold">{{ $enrollment->creator?->name ?? 'Unknown' }}</div></div>
        <div class="col-md-6"><div class="small muted">Recorded on</div><div class="fw-semibold">{{ $enrollment->created_at?->format('M d, Y h:i A') }}</div></div>
    </div>
</div>
@endsection
