@extends('layouts.portal')

@section('title', 'Edit Account')
@section('topbar-title', 'Edit account')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.users.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Back to user management</a>
    <div class="eyebrow mt-3 mb-1">SECURITY & ACCESS</div>
    <h1 class="page-title mb-1">Edit account</h1>
    <p class="muted mb-0">Update {{ $user->email }}.</p>
</div>
<form method="POST" action="{{ route('admin.users.update', $user) }}">
    @csrf
    @method('PUT')
    @include('admin.users._form', ['user' => $user, 'isEdit' => true])
</form>
@endsection
