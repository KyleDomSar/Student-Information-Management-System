@extends('layouts.portal')

@section('title', 'Edit Student')
@section('topbar-title', 'Edit student')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.students.show', $student) }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Back to student record</a>
    <div class="eyebrow mt-3 mb-1">UPDATE RECORD</div>
    <h1 class="page-title mb-1">Edit student</h1>
    <p class="muted mb-0">Update {{ $student->student_number }}. Changes to official records are recorded in the audit log.</p>
</div>

<form method="POST" action="{{ route('admin.students.update', $student) }}">
    @csrf
    @method('PUT')
    @include('admin.students._form', ['student' => $student, 'isEdit' => true])
</form>
@endsection
