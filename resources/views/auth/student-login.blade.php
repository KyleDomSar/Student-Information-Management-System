@extends('layouts.guest')

@section('title', 'Student Sign In')

@section('content')
<div class="row justify-content-center align-items-center" style="min-height:65vh">
    <div class="col-sm-10 col-md-7 col-lg-5 col-xl-4">
        <div class="auth-card bg-white p-4 p-lg-5">
            <a href="{{ route('login', [], false) }}" class="small text-decoration-none" style="color:#138b78"><i class="bi bi-arrow-left"></i> Choose portal</a>
            <div class="brand-mark mt-4 mb-3" style="background:#138b78"><i class="bi bi-mortarboard"></i></div>
            <div class="small fw-bold text-uppercase mb-2" style="color:#138b78">Student Portal</div>
            <h1 class="h3 fw-bold mb-2">Hello, student</h1>
            <p class="muted mb-4">Sign in to view your academic information.</p>

            @if($errors->any())
                <div class="alert alert-danger small" role="alert">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('student.login.store', [], false) }}">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold small">Student email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control" autocomplete="username" required autofocus>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold small">Password</label>
                    <input id="password" type="password" name="password" class="form-control" autocomplete="current-password" required>
                </div>
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="remember" value="1" id="remember">
                    <label class="form-check-label small muted" for="remember">Remember me</label>
                </div>
                <button type="submit" class="btn btn-sims w-100" style="background:#138b78;border-color:#138b78">Sign in to Student Portal</button>
            </form>
        </div>
    </div>
</div>
@endsection
