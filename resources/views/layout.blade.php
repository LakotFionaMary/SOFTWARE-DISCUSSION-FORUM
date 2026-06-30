<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SDF Platform')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --brand:       #4f46e5;
            --brand-dark:  #4338ca;
            --brand-light: #818cf8;
            --surface:     #ffffff;
            --bg:          #f1f5f9;
            --sidebar-bg:  #0f172a;
            --text-primary:#1e293b;
            --text-muted:  #64748b;
            --border:      #e2e8f0;
            --emerald:     #10b981;
            --amber:       #f59e0b;
            --rose:        #ef4444;
            --nav-h:       64px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text-primary);
            min-height: 100vh;
        }

        /* ══════════ NAVBAR ══════════ */
        .navbar {
            height: var(--nav-h);
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            position: sticky;
            top: 0;
            z-index: 999;
            box-shadow: 0 2px 16px rgba(79,70,229,0.35);
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            text-decoration: none;
        }

        .nav-brand-icon {
            width: 36px; height: 36px;
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800;
            font-size: 0.95rem;
            color: white;
            letter-spacing: -0.5px;
            backdrop-filter: blur(4px);
        }

        .nav-brand-text {
            color: white;
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        .nav-links {
            list-style: none;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .nav-item {
            color: rgba(255,255,255,0.82);
            text-decoration: none;
            padding: 0.45rem 0.9rem;
            border-radius: 8px;
            font-size: 0.88rem;
            font-weight: 500;
            transition: background 0.18s, color 0.18s;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            white-space: nowrap;
        }

        .nav-item:hover { background: rgba(255,255,255,0.18); color: white; }
        .nav-item.active { background: rgba(255,255,255,0.22); color: white; }

        .nav-item svg { width: 15px; height: 15px; opacity: 0.85; }

        .badge {
            background: var(--amber);
            color: #451a03;
            font-size: 0.65rem;
            padding: 1px 6px;
            border-radius: 999px;
            font-weight: 700;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-user {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.35rem 0.7rem;
            background: rgba(255,255,255,0.12);
            border-radius: 8px;
            color: white;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .avatar {
            width: 28px; height: 28px;
            background: rgba(255,255,255,0.3);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            color: white;
        }

        .logout-btn {
            background: rgba(239,68,68,0.15);
            border: 1px solid rgba(239,68,68,0.35);
            color: rgba(255,255,255,0.88);
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            font-family: 'Inter', sans-serif;
            padding: 0.4rem 0.85rem;
            border-radius: 8px;
            transition: background 0.18s;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .logout-btn:hover { background: rgba(239,68,68,0.28); color: white; }

        .hamburger {
            display: none;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0.3rem;
        }

        .hamburger span {
            display: block;
            width: 22px; height: 2px;
            background: white;
            border-radius: 2px;
            transition: all 0.25s;
        }

        /* ══════════ MOBILE ══════════ */
        @media (max-width: 768px) {
            .hamburger { display: flex; }
            .nav-user-name { display: none; }

            .nav-links {
                display: none;
                flex-direction: column;
                position: absolute;
                top: var(--nav-h);
                left: 0; right: 0;
                background: linear-gradient(180deg, #4f46e5, #4338ca);
                padding: 0.75rem 1rem;
                gap: 0.25rem;
                box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            }

            .nav-links.open { display: flex; }
            .nav-item { width: 100%; }
            .nav-right { gap: 0.35rem; }
        }

        /* ══════════ PAGE SHELL ══════════ */
        .page-shell {
            min-height: calc(100vh - var(--nav-h));
        }

        /* ══════════ CARD (auth pages) ══════════ */
        .page-content {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - var(--nav-h));
            padding: 2rem;
        }

        .card {
            background: var(--surface);
            padding: 2rem;
            border-radius: 14px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 420px;
        }

        .card h2 {
            margin-bottom: 1.5rem;
            text-align: center;
            color: var(--text-primary);
            font-weight: 700;
        }

        .form-group { margin-bottom: 1rem; }

        .form-group label {
            display: block;
            margin-bottom: 0.4rem;
            color: var(--text-muted);
            font-size: 0.875rem;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 0.6rem 0.9rem;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            transition: border-color 0.18s;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(79,70,229,0.12);
        }

        .btn {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            cursor: pointer;
            margin-top: 0.5rem;
            transition: opacity 0.18s, transform 0.1s;
        }

        .btn:hover { opacity: 0.92; transform: translateY(-1px); }
        .btn:active { transform: translateY(0); }

        .errors {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            border-radius: 8px;
            padding: 0.75rem;
            margin-bottom: 1rem;
            color: #b91c1c;
            font-size: 0.875rem;
        }

        .link-text {
            text-align: center;
            margin-top: 1rem;
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        .link-text a { color: var(--brand); text-decoration: none; font-weight: 500; }
        .link-text a:hover { text-decoration: underline; }
    </style>
</head>
<body>

    @include('include.header')

    <div class="page-shell">
        @yield('page-content')

        {{-- fallback for auth pages using old card layout --}}
        @hasSection('content')
        <div class="page-content">
            <div class="card">
                <h2>@yield('card-title')</h2>
                @if($errors->any())
                    <div class="errors">
                        <ul style="list-style:none;">
                            @foreach($errors->all() as $error)
                                <li>• {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @yield('content')
            </div>
        </div>
        @endif
    </div>

    <script>
        function toggleMenu() {
            document.querySelector('.nav-links').classList.toggle('open');
        }
    </script>

    @stack('scripts')
</body>
</html>
