<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Feedback') - Quran Visuals</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600&family=Inter:wght@400;500;600&display=swap');
        :root {
            --bg-1: #0b0b0d;
            --bg-2: #141318;
            --bg-3: #1b1a22;
            --accent: #c28b3b;
            --accent-2: #6f4b1f;
            --text: #f2f2f2;
            --muted: #b2b0bd;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            color: var(--text);
            background: radial-gradient(circle at top left, var(--bg-3), var(--bg-1)),
                radial-gradient(circle at bottom right, var(--bg-2), var(--bg-1));
            font-family: "Inter", "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background-image:
                radial-gradient(circle at 20% 20%, rgba(255,255,255,0.04) 0, transparent 35%),
                radial-gradient(circle at 70% 30%, rgba(255,255,255,0.03) 0, transparent 40%),
                radial-gradient(circle at 40% 80%, rgba(255,255,255,0.02) 0, transparent 45%);
            pointer-events: none;
            mix-blend-mode: screen;
        }

        a { color: var(--accent); text-decoration: none; }
        a:hover { text-decoration: underline; }

        .site-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 24px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .site-nav .nav-brand {
            font-family: "Cinzel", "Playfair Display", Georgia, serif;
            font-size: 1.3rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--text);
        }

        .site-nav .nav-links {
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 0.9rem;
        }

        .site-nav .nav-links a {
            color: var(--muted);
            transition: color 0.2s;
        }

        .site-nav .nav-links a:hover,
        .site-nav .nav-links a.active {
            color: var(--text);
            text-decoration: none;
        }

        .container {
            max-width: 960px;
            margin: 0 auto;
            padding: 32px 24px;
        }

        .flash {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 24px;
            font-size: 0.9rem;
        }

        .flash-success {
            border: 1px solid rgba(74, 224, 138, 0.3);
            background: rgba(26, 122, 66, 0.2);
            color: #a0f0c0;
        }

        .flash-error {
            border: 1px solid rgba(255, 120, 120, 0.3);
            background: rgba(120, 40, 40, 0.2);
            color: #ffdede;
        }

        .btn {
            display: inline-block;
            padding: 10px 18px;
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.14);
            background: rgba(8, 9, 12, 0.7);
            color: var(--text);
            font-size: 0.9rem;
            font-family: inherit;
            cursor: pointer;
            transition: border-color 0.2s, background 0.2s;
        }

        .btn:hover { border-color: rgba(255,255,255,0.3); text-decoration: none; }

        .btn-primary {
            border-color: var(--accent);
            background: linear-gradient(120deg, rgba(194,139,59,0.18), rgba(194,139,59,0.35));
            font-weight: 600;
            letter-spacing: 0.04em;
        }

        .btn-primary:hover {
            background: linear-gradient(120deg, rgba(194,139,59,0.3), rgba(194,139,59,0.5));
        }

        .btn-danger {
            border-color: rgba(255, 100, 100, 0.4);
            background: rgba(120, 30, 30, 0.3);
            color: #ffa0a0;
        }

        .btn-sm { padding: 6px 12px; font-size: 0.8rem; }

        .form-group { margin-bottom: 18px; }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 0.85rem;
            color: var(--muted);
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.14);
            background: rgba(8, 9, 12, 0.7);
            color: var(--text);
            font-size: 0.9rem;
            font-family: inherit;
        }

        .form-group textarea { resize: vertical; min-height: 120px; }

        .form-group .error {
            color: #ff9090;
            font-size: 0.8rem;
            margin-top: 4px;
        }

        .card {
            padding: 20px;
            border-radius: 14px;
            border: 1px solid rgba(255,255,255,0.08);
            background: rgba(8, 9, 12, 0.5);
        }

        @yield('extra-css')
    </style>
</head>
<body>
    <nav class="site-nav">
        <a href="/" class="nav-brand">Quran Visuals</a>
        <div class="nav-links">
            <a href="/feedback" class="{{ request()->is('feedback*') && !request()->is('feedback/create') ? 'active' : '' }}">Feedback</a>
            <a href="/roadmap" class="{{ request()->is('roadmap') ? 'active' : '' }}">Roadmap</a>
            @auth
                <span style="color: var(--muted); font-size: 0.85rem;">{{ Auth::user()->name }}</span>
                <form method="POST" action="/logout" style="display:inline;">
                    @csrf
                    <button type="submit" style="background:none;border:none;color:var(--muted);cursor:pointer;font-size:0.85rem;font-family:inherit;">Logout</button>
                </form>
            @else
                <a href="/login">Login</a>
                <a href="/register" class="btn btn-primary btn-sm">Sign Up</a>
            @endauth
        </div>
    </nav>

    <div class="container">
        @if (session('success'))
            <div class="flash flash-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="flash flash-error">{{ session('error') }}</div>
        @endif

        @yield('content')
    </div>
</body>
</html>
