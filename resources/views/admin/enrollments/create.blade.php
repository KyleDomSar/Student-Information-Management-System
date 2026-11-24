@extends('layouts.portal')

@section('title', 'Create Enrollment')
@section('topbar-title', 'Create enrollment')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.enrollments.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Back to enrollments</a>
    <div class="eyebrow mt-3 mb-1">NEW ACADEMIC RECORD</div>
    <h1 class="page-title mb-1">Create enrollment</h1>
    <p class="muted mb-0">The new record will be marked as pending until approved by authorized staff.</p>
</div>

@if($errors->any())
    <div class="alert alert-danger"><div class="fw-semibold mb-1">Please review the form.</div><ul class="mb-0 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<form method="POST" action="{{ route('admin.enrollments.store') }}">
    @csrf
    <div class="surface p-3 p-md-4 mb-3">
        <h2 class="h6 fw-bold mb-3">Student and term</h2>
        <div class="row g-3">
            <div class="col-12">
                <label for="student_id" class="form-label small fw-semibold">Student *</label>
                <select id="student_id" name="student_id" class="form-select" required>
                    <option value="">Choose a student</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" @selected((string) old('student_id') === (string) $student->id)>{{ $student->student_number }} · {{ $student->user->name }} ({{ $student->program?->code ?? 'No program' }})</option>
                    @endforeach
                </select>
                @if($students->isEmpty())<div class="form-text text-danger">No active student accounts are available. Create or activate a student first.</div>@endif
                @error('student_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="academic_year" class="form-label small fw-semibold">Academic year *</label>
                <input id="academic_year" name="academic_year" value="{{ old('academic_year', (now()->year).'-'.(now()->year + 1)) }}" class="form-control" placeholder="2026-2027" pattern="[0-9]{4}-[0-9]{4}" required>
                <div class="form-text">Format: YYYY-YYYY, for example 2026-2027.</div>
                @error('academic_year')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="semester" class="form-label small fw-semibold">Semester *</label>
                <select id="semester" name="semester" class="form-select" required>
                    <option value="">Choose a semester</option>
                    @foreach(['First', 'Second', 'Summer'] as $term)
                        <option value="{{ $term }}" @selected(old('semester') === $term)>{{ $term }} Semester</option>
                    @endforeach
                </select>
                @error('semester')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    <div class="surface p-3 p-md-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div><h2 class="h6 fw-bold mb-1">Subjects *</h2><p class="muted small mb-0">Only active subjects for the selected student's program can be saved.</p></div>
            <span class="badge badge-soft">Choose 1 to 20</span>
        </div>
        @if($subjects->isEmpty())
            <div class="alert alert-warning mb-0">No active subjects available. Add subjects in the Subject Catalog first.</div>
        @else
            <div class="row g-2">
                @foreach($subjects as $subject)
                    <div class="col-md-6">
                        <label class="border rounded-3 p-3 d-flex gap-2 h-100">
                            <input class="form-check-input mt-1" type="checkbox" name="subjects[]" value="{{ $subject->id }}" @checked(in_array((string) $subject->id, array_map('strval', (array) old('subjects', [])), true))>
                            <span>
                                <span class="d-block fw-semibold">{{ $subject->code }} · {{ $subject->title }}</span>
                                <span class="d-block muted small">{{ $subject->units }} units · {{ $subject->program?->code ?? 'General subject' }}</span>
                            </span>
                        </label>
                    </div>
                @endforeach
            </div>
        @endif
        @error('subjects')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
        @error('subjects.*')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
    </div>

    <div class="d-flex flex-wrap gap-2 mt-3">
        <button type="submit" class="btn btn-primary" {{ $students->isEmpty() || $subjects->isEmpty() ? 'disabled' : '' }}><i class="bi bi-check2 me-1"></i> Create pending enrollment</button>
        <a href="{{ route('admin.enrollments.index') }}" class="btn btn-light">Cancel</a>
    </div>
</form>
@endsection
