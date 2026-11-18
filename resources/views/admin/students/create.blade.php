@extends('layouts.portal')

@section('title', 'Add Student')
@section('topbar-title', 'Add student')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.students.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Back to student records</a>
    <div class="eyebrow mt-3 mb-1">NEW ACCOUNT</div>
    <h1 class="page-title mb-1">Add student</h1>
    <p class="muted mb-0">Create the student's portal account and official profile together.</p>
</div>

<form method="POST" action="{{ route('admin.students.store') }}">
    @csrf
    @include('admin.students._form', ['student' => null, 'isEdit' => false])
</form>
@endsection
