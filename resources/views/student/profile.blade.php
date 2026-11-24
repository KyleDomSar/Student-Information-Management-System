@extends('layouts.portal')

@section('title', 'My Profile')
@section('topbar-title', 'My profile')

@section('content')
<div class="mb-4">
    <div class="eyebrow mb-1">PERSONAL INFORMATION</div>
    <h1 class="page-title mb-1">My Profile</h1>
    <p class="muted mb-0">You may update permitted personal information. Official academic records remain under authorized staff control.</p>
</div>

@if(!$student)
    <div class="surface p-4">Your student profile has not been linked yet. Please contact the registrar.</div>
@else
    <div class="surface p-4 p-md-5 mb-3">
        <div class="d-flex align-items-center gap-3 mb-4">
            <div class="brand-mark" style="width:58px;height:58px;border-radius:18px;font-size:1.3rem">{{ strtoupper(substr($student->user->name, 0, 1)) }}</div>
            <div><h2 class="h4 fw-bold mb-1">{{ $student->user->name }}</h2><div class="muted">{{ $student->student_number }}</div></div>
        </div>

        <form method="POST" action="{{ route('student.profile.update') }}">
            @csrf
            @method('PATCH')
            <h2 class="h6 fw-bold mb-3">Permitted personal information</h2>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label small fw-semibold">Full name</label>
                    <input id="name" name="name" value="{{ old('name', $student->user->name) }}" class="form-control" maxlength="255" required>
                    @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label small fw-semibold">Email address</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $student->user->email) }}" class="form-control" maxlength="255" required>
                    @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3"><i class="bi bi-check2 me-1"></i> Save personal information</button>
        </form>

        <hr class="my-4">
        <h2 class="h6 fw-bold mb-3">Official academic information</h2>
        <div class="row g-4">
            <div class="col-md-6"><div class="small muted">Student number</div><div class="fw-semibold">{{ $student->student_number }}</div></div>
            <div class="col-md-6"><div class="small muted">Program</div><div class="fw-semibold">{{ $student->program?->name ?? 'Not assigned' }}</div></div>
            <div class="col-md-6"><div class="small muted">Year level</div><div class="fw-semibold">{{ $student->year_level ?? 'Not assigned' }}</div></div>
            <div class="col-md-6"><div class="small muted">Student status</div><div class="fw-semibold text-capitalize">{{ $student->status }}</div></div>
            <div class="col-md-6"><div class="small muted">Account status</div><div class="fw-semibold">{{ $student->user->is_active ? 'Active' : 'Inactive' }}</div></div>
        </div>
        <div class="alert alert-info border-0 small mt-4 mb-0"><i class="bi bi-info-circle me-1"></i> Student number, program, year level, and enrollment records can only be changed by authorized school personnel.</div>
    </div>

    <div class="surface p-4">
        <h2 class="h5 fw-bold mb-1">Change password</h2>
        <p class="muted small mb-3">Enter your current password before choosing a new one.</p>
        <form method="POST" action="{{ route('student.password.update') }}">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="current_password" class="form-label small fw-semibold">Current password</label>
                    <input id="current_password" type="password" name="current_password" class="form-control" autocomplete="current-password" required>
                    @error('current_password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="password" class="form-label small fw-semibold">New password</label>
                    <input id="password" type="password" name="password" class="form-control" minlength="10" autocomplete="new-password" required>
                    @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="password_confirmation" class="form-label small fw-semibold">Confirm new password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" minlength="10" autocomplete="new-password" required>
                </div>
            </div>
            <button type="submit" class="btn btn-outline-primary mt-3"><i class="bi bi-shield-lock me-1"></i> Update password</button>
        </form>
    </div>
@endif
@endsection
