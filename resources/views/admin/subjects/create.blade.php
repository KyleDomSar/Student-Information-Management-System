@extends('layouts.portal')

@section('title', 'Add Subject')
@section('topbar-title', 'Add subject')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.subjects.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Back to subjects</a>
    <div class="eyebrow mt-3 mb-1">ACADEMIC SETUP</div>
    <h1 class="page-title mb-1">Add subject</h1>
    <p class="muted mb-0">Add a new subject to your academic catalog.</p>
</div>
<form method="POST" action="{{ route('admin.subjects.store') }}">
    @csrf
    @include('admin.subjects._form', ['subject' => null])
</form>
@endsection
