<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Smart Discussion Forum')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        :root {
            --ink: #1c2b33;
            --slate: #3d5a6c;
            --paper: #f6f4ee;
            --paper-dim: #ece8db;
            --accent: #2f6f5e;
            --accent-dark: #204b3f;
            --warn: #b3542e;
            --line: #d8d2c4;
            --seal: #a8792f;
            --seal-dim: #f2e8d5;
            --sky: #2a5a72;
            --sky-dim: #e6edf1;
            --radius: 6px;
            --sans: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            --serif: 'Iowan Old Style', 'Georgia', serif;
            --mono: ui-monospace, 'SF Mono', 'Courier New', monospace;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: var(--serif);
            background: var(--paper);
            color: var(--ink);
        }
        header.topbar {
            background: var(--ink);
            color: var(--paper);
            padding: 14px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: var(--sans);
            flex-wrap: wrap;
            gap: 8px;
        }
        header.topbar .brand { font-weight: 700; letter-spacing: .04em; text-transform: uppercase; font-size: 15px; }
        header.topbar nav { display: flex; align-items: center; flex-wrap: wrap; }
        header.topbar nav a {
            color: var(--paper);
            text-decoration: none;
            margin-left: 18px;
            font-size: 14px;
            opacity: .85;
            padding: 4px 2px;
            border-bottom: 2px solid transparent;
            transition: opacity 0.15s, border-color 0.15s;
        }
        header.topbar nav a:hover { opacity: 1; }
        header.topbar nav a.active { opacity: 1; border-bottom-color: var(--paper); font-weight: 600; }
        header.topbar nav a:focus-visible,
        .btn:focus-visible,
        input:focus-visible, textarea:focus-visible, select:focus-visible {
            outline: 2px solid var(--seal);
            outline-offset: 2px;
        }

        main { max-width: 880px; margin: 0 auto; padding: 32px 24px 80px; }
        h1, h2, h3 { font-family: var(--serif); color: var(--ink); }

        h2 {
            font-size: 20px;
            margin-top: 36px;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 1px solid var(--line);
        }

        .card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 20px 22px;
            margin-bottom: 16px;
        }

        .panel-lecturer { border-left: 4px solid var(--warn); }
        .panel-student { border-left: 4px solid var(--sky); background: var(--sky-dim); }
        .panel-create { border-left: 4px solid var(--accent-dark); }

        .btn {
            display: inline-block;
            font-family: var(--sans);
            background: var(--accent);
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: var(--radius);
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            transition: background 0.15s;
        }
        .btn:hover { background: var(--accent-dark); }
        .btn.secondary, .btn.btn-secondary {
            background: transparent;
            color: var(--accent);
            border: 1px solid var(--accent);
        }
        .btn.secondary:hover, .btn.btn-secondary:hover { background: var(--paper-dim); }
        .btn.warn { background: var(--warn); }
        .btn.warn:hover { background: #8f4224; }

        input, textarea, select {
            width: 100%;
            font-family: var(--sans);
            padding: 10px 12px;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            margin-bottom: 12px;
            font-size: 14px;
        }
        label { font-family: var(--sans); font-size: 13px; color: var(--slate); display: block; margin-bottom: 4px; }
        .eyebrow { font-family: var(--sans); text-transform: uppercase; letter-spacing: .08em; font-size: 12px; color: var(--accent); font-weight: 600; }
        .muted { color: var(--slate); font-size: 14px; font-family: var(--sans); }
        .flag { color: var(--warn); font-weight: 600; }
        .error { color: var(--warn); font-family: var(--sans); font-size: 13px; margin-top: -6px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; font-family: var(--sans); font-size: 14px; }
        th, td { text-align: left; padding: 8px 10px; border-bottom: 1px solid var(--line); }

        .tag-code {
            display: inline-block;
            font-family: var(--mono);
            font-size: 11px;
            letter-spacing: .03em;
            background: var(--paper-dim);
            color: var(--slate);
            padding: 2px 7px;
            border-radius: 3px;
        }

        .empty-state {
            color: var(--slate);
            font-style: italic;
            font-family: var(--sans);
            font-size: 14px;
        }

        .rec-list { display: flex; flex-direction: column; gap: 10px; }
        .rec-card {
            position: relative;
            background: #fff;
            border: 1px solid var(--line);
            border-left: 3px dashed var(--seal);
            border-radius: var(--radius);
            padding: 14px 52px 14px 16px;
        }
        .rec-card .rec-title {
            font-family: var(--serif);
            font-size: 16px;
            font-weight: 600;
        }
        .rec-card .rec-title a { color: var(--ink); text-decoration: none; }
        .rec-card .rec-title a:hover { color: var(--accent); }
        .rec-card .tag-code { margin-top: 6px; }
        .rec-score {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 34px;
            height: 34px;
            border: 1.5px solid var(--seal);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--mono);
            font-size: 11px;
            font-weight: 700;
            color: var(--seal);
            background: var(--seal-dim);
            transform: rotate(-6deg);
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="brand">Smart Discussion Forum</div>
        <nav>
            <a href="{{ route('dashboard') }}" class="{{ request()->is('dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="/profile" class="{{ request()->is('profile') ? 'active' : '' }}">My Profile</a>
            <a href="#" id="logoutLink">Log out</a>
        </nav>
    </header>
    <main>
        @yield('content')
    </main>
    <script>
        const apiToken = localStorage.getItem('sdf_token');
        async function api(path, options = {}) {
            const res = await fetch('/api' + path, {
                ...options,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    ...(apiToken ? { Authorization: 'Bearer ' + apiToken } : {}),
                    ...(options.headers || {}),
                },
                body: options.body ? JSON.stringify(options.body) : undefined,
            });
            if (res.status === 401) { window.location = '/'; return; }
            return res.json();
        }
        document.getElementById('logoutLink')?.addEventListener('click', async (e) => {
            e.preventDefault();
            await api('/logout', { method: 'POST' });
            localStorage.removeItem('sdf_token');
            window.location = '/';
        });
    </script>
    @yield('scripts')
</body>
</html>