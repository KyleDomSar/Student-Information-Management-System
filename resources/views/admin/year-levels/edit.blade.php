@extends('layouts.portal')

@section('title', 'Edit Year Level')
@section('topbar-title', 'Edit year level')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.year-levels.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Back to year levels</a>
    <h1 class="page-title mt-3 mb-1">Edit year level</h1>
    <p class="muted mb-0">Update the year level name or availability.</p>
</div>
<form method="POST" action="{{ route('admin.year-levels.update', $yearLevel) }}">
    @csrf
    @method('PUT')
    @include('admin.year-levels._form', ['isEdit' => true])
</form>
@endsection
