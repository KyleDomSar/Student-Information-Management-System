@extends('layouts.portal')

@section('title', 'Add Account')
@section('topbar-title', 'Add account')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.users.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Back to user management</a>
    <div class="eyebrow mt-3 mb-1">SECURITY & ACCESS</div>
    <h1 class="page-title mb-1">Add account</h1>
    <p class="muted mb-0">Create an administrator or staff account.</p>
</div>
<form method="POST" action="{{ route('admin.users.store') }}">
    @csrf
    @include('admin.users._form', ['user' => null, 'isEdit' => false])
</form>
@endsection
