@extends('layouts.portal')

@section('title', 'My Profile')
@section('topbar-title', 'My profile')

@section('content')
<div class="mb-4">
    <div class="eyebrow mb-1">PERSONAL INFORMATION</div>
    <h1 class="page-title mb-1">My Profile</h1>
    <p class="muted mb-0">Official student information is maintained by authorized school personnel.</p>
</div>

@if(!$student)
    <div class="surface p-4">Your student profile has not been linked yet. Please contact the registrar.</div>
@else
    <div class="surface p-4 p-md-5">
        <div class="d-flex align-items-center gap-3 mb-4">
            <div class="brand-mark" style="width:58px;height:58px;border-radius:18px;font-size:1.3rem">{{ strtoupper(substr($student->user->name, 0, 1)) }}</div>
            <div><h2 class="h4 fw-bold mb-1">{{ $student->user->name }}</h2><div class="muted">{{ $student->student_number }}</div></div>
        </div>
        <div class="row g-4">
            <div class="col-md-6"><div class="small muted">Email address</div><div class="fw-semibold">{{ $student->user->email }}</div></div>
            <div class="col-md-6"><div class="small muted">Student number</div><div class="fw-semibold">{{ $student->student_number }}</div></div>
            <div class="col-md-6"><div class="small muted">Program</div><div class="fw-semibold">{{ $student->program?->name ?? 'Not assigned' }}</div></div>
            <div class="col-md-6"><div class="small muted">Year level</div><div class="fw-semibold">{{ $student->year_level ?? 'Not assigned' }}</div></div>
            <div class="col-md-6"><div class="small muted">Student status</div><div class="fw-semibold text-capitalize">{{ $student->status }}</div></div>
            <div class="col-md-6"><div class="small muted">Account status</div><div class="fw-semibold">{{ $student->user->is_active ? 'Active' : 'Inactive' }}</div></div>
        </div>
        <div class="alert alert-info border-0 small mt-4 mb-0"><i class="bi bi-info-circle me-1"></i> This page is read-only. To request a correction, contact the registrar or authorized school staff.</div>
    </div>
@endif
@endsection
