@extends('layouts.portal')

@section('title', 'Edit Section')
@section('topbar-title', 'Edit section')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.sections.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Back to sections</a>
    <h1 class="page-title mt-3 mb-1">Edit section</h1>
    <p class="muted mb-0">Update the section assignment details or availability.</p>
</div>
<form method="POST" action="{{ route('admin.sections.update', $section) }}">
    @csrf
    @method('PUT')
    @include('admin.sections._form', ['isEdit' => true])
</form>
@endsection
