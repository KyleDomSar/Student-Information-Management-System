@extends('layouts.portal')

@section('title', 'Enrollment Details')
@section('topbar-title', 'Enrollment details')

@section('content')
<div class="mb-4">
    <a href="{{ route('student.enrollments.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Back to my enrollments</a>
    <div class="eyebrow mt-3 mb-1">ACADEMIC RECORD #{{ $enrollment->id }}</div>
    <h1 class="page-title mb-1">{{ $enrollment->academic_year }} · {{ $enrollment->semester }}</h1>
    <p class="muted mb-0">Official enrollment information provided by the registrar.</p>
</div>
<div class="surface p-4">
    <div class="row g-3">
        <div class="col-md-4"><div class="small muted">Student number</div><div class="fw-semibold">{{ $enrollment->student->student_number }}</div></div>
        <div class="col-md-4"><div class="small muted">Program / Course</div><div class="fw-semibold">{{ $enrollment->student->program?->name ?? 'Not assigned' }}</div></div>
        <div class="col-md-4"><div class="small muted">Year level</div><div class="fw-semibold">{{ $enrollment->student->yearLevelRecord?->name ?? 'Not assigned' }}</div></div>
        <div class="col-md-4"><div class="small muted">Assigned section</div><div class="fw-semibold">{{ $enrollment->student->section?->code ?? 'Not assigned' }}</div></div>
        <div class="col-md-4"><div class="small muted">Academic year</div><div class="fw-semibold">{{ $enrollment->academic_year }}</div></div>
        <div class="col-md-4"><div class="small muted">Semester</div><div class="fw-semibold">{{ $enrollment->semester }}</div></div>
    </div>
</div>
<div class="alert alert-info border-0 small mt-3"><i class="bi bi-info-circle me-1"></i> Student Portal access is read-only. To ask for an enrollment correction, contact the registrar.</div>
@endsection
