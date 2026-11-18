@extends('layouts.portal')

@section('title', 'Edit Subject')
@section('topbar-title', 'Edit subject')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.subjects.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Back to subjects</a>
    <div class="eyebrow mt-3 mb-1">ACADEMIC SETUP</div>
    <h1 class="page-title mb-1">Edit subject</h1>
    <p class="muted mb-0">Update {{ $subject->code }}.</p>
</div>
<form method="POST" action="{{ route('admin.subjects.update', $subject) }}">
    @csrf
    @method('PUT')
    @include('admin.subjects._form', ['subject' => $subject])
</form>
@endsection
