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
        :root {
            color-scheme: light;
            --ink: #15243b;
            --muted: #718096;
            --brand: #3157d5;
            --brand-dark: #2446b6;
            --canvas: #f4f7fc;
            --sidebar: #14233d;
            --line: #e6ebf3;
            --surface-shadow: 0 10px 28px rgba(25, 42, 70, .055);
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            min-height: 100vh;
            margin: 0;
            background: var(--canvas);
            color: var(--ink);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            font-size: .95rem;
            -webkit-font-smoothing: antialiased;
        }

        a { text-underline-offset: 3px; }
        .skip-link {
            position: fixed;
            top: 10px;
            left: 10px;
            z-index: 2000;
            padding: 10px 14px;
            transform: translateY(-150%);
            border-radius: 10px;
            background: #fff;
            color: var(--ink);
            box-shadow: var(--surface-shadow);
        }
        .skip-link:focus { transform: translateY(0); }

        .portal-shell { min-height: 100vh; display: flex; }
        .sidebar {
            position: sticky;
            top: 0;
            align-self: flex-start;
            width: 260px;
            height: 100vh;
            height: 100dvh;
            flex: 0 0 260px;
            overflow-y: auto;
            overscroll-behavior: contain;
            padding: 22px 15px 18px;
            display: flex;
            flex-direction: column;
            color: #d7e0ef;
            background: linear-gradient(180deg, #192c4b 0%, #12213a 100%);
            border-right: 1px solid rgba(255,255,255,.06);
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,.2) transparent;
            z-index: 1020;
        }
        .sidebar::-webkit-scrollbar { width: 6px; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.2); border-radius: 20px; }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 2px 10px 26px;
            color: #fff;
            font-size: 1.16rem;
            font-weight: 750;
            letter-spacing: -.025em;
            text-decoration: none;
        }
        .brand-mark {
            display: inline-grid;
            place-items: center;
            width: 39px;
            height: 39px;
            flex: 0 0 39px;
            border-radius: 12px;
            background: linear-gradient(145deg, #5276f3, #3157d5);
            color: #fff;
            font-size: 1rem;
            font-weight: 800;
            box-shadow: 0 6px 14px rgba(49,87,213,.25);
        }
        .sidebar-label {
            padding: 13px 12px 7px;
            color: #8fa0bb;
            font-size: .66rem;
            font-weight: 750;
            letter-spacing: .12em;
            text-transform: uppercase;
        }
        .sidebar .nav { gap: 3px; }
        .sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 43px;
            margin: 1px 0;
            padding: 10px 12px;
            border: 1px solid transparent;
            border-radius: 11px;
            color: #c3cde0;
            font-size: .9rem;
            font-weight: 550;
            transition: background .18s ease, color .18s ease, transform .18s ease, border-color .18s ease;
        }
        .sidebar .nav-link i {
            display: inline-grid;
            place-items: center;
            width: 21px;
            flex: 0 0 21px;
            font-size: 1rem;
        }
        .sidebar .nav-link:hover {
            transform: translateX(2px);
            background: rgba(255,255,255,.075);
            color: #fff;
        }
        .sidebar .nav-link.active {
            border-color: rgba(255,255,255,.08);
            background: rgba(111,143,255,.19);
            color: #fff;
            box-shadow: inset 3px 0 0 #8ea6ff;
        }
        .sidebar-footer {
            margin-top: auto;
            padding: 17px 7px 0;
            border-top: 1px solid rgba(255,255,255,.12);
        }
        .sidebar-footer .user-meta { min-width: 0; }
        .sidebar-footer .user-meta .user-name {
            max-width: 158px;
            color: #fff;
            font-size: .83rem;
            font-weight: 650;
        }
        .sidebar-footer .user-meta .user-role {
            color: #9cacc6;
            font-size: .74rem;
            text-transform: capitalize;
        }
        .sidebar-footer .btn {
            min-height: 39px;
            margin-top: 14px;
            border-color: rgba(255,255,255,.28);
            color: #e7edf8;
            border-radius: 9px;
            font-weight: 600;
        }
        .sidebar-footer .btn:hover { background: rgba(255,255,255,.12); color: #fff; }

        .main-area { min-width: 0; min-height: 100vh; flex: 1 1 auto; }
        .topbar {
            position: sticky;
            top: 0;
            z-index: 1010;
            min-height: 76px;
            padding: 14px clamp(18px, 3vw, 36px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            background: rgba(255,255,255,.94);
            border-bottom: 1px solid var(--line);
            backdrop-filter: blur(14px);
        }
        .topbar .eyebrow { margin-bottom: 2px; }
        .topbar-title { color: var(--ink); font-size: .98rem; font-weight: 700; }
        .topbar-meta { display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: 14px; }
        .topbar-date { color: var(--muted); font-size: .82rem; white-space: nowrap; }
        .topbar-user {
            padding-left: 14px;
            border-left: 1px solid var(--line);
            color: var(--ink);
            font-size: .82rem;
            font-weight: 650;
        }
        .topbar-user span { display: block; color: var(--muted); font-size: .72rem; font-weight: 500; text-transform: capitalize; }

        .page-content {
            width: min(100%, 1560px);
            margin: 0 auto;
            padding: 32px clamp(18px, 3vw, 38px) 44px;
        }
        .eyebrow {
            color: var(--muted);
            font-size: .72rem;
            font-weight: 750;
            letter-spacing: .095em;
        }
        .page-title {
            margin: 0;
            color: var(--ink);
            font-size: clamp(1.5rem, 2.2vw, 1.9rem);
            font-weight: 780;
            letter-spacing: -.045em;
            line-height: 1.18;
        }
        .muted { color: var(--muted) !important; }
        .surface {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 17px;
            box-shadow: var(--surface-shadow);
        }
        .stat-card { height: 100%; padding: 22px; }
        .stat-icon {
            display: grid;
            place-items: center;
            width: 44px;
            height: 44px;
            border: 1px solid #e4eaff;
            border-radius: 13px;
            background: #eef2ff;
            color: var(--brand);
            font-size: 1.15rem;
        }
        .stat-value {
            color: var(--ink);
            font-size: 2rem;
            font-weight: 780;
            letter-spacing: -.055em;
            line-height: 1.2;
            font-variant-numeric: tabular-nums;
        }

        .btn {
            border-radius: 10px;
            font-size: .88rem;
            font-weight: 650;
            transition: transform .16s ease, box-shadow .16s ease, background .16s ease;
        }
        .btn:not(:disabled):hover { transform: translateY(-1px); }
        .btn-primary {
            background: var(--brand);
            border-color: var(--brand);
            box-shadow: 0 4px 10px rgba(49,87,213,.13);
        }
        .btn-primary:hover, .btn-primary:focus {
            background: var(--brand-dark);
            border-color: var(--brand-dark);
        }
        .btn-outline-primary { color: var(--brand); border-color: #c9d4f7; }
        .btn-outline-primary:hover { background: #edf1ff; border-color: #b6c5f3; color: var(--brand-dark); }
        .btn-light { border: 1px solid var(--line); background: #f8faff; color: #42516a; }
        .btn-light:hover { background: #eef2f8; border-color: #d9e0eb; color: var(--ink); }

        .form-label { color: #36445b; }
        .form-control, .form-select {
            min-height: 44px;
            border: 1px solid #dce3ee;
            border-radius: 10px;
            color: var(--ink);
            font-size: .9rem;
        }
        textarea.form-control { min-height: auto; }
        .form-control::placeholder { color: #9aa7b9; }
        .form-control:focus, .form-select:focus {
            border-color: #8ba2ef;
            box-shadow: 0 0 0 .2rem rgba(49,87,213,.11);
        }
        .form-text { color: var(--muted); font-size: .78rem; }
        .form-check-input:checked { background-color: var(--brand); border-color: var(--brand); }
        .form-check-input:focus { border-color: #8ba2ef; box-shadow: 0 0 0 .2rem rgba(49,87,213,.11); }

        .table { color: var(--ink); }
        .table > :not(caption) > * > * { padding: 13px 15px; }
        .table thead th {
            background: #f8faff;
            color: #65748b;
            border-bottom: 1px solid var(--line);
            font-size: .72rem;
            font-weight: 750;
            letter-spacing: .055em;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .table tbody td { border-color: #edf0f5; font-size: .87rem; }
        .table-hover tbody tr:hover > * { background: #f9fbff; }
        .badge { font-weight: 650; letter-spacing: .01em; }
        .badge-soft { color: #3157d5; background: #edf1ff; }
        .alert { border-radius: 12px; }
        .pagination { gap: 4px; }
        .pagination .page-link { border-radius: 8px !important; border-color: var(--line); color: var(--brand); }
        .pagination .active .page-link { background: var(--brand); border-color: var(--brand); }
        :focus-visible { outline: 3px solid rgba(49,87,213,.3); outline-offset: 2px; }

        @media (max-width: 800px) {
            .sidebar { width: 76px; flex-basis: 76px; padding: 18px 9px; }
            .sidebar-brand { justify-content: center; padding: 0 0 24px; }
            .sidebar-brand .brand-text, .sidebar-label, .sidebar .nav-link span,
            .sidebar-footer .user-meta, .sidebar-footer .logout-label { display: none; }
            .sidebar .nav-link { justify-content: center; padding: 11px 5px; }
            .sidebar .nav-link i { width: auto; flex-basis: auto; font-size: 1.08rem; }
            .sidebar-footer { padding-left: 0; padding-right: 0; text-align: center; }
            .sidebar-footer .d-flex { justify-content: center; }
            .sidebar-footer .btn { padding-inline: 8px; }
            .topbar { min-height: 68px; padding: 12px 18px; }
            .page-content { padding: 25px 18px 34px; }
        }
        @media (max-width: 520px) {
            .sidebar { width: 62px; flex-basis: 62px; padding-inline: 7px; }
            .sidebar .nav-link { min-height: 41px; }
            .topbar-meta { gap: 8px; }
            .topbar-user { display: none; }
            .topbar-date { font-size: .74rem; }
            .page-content { padding: 22px 14px 30px; }
            .surface { border-radius: 14px; }
            .stat-card { padding: 18px; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; }
        }
    </style>
</head>
<body>
<a class="skip-link" href="#main-content">Skip to main content</a>
<div class="portal-shell">
    <aside class="sidebar" aria-label="Main navigation">
        <a href="{{ auth()->user()->isStudent() ? route('student.dashboard', [], false) : route('admin.dashboard', [], false) }}" class="sidebar-brand" title="SIMS dashboard">
            <span class="brand-mark">S</span><span class="brand-text">SIMS</span>
        </a>

        @if(auth()->user()->isStudent())
            <div class="sidebar-label">Student Portal</div>
            <nav class="nav flex-column" aria-label="Student navigation">
                <a class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}" href="{{ route('student.dashboard', [], false) }}" title="Overview" @if(request()->routeIs('student.dashboard')) aria-current="page" @endif><i class="bi bi-grid"></i><span>Overview</span></a>
                <a class="nav-link {{ request()->routeIs('student.profile') ? 'active' : '' }}" href="{{ route('student.profile', [], false) }}" title="My Profile" @if(request()->routeIs('student.profile')) aria-current="page" @endif><i class="bi bi-person"></i><span>My Profile</span></a>
                <a class="nav-link {{ request()->routeIs('student.enrollments.*') ? 'active' : '' }}" href="{{ route('student.enrollments.index', [], false) }}" title="Enrollment History" @if(request()->routeIs('student.enrollments.*')) aria-current="page" @endif><i class="bi bi-journal-check"></i><span>Enrollment History</span></a>
                <a class="nav-link {{ request()->routeIs('student.visitor-timeouts.*') ? 'active' : '' }}" href="{{ route('student.visitor-timeouts.index', [], false) }}" title="Visitor Time-Out" @if(request()->routeIs('student.visitor-timeouts.*')) aria-current="page" @endif><i class="bi bi-clock-history"></i><span>Visitor Time-Out</span></a>
            </nav>
        @else
            <div class="sidebar-label">Workspace</div>
            <nav class="nav flex-column" aria-label="Administration navigation">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard', [], false) }}" title="Dashboard" @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif><i class="bi bi-grid"></i><span>Dashboard</span></a>

                @if(in_array(auth()->user()->role, ['admin', 'staff'], true))
                    <div class="sidebar-label">Records</div>
                    <a class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}" href="{{ route('admin.students.index', [], false) }}" title="Students" @if(request()->routeIs('admin.students.*')) aria-current="page" @endif><i class="bi bi-people"></i><span>Students</span></a>
                    <a class="nav-link {{ request()->routeIs('admin.enrollments.*') ? 'active' : '' }}" href="{{ route('admin.enrollments.index', [], false) }}" title="Enrollment Records" @if(request()->routeIs('admin.enrollments.*')) aria-current="page" @endif><i class="bi bi-journal-check"></i><span>Enrollment Records</span></a>

                    <div class="sidebar-label">Academic Setup</div>
                    <a class="nav-link {{ request()->routeIs('admin.programs.*') ? 'active' : '' }}" href="{{ route('admin.programs.index', [], false) }}" title="Programs / Courses" @if(request()->routeIs('admin.programs.*')) aria-current="page" @endif><i class="bi bi-book"></i><span>Programs / Courses</span></a>
                    <a class="nav-link {{ request()->routeIs('admin.year-levels.*') ? 'active' : '' }}" href="{{ route('admin.year-levels.index', [], false) }}" title="Year Levels" @if(request()->routeIs('admin.year-levels.*')) aria-current="page" @endif><i class="bi bi-layers"></i><span>Year Levels</span></a>
                    <a class="nav-link {{ request()->routeIs('admin.sections.*') ? 'active' : '' }}" href="{{ route('admin.sections.index', [], false) }}" title="Sections" @if(request()->routeIs('admin.sections.*')) aria-current="page" @endif><i class="bi bi-diagram-3"></i><span>Sections</span></a>

                    <div class="sidebar-label">Reports & Security</div>
                    <a class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.students', [], false) }}" title="Student Reports" @if(request()->routeIs('admin.reports.*')) aria-current="page" @endif><i class="bi bi-file-earmark-bar-graph"></i><span>Student Reports</span></a>
                    @if(auth()->user()->isAdministrator())
                        <a class="nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}" href="{{ route('admin.audit-logs.index', [], false) }}" title="Audit Logs" @if(request()->routeIs('admin.audit-logs.*')) aria-current="page" @endif><i class="bi bi-clock-history"></i><span>Audit Logs</span></a>
                        <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index', [], false) }}" title="User Management" @if(request()->routeIs('admin.users.*')) aria-current="page" @endif><i class="bi bi-shield-lock"></i><span>User Management</span></a>
                    @endif
                @endif
            </nav>
        @endif

        <div class="sidebar-footer">
            <div class="d-flex align-items-center gap-2">
                <div class="brand-mark" style="width:34px;height:34px;flex-basis:34px;border-radius:50%">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <div class="user-meta overflow-hidden">
                    <div class="user-name text-truncate">{{ auth()->user()->name }}</div>
                    <div class="user-role">{{ str_replace('_', ' ', auth()->user()->role) }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout', [], false) }}" class="mt-3">
                @csrf
                <button class="btn btn-sm btn-outline-light w-100" type="submit"><i class="bi bi-box-arrow-right"></i> <span class="logout-label">Sign out</span></button>
            </form>
        </div>
    </aside>

    <div class="main-area">
        <header class="topbar">
            <div>
                <div class="eyebrow">{{ auth()->user()->isStudent() ? 'STUDENT PORTAL' : 'ADMINISTRATION' }}</div>
                <div class="topbar-title">@yield('topbar-title', 'Workspace')</div>
            </div>
            <div class="topbar-meta">
                <div class="topbar-date"><i class="bi bi-calendar3 me-1"></i>{{ now()->format('l, F j, Y') }}</div>
                <div class="topbar-user">{{ auth()->user()->name }}<span>{{ str_replace('_', ' ', auth()->user()->role) }}</span></div>
            </div>
        </header>

        <main class="page-content" id="main-content">
            @if(session('success'))
                <div class="alert alert-success border-0 d-flex align-items-start gap-2 shadow-sm" role="status">
                    <i class="bi bi-check-circle-fill mt-1"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger border-0 d-flex align-items-start gap-2 shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                    <div>{{ session('error') }}</div>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
