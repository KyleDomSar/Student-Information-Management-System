@extends('layouts.portal')

@section('title', 'Edit Program')
@section('topbar-title', 'Edit program')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.programs.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Back to programs</a>
    <div class="eyebrow mt-3 mb-1">ACADEMIC SETUP</div>
    <h1 class="page-title mb-1">Edit program</h1>
    <p class="muted mb-0">Update {{ $program->code }}.</p>
</div>
<form method="POST" action="{{ route('admin.programs.update', $program) }}">
    @csrf
    @method('PUT')
    @include('admin.programs._form', ['program' => $program])
</form>
@endsection
