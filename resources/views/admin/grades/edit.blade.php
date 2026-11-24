@extends('layouts.portal')

@section('title', 'Encode Grade')
@section('topbar-title', 'Grade entry')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.grades.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Back to gradebook</a>
    <div class="eyebrow mt-3 mb-1">FINAL GRADE</div>
    <h1 class="page-title mb-1">{{ $enrollmentSubject->subject->code }} · {{ $enrollmentSubject->subject->title }}</h1>
    <p class="muted mb-0">{{ $enrollmentSubject->enrollment->student->user->name }} · {{ $enrollmentSubject->enrollment->student->student_number }}</p>
</div>

@if($errors->any())
    <div class="alert alert-danger"><div class="fw-semibold mb-1">Please review the form.</div><ul class="mb-0 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="surface p-4 mb-3">
    <div class="row g-3">
        <div class="col-md-4"><div class="small muted">Academic year</div><div class="fw-semibold">{{ $enrollmentSubject->enrollment->academic_year }}</div></div>
        <div class="col-md-4"><div class="small muted">Semester</div><div class="fw-semibold">{{ $enrollmentSubject->enrollment->semester }}</div></div>
        <div class="col-md-4"><div class="small muted">Units</div><div class="fw-semibold">{{ $enrollmentSubject->subject->units }}</div></div>
    </div>
</div>

<form method="POST" action="{{ route('admin.grades.update', $enrollmentSubject) }}">
    @csrf
    @method('PUT')
    <div class="surface p-3 p-md-4">
        <div class="mb-3">
            <label for="final_grade" class="form-label small fw-semibold">Final grade ({{ config('sims.grade_scale.minimum') }} to {{ config('sims.grade_scale.maximum') }}) *</label>
            <input id="final_grade" type="number" name="final_grade" min="{{ config('sims.grade_scale.minimum') }}" max="{{ config('sims.grade_scale.maximum') }}" step="0.01" value="{{ old('final_grade', $enrollmentSubject->final_grade) }}" class="form-control" required>
            @error('final_grade')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            <div class="form-text">Enter the official numeric grade provided by the instructor or registrar.</div>
        </div>
        <div class="mb-3">
            <label for="remarks" class="form-label small fw-semibold">Remarks</label>
            <input id="remarks" name="remarks" value="{{ old('remarks', $enrollmentSubject->remarks) }}" class="form-control" maxlength="255" placeholder="Optional remarks">
            @error('remarks')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="alert alert-info border-0 small mb-0"><i class="bi bi-shield-lock me-1"></i> Only authorized Admin/Staff accounts can change grades. Grade changes are recorded in the audit log.</div>
    </div>
    <div class="d-flex flex-wrap gap-2 mt-3">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i> Save grade</button>
        <a href="{{ route('admin.grades.index') }}" class="btn btn-light">Cancel</a>
    </div>
</form>
@endsection
