@extends('layouts.portal')

@section('title', 'Create Enrollment')
@section('topbar-title', 'Create enrollment')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.enrollments.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Back to enrollments</a>
    <div class="eyebrow mt-3 mb-1">REGISTRAR WORKSPACE</div>
    <h1 class="page-title mb-1">Create enrollment record</h1>
    <p class="muted mb-0">Record the student's academic year and semester.</p>
</div>
@if($errors->any())
    <div class="alert alert-danger"><div class="fw-semibold mb-1">Please review the form.</div><ul class="mb-0 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<form method="POST" action="{{ route('admin.enrollments.store') }}">
    @csrf
    <div class="surface p-3 p-md-4">
        <h2 class="h6 fw-bold mb-3">Student and enrollment information</h2>
        <div class="row g-3">
            <div class="col-12">
                <label for="student_id" class="form-label small fw-semibold">Student *</label>
                <select id="student_id" name="student_id" class="form-select" required>
                    <option value="">Choose a student</option>
                    @foreach($students as $student)<option value="{{ $student->id }}" @selected((string) old('student_id') === (string) $student->id)>{{ $student->student_number }} · {{ $student->user->name }} ({{ $student->program?->code ?? 'No program' }})</option>@endforeach
                </select>
                @if($students->isEmpty())<div class="form-text text-danger">No active student accounts are available. Create or activate a student first.</div>@endif
                @error('student_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="academic_year" class="form-label small fw-semibold">Academic year *</label>
                <input id="academic_year" name="academic_year" value="{{ old('academic_year', now()->year.'-'.(now()->year + 1)) }}" class="form-control" placeholder="2026-2027" pattern="[0-9]{4}-[0-9]{4}" required>
                <div class="form-text">Format: YYYY-YYYY.</div>
                @error('academic_year')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="semester" class="form-label small fw-semibold">Semester *</label>
                <select id="semester" name="semester" class="form-select" required>
                    <option value="">Choose a semester</option>
                    @foreach(['First Semester', 'Second Semester', 'Midyear'] as $term)<option value="{{ $term }}" @selected(old('semester') === $term)>{{ $term }}</option>@endforeach
                </select>
                @error('semester')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2 mt-3">
        <button type="submit" class="btn btn-primary" {{ $students->isEmpty() ? 'disabled' : '' }}><i class="bi bi-check2 me-1"></i> Save enrollment record</button>
        <a href="{{ route('admin.enrollments.index') }}" class="btn btn-light">Cancel</a>
    </div>
</form>
@endsection
