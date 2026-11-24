@extends('layouts.portal')

@section('title', 'Edit Class Schedule')
@section('topbar-title', 'Edit class schedule')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.schedules.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Back to class schedules</a>
    <div class="eyebrow mt-3 mb-1">TIMETABLE SETUP</div>
    <h1 class="page-title mb-1">Edit class schedule</h1>
    <p class="muted mb-0">Update meeting details for schedule #{{ $schedule->id }}.</p>
</div>
<form method="POST" action="{{ route('admin.schedules.update', $schedule) }}">
    @csrf
    @method('PUT')
    @include('admin.schedules._form', ['schedule' => $schedule])
</form>
@endsection
