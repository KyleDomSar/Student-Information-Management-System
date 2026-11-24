@extends('layouts.portal')

@section('title', 'Add Class Schedule')
@section('topbar-title', 'Add class schedule')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.schedules.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Back to class schedules</a>
    <div class="eyebrow mt-3 mb-1">TIMETABLE SETUP</div>
    <h1 class="page-title mb-1">Add class schedule</h1>
    <p class="muted mb-0">Create a meeting schedule for a subject attached to an approved enrollment.</p>
</div>
<form method="POST" action="{{ route('admin.schedules.store') }}">
    @csrf
    @include('admin.schedules._form', ['schedule' => null])
</form>
@endsection
