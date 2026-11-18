<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sign in') | {{ config('app.name', 'SIMS') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root { --sims-ink:#17243b; --sims-muted:#718096; --sims-blue:#3157d5; --sims-bg:#f4f6fb; }
        body { min-height:100vh; background:radial-gradient(circle at top left,#e7edff 0,transparent 35%),var(--sims-bg); color:var(--sims-ink); }
        .brand-mark { display:inline-grid; place-items:center; width:42px; height:42px; border-radius:13px; background:var(--sims-blue); color:#fff; font-weight:800; letter-spacing:-1px; }
        .auth-card { border:1px solid #e7ebf3; border-radius:22px; box-shadow:0 24px 70px rgba(34,49,87,.09); }
        .form-control { min-height:48px; border-color:#dfe5ef; border-radius:11px; }
        .form-control:focus { border-color:#8299f0; box-shadow:0 0 0 .2rem rgba(49,87,213,.12); }
        .btn-sims { min-height:48px; border-radius:11px; background:var(--sims-blue); border-color:var(--sims-blue); color:#fff; font-weight:600; }
        .btn-sims:hover { background:#2547b8; border-color:#2547b8; color:#fff; }
        .muted { color:var(--sims-muted); }
    </style>
</head>
<body>
    <nav class="container py-4">
        <a href="{{ route('login') }}" class="text-decoration-none text-dark d-inline-flex align-items-center gap-2">
            <span class="brand-mark">S</span>
            <span class="fw-bold fs-5">SIMS <span class="muted fw-normal">Portal</span></span>
        </a>
    </nav>

    <main class="container pb-5">
        @yield('content')
    </main>

    <footer class="container text-center small muted pb-4">
        Student Information Management System · Academic project
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
