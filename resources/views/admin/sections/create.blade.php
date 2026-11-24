@extends('layouts.portal')

@section('title', 'Add Section')
@section('topbar-title', 'Add section')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.sections.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Back to sections</a>
    <h1 class="page-title mt-3 mb-1">Add section</h1>
    <p class="muted mb-0">Create a section under an academic program and year level.</p>
</div>
<form method="POST" action="{{ route('admin.sections.store') }}">
    @csrf
    @include('admin.sections._form', ['isEdit' => false])
</form>
@endsection
