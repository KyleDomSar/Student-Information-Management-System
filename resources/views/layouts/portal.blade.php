<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | {{ config('app.name', 'SIMS') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root { --ink:#17243b; --muted:#718096; --blue:#3157d5; --canvas:#f4f6fb; --sidebar:#14213a; }
        * { box-sizing:border-box; }
        body { min-height:100vh; background:var(--canvas); color:var(--ink); }
        .portal-shell { min-height:100vh; display:flex; }
        .sidebar { width:250px; flex-shrink:0; background:var(--sidebar); color:#d7deec; padding:24px 16px; display:flex; flex-direction:column; }
        .sidebar-brand { color:#fff; font-size:1.18rem; font-weight:750; text-decoration:none; display:flex; align-items:center; gap:11px; padding:0 10px 30px; }
        .brand-mark { display:inline-grid; place-items:center; width:38px; height:38px; border-radius:12px; background:#4266e8; color:white; font-weight:800; }
        .sidebar-label { color:#8593ad; text-transform:uppercase; letter-spacing:.11em; font-size:.68rem; font-weight:700; padding:8px 12px; }
        .sidebar .nav-link { color:#b9c4d9; padding:11px 12px; border-radius:10px; margin:2px 0; font-size:.91rem; }
        .sidebar .nav-link:hover,.sidebar .nav-link.active { background:rgba(255,255,255,.1); color:#fff; }
        .sidebar .nav-link i { width:23px; display:inline-block; }
        .sidebar-footer { border-top:1px solid rgba(255,255,255,.12); padding:18px 8px 0; margin-top:auto; }
        .main-area { min-width:0; flex:1; }
        .topbar { min-height:76px; padding:16px 34px; background:#fff; border-bottom:1px solid #e8ecf3; display:flex; justify-content:space-between; align-items:center; }
        .page-content { padding:30px 34px; max-width:1500px; margin:0 auto; }
        .eyebrow { color:var(--muted); font-size:.8rem; font-weight:600; letter-spacing:.04em; }
        .page-title { font-size:1.7rem; font-weight:750; letter-spacing:-.04em; }
        .surface { background:#fff; border:1px solid #e8ecf3; border-radius:16px; }
        .stat-card { padding:20px; height:100%; }
        .stat-icon { display:grid; place-items:center; width:42px; height:42px; border-radius:12px; background:#edf1ff; color:var(--blue); font-size:1.15rem; }
        .stat-value { font-size:1.8rem; font-weight:750; letter-spacing:-.04em; }
        .table > :not(caption) > * > * { padding:14px 16px; }
        .badge-soft { color:#3157d5; background:#edf1ff; }
        @media(max-width:800px) {
            .sidebar { width:76px; padding:18px 10px; }
            .sidebar-brand { justify-content:center; padding:0 0 26px; }
            .sidebar-brand .brand-text,.sidebar-label,.sidebar .nav-link span,.sidebar-footer .user-meta,.sidebar-footer .logout-label { display:none; }
            .sidebar .nav-link { text-align:center; padding:12px 6px; }
            .sidebar .nav-link i { width:auto; font-size:1.1rem; }
            .sidebar-footer { text-align:center; padding-left:0; padding-right:0; }
            .topbar { padding:14px 18px; }
            .page-content { padding:22px 16px; }
        }
        @media(max-width:480px) { .sidebar { width:62px; } .page-title { font-size:1.4rem; } }
    </style>
</head>
<body>
<div class="portal-shell">
    <aside class="sidebar">
        <a href="{{ auth()->user()->isStudent() ? route('student.dashboard') : route('admin.dashboard') }}" class="sidebar-brand">
            <span class="brand-mark">S</span><span class="brand-text">SIMS</span>
        </a>

        @if(auth()->user()->isStudent())
            <div class="sidebar-label">Student Portal</div>
            <nav class="nav flex-column">
                <a class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}" href="{{ route('student.dashboard') }}"><i class="bi bi-grid"></i><span>Overview</span></a>
                <a class="nav-link {{ request()->routeIs('student.profile') ? 'active' : '' }}" href="{{ route('student.profile') }}"><i class="bi bi-person"></i><span>My Profile</span></a>
                <a class="nav-link {{ request()->routeIs('student.enrollments.*') ? 'active' : '' }}" href="{{ route('student.enrollments.index') }}"><i class="bi bi-journal-check"></i><span>My Enrollments</span></a>
                <a class="nav-link {{ request()->routeIs('student.grades.*') ? 'active' : '' }}" href="{{ route('student.grades.index') }}"><i class="bi bi-card-list"></i><span>My Grades</span></a>
                <a class="nav-link {{ request()->routeIs('student.schedules.*') ? 'active' : '' }}" href="{{ route('student.schedules.index') }}"><i class="bi bi-calendar3"></i><span>My Class Schedule</span></a>
            </nav>
        @else
            <div class="sidebar-label">Administration</div>
            <nav class="nav flex-column">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="bi bi-grid"></i><span>Dashboard</span></a>
                @if(in_array(auth()->user()->role, ['admin', 'staff'], true))
                    <a class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}" href="{{ route('admin.students.index') }}"><i class="bi bi-people"></i><span>Students</span></a>
                    <a class="nav-link {{ request()->routeIs('admin.programs.*') ? 'active' : '' }}" href="{{ route('admin.programs.index') }}"><i class="bi bi-book"></i><span>Programs</span></a>
                    <a class="nav-link {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}" href="{{ route('admin.subjects.index') }}"><i class="bi bi-journal-bookmark"></i><span>Subjects</span></a>
                    <a class="nav-link {{ request()->routeIs('admin.year-levels.*') ? 'active' : '' }}" href="{{ route('admin.year-levels.index') }}"><i class="bi bi-layers"></i><span>Year Levels</span></a>
                    <a class="nav-link {{ request()->routeIs('admin.sections.*') ? 'active' : '' }}" href="{{ route('admin.sections.index') }}"><i class="bi bi-diagram-3"></i><span>Sections</span></a>
                    <a class="nav-link {{ request()->routeIs('admin.enrollments.*') ? 'active' : '' }}" href="{{ route('admin.enrollments.index') }}"><i class="bi bi-journal-check"></i><span>Enrollments</span></a>
                    <a class="nav-link {{ request()->routeIs('admin.grades.*') ? 'active' : '' }}" href="{{ route('admin.grades.index') }}"><i class="bi bi-card-checklist"></i><span>Gradebook</span></a>
                    <a class="nav-link {{ request()->routeIs('admin.schedules.*') ? 'active' : '' }}" href="{{ route('admin.schedules.index') }}"><i class="bi bi-calendar3"></i><span>Class Schedules</span></a>
                    @if(auth()->user()->isAdministrator())
                        <a class="nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}" href="{{ route('admin.audit-logs.index') }}"><i class="bi bi-clock-history"></i><span>Audit Logs</span></a>
                        <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}"><i class="bi bi-shield-lock"></i><span>User Management</span></a>
                    @endif
                @endif
            </nav>
        @endif

        <div class="sidebar-footer">
            <div class="d-flex align-items-center gap-2">
                <div class="brand-mark" style="width:34px;height:34px;border-radius:50%">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <div class="user-meta overflow-hidden">
                    <div class="text-white small fw-semibold text-truncate">{{ auth()->user()->name }}</div>
                    <div class="small text-capitalize" style="color:#9aa8c2">{{ auth()->user()->role }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button class="btn btn-sm btn-outline-light w-100" type="submit"><i class="bi bi-box-arrow-right"></i> <span class="logout-label">Sign out</span></button>
            </form>
        </div>
    </aside>

    <div class="main-area">
        <header class="topbar">
            <div>
                <div class="eyebrow">{{ auth()->user()->isStudent() ? 'STUDENT PORTAL' : 'ADMINISTRATION' }}</div>
                <div class="fw-semibold">@yield('topbar-title', 'Workspace')</div>
            </div>
            <div class="small muted d-none d-sm-block">{{ now()->format('l, F j, Y') }}</div>
        </header>

        <main class="page-content">
            @if(session('success'))
                <div class="alert alert-success border-0">{{ session('success') }}</div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
