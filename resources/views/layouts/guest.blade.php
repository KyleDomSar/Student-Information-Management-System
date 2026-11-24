<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sign in') | {{ config('app.name', 'SIMS') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --sims-ink: #15243b;
            --sims-muted: #718096;
            --sims-blue: #3157d5;
            --sims-blue-dark: #2446b6;
            --sims-bg: #f4f7fc;
            --sims-line: #e5eaf2;
        }
        * { box-sizing: border-box; }
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background:
                radial-gradient(circle at 12% 8%, rgba(91,125,245,.15), transparent 29rem),
                radial-gradient(circle at 92% 88%, rgba(19,139,120,.09), transparent 26rem),
                var(--sims-bg);
            color: var(--sims-ink);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .guest-nav { padding-top: 25px; padding-bottom: 25px; }
        .guest-brand { color: var(--sims-ink); text-decoration: none; display: inline-flex; align-items: center; gap: 11px; }
        .brand-mark {
            display: inline-grid;
            place-items: center;
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: linear-gradient(145deg, #5276f3, var(--sims-blue));
            color: #fff;
            font-size: 1.05rem;
            font-weight: 800;
            box-shadow: 0 7px 18px rgba(49,87,213,.22);
        }
        .guest-brand-name { font-size: 1.12rem; font-weight: 780; letter-spacing: -.035em; }
        .guest-brand-subtitle { display: block; color: var(--sims-muted); font-size: .73rem; font-weight: 500; letter-spacing: 0; }
        .auth-card {
            overflow: hidden;
            border: 1px solid rgba(224,231,242,.95);
            border-radius: 22px;
            background: rgba(255,255,255,.96);
            box-shadow: 0 22px 65px rgba(34,49,87,.09);
            transition: transform .18s ease, box-shadow .18s ease;
        }
        a.auth-card:hover { transform: translateY(-3px); box-shadow: 0 28px 72px rgba(34,49,87,.13); }
        .form-control {
            min-height: 48px;
            border-color: #dfe5ef;
            border-radius: 11px;
            color: var(--sims-ink);
        }
        .form-control:focus {
            border-color: #8299f0;
            box-shadow: 0 0 0 .2rem rgba(49,87,213,.12);
        }
        .btn-sims {
            min-height: 48px;
            border-radius: 11px;
            background: var(--sims-blue);
            border-color: var(--sims-blue);
            color: #fff;
            font-weight: 700;
            box-shadow: 0 5px 13px rgba(49,87,213,.16);
            transition: transform .16s ease, background .16s ease;
        }
        .btn-sims:hover { transform: translateY(-1px); background: var(--sims-blue-dark); border-color: var(--sims-blue-dark); color: #fff; }
        .muted { color: var(--sims-muted) !important; }
        .guest-footer { margin-top: auto; }
        :focus-visible { outline: 3px solid rgba(49,87,213,.3); outline-offset: 2px; }
        @media (max-width: 575px) {
            .guest-nav { padding-top: 18px; padding-bottom: 18px; }
            .auth-card { border-radius: 17px; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { transition-duration: .01ms !important; }
        }
    </style>
</head>
<body>
    <nav class="container guest-nav">
        <a href="{{ route('login', [], false) }}" class="guest-brand">
            <span class="brand-mark">S</span>
            <span class="guest-brand-name">SIMS<span class="guest-brand-subtitle">Student Information Management System</span></span>
        </a>
    </nav>

    <main class="container pb-5 flex-grow-1">
        @yield('content')
    </main>

    <footer class="container guest-footer text-center small muted pb-4">
        Cavite State University · College of Engineering and Information Technology
    </footer>
    </body>
</html>
