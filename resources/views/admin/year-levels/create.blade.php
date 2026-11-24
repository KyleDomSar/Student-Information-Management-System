@extends('layouts.portal')

@section('title', 'Add Year Level')
@section('topbar-title', 'Add year level')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.year-levels.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Back to year levels</a>
    <h1 class="page-title mt-3 mb-1">Add year level</h1>
    <p class="muted mb-0">Create a year level that can be assigned to student records and sections.</p>
</div>
<form method="POST" action="{{ route('admin.year-levels.store') }}">
    @csrf
    @include('admin.year-levels._form', ['isEdit' => false])
</form>
@endsection
