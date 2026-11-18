@extends('layouts.guest')

@section('title', 'Choose a portal')

@section('content')
<div class="row justify-content-center align-items-center" style="min-height:65vh">
    <div class="col-lg-9 col-xl-8">
        <div class="text-center mb-5">
            <div class="text-primary fw-bold small text-uppercase mb-2">Student Information Management System</div>
            <h1 class="display-6 fw-bold mb-2">Welcome to SIMS</h1>
            <p class="muted mb-0">Choose the portal that matches your account.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <a href="{{ route('admin.login') }}" class="auth-card bg-white p-4 p-lg-5 d-block h-100 text-decoration-none text-dark">
                    <div class="brand-mark mb-4"><i class="bi bi-shield-lock"></i></div>
                    <div class="small text-primary fw-bold text-uppercase mb-2">For authorized personnel</div>
                    <h2 class="h4 fw-bold">Admin & Staff</h2>
                    <p class="muted mb-4">Manage student records, academic programs, enrollments, and administrative tasks.</p>
                    <span class="fw-semibold text-primary">Continue to staff login <i class="bi bi-arrow-right"></i></span>
                </a>
            </div>
            <div class="col-md-6">
                <a href="{{ route('student.login') }}" class="auth-card bg-white p-4 p-lg-5 d-block h-100 text-decoration-none text-dark">
                    <div class="brand-mark mb-4" style="background:#138b78"><i class="bi bi-mortarboard"></i></div>
                    <div class="small fw-bold text-uppercase mb-2" style="color:#138b78">For enrolled students</div>
                    <h2 class="h4 fw-bold">Student Portal</h2>
                    <p class="muted mb-4">View your personal information, enrollment status, grades, and class schedule.</p>
                    <span class="fw-semibold" style="color:#138b78">Continue to student login <i class="bi bi-arrow-right"></i></span>
                </a>
            </div>
        </div>
        <p class="small muted text-center mt-4 mb-0">Accounts are provided by the school administrator. Public registration is disabled.</p>
    </div>
</div>
@endsection
