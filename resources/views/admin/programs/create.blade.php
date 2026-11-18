@extends('layouts.portal')

@section('title', 'Add Program')
@section('topbar-title', 'Add program')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.programs.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Back to programs</a>
    <div class="eyebrow mt-3 mb-1">ACADEMIC SETUP</div>
    <h1 class="page-title mb-1">Add program</h1>
    <p class="muted mb-0">Create a program that can be assigned to student records.</p>
</div>
<form method="POST" action="{{ route('admin.programs.store') }}">
    @csrf
    @include('admin.programs._form', ['program' => null])
</form>
@endsection
