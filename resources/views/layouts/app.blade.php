<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Smart Discussion Forum')</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
    
@vite(['resources/js/app.js'])
 <script src="https://js.pusher.com/8.0.1/pusher.min.js"></script>

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
        /* ---------- Global app shell: sidebar (left) + content (right) ----------
           This is the one consistent frame every page gets via
           @@extends('layouts.app'). Individual pages just @@yield('content')
           into the right-hand pane; they don't need to know the sidebar
           exists. Auth pages (login/register) opt out via a body class. */
        .app-shell { display: flex; align-items: stretch; min-height: 100vh; }
        .app-sidebar {
            width: 248px;
            flex-shrink: 0;
            background: linear-gradient(180deg, var(--ink) 0%, #142027 100%);
            color: var(--paper);
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
        }
        .app-brand {
            display: flex; align-items: center; gap: 10px;
            padding: 22px 20px 18px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-weight: 700; letter-spacing: .04em; text-transform: uppercase; font-size: 15px;
        }
        .app-brand-icon {
            font-size: 16px; width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center;
            background: rgba(47,111,94,.25); border-radius: 8px;
        }
        /* Hamburger toggle: only ever shown on mobile (see the 760px query
           below). Hidden here so it takes no space/has no effect on desktop. */
        .mobile-menu-toggle { display: none; }
        .app-nav { display: flex; flex-direction: column; padding: 4px 10px; overflow-y: auto; flex: 1; }
        .app-nav-section {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em;
            color: rgba(246,244,238,.4);
            padding: 16px 10px 6px;
        }
        .app-nav-section:first-child { padding-top: 8px; }
        .app-nav-item {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 12px;
            margin: 1px 0;
            border-radius: 8px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 14px;
            color: var(--paper);
            text-decoration: none;
            opacity: .78;
            position: relative;
            transition: opacity .15s ease, background .15s ease, transform .15s ease;
        }
        .app-nav-item .icon {
            font-size: 15px; width: 22px; height: 22px; text-align: center; flex-shrink: 0;
            display: inline-flex; align-items: center; justify-content: center;
            transition: transform .15s ease;
        }
        .app-nav-item:hover { opacity: 1; background: rgba(255,255,255,.07); transform: translateX(2px); }
        .app-nav-item:hover .icon { transform: scale(1.1); }
        .app-nav-item.active { opacity: 1; background: rgba(47,111,94,.22); font-weight: 600; }
        .app-nav-item.active::before {
            content: ''; position: absolute; left: -10px; top: 50%; transform: translateY(-50%);
            width: 3px; height: 18px; background: var(--accent); border-radius: 0 3px 3px 0;
        }
        .app-sidebar-footer {
            display: flex; align-items: center; gap: 10px;
            padding: 14px 16px;
            border-top: 1px solid rgba(255,255,255,.12);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 13px;
        }
        .app-avatar {
            width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0;
            background: var(--accent); color: #fff; font-weight: 700; font-size: 13px;
            display: flex; align-items: center; justify-content: center; overflow: hidden;
        }
        .app-sidebar-footer .app-user-info { min-width: 0; }
        .app-sidebar-footer .app-user { opacity: .9; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .app-sidebar-footer a { color: var(--paper); opacity: .6; text-decoration: none; font-size: 12px; transition: opacity .12s ease; }
        .app-sidebar-footer a:hover { opacity: 1; text-decoration: underline; }
        .app-main { flex: 1; min-width: 0; padding: 32px 24px 80px; }
        .app-main > .content-col { max-width: 880px; margin: 0 auto; }
        /* Dashboard/chat pages want to fill the available width rather than
           sit in a narrow centered column like plain content pages (forms,
           profile, etc). */
        .app-main:has(.dash-shell) .content-col,
        .app-main:has(.chat-thread) .content-col { max-width: 1400px; }
        @media (max-width: 760px) {
            .app-shell { flex-direction: column; }
            .app-sidebar { width: 100%; height: auto; position: relative; flex-direction: column; align-items: stretch; }
            .app-brand { padding: 12px 14px; display: flex; align-items: center; justify-content: space-between; }
            .mobile-menu-toggle {
                display: inline-flex; align-items: center; justify-content: center;
                width: 34px; height: 34px; border-radius: 8px; flex-shrink: 0;
                background: rgba(255,255,255,.08); border: none; color: var(--paper); cursor: pointer;
            }
            .mobile-menu-toggle:hover { background: rgba(255,255,255,.15); }
            /* Collapsed by default on mobile; JS toggles .mobile-open. Sits in
               normal flow (not an overlay) so it simply pushes content down
               rather than needing z-index/backdrop handling. */
            .app-nav { display: none; flex-direction: column; overflow-x: visible; padding: 4px 10px 10px; max-height: 70vh; overflow-y: auto; }
            .app-nav.mobile-open { display: flex; }
            .app-nav-item { padding: 11px 12px; flex-shrink: initial; }
            .app-nav-item:hover { transform: none; }
            .app-nav-item.active::before { left: -10px; top: 50%; bottom: auto; transform: translateY(-50%); width: 3px; height: 18px; border-radius: 0 3px 3px 0; }
            .app-sidebar-footer { border-top: 1px solid rgba(255,255,255,.12); padding: 10px 14px; }
            .app-main { padding: 16px 12px 60px; }
            .dash-main { padding: 14px 12px; }
        }
        /* Auth pages (login/register/rules) render full-bleed, no sidebar */
        body.auth-page .app-sidebar { display: none; }
        body.auth-page .app-main { padding: 0; }
        h1, h2, h3 { font-family: 'Iowan Old Style', Georgia, serif; color: var(--ink); }
        .card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 20px 22px;
            margin-bottom: 16px;
            box-shadow: 0 1px 2px rgba(28,43,51,.04);
            transition: box-shadow .18s ease, border-color .18s ease;
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
            font-weight: 600;
            text-decoration: none;
            transition: background .15s ease, transform .1s ease, box-shadow .15s ease;
        }
        .btn:hover { background: var(--accent-dark); transform: translateY(-1px); box-shadow: 0 4px 10px rgba(32,75,63,.25); }
        .btn:active { transform: translateY(0); box-shadow: none; }
        .btn.secondary { background: transparent; color: var(--accent); border: 1px solid var(--accent); box-shadow: none; }
        .btn.secondary:hover { background: rgba(47,111,94,.08); box-shadow: none; }
        .btn.warn { background: var(--warn); }
        .btn.warn:hover { background: #8f3f21; box-shadow: 0 4px 10px rgba(143,63,33,.25); }
        input, textarea, select {
            width: 100%;
            font-family: var(--sans);
            padding: 10px 12px;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            margin-bottom: 12px;
            font-size: 14px;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(47,111,94,.14);
        }
        /* Visible keyboard focus everywhere, not just form fields */
        a:focus-visible, button:focus-visible, .btn:focus-visible, [tabindex]:focus-visible {
            outline: 2px solid var(--accent);
            outline-offset: 2px;
        }
        label { font-family: var(--sans); font-size: 13px; color: var(--slate); display: block; margin-bottom: 4px; }
        .eyebrow { font-family: var(--sans); text-transform: uppercase; letter-spacing: .08em; font-size: 12px; color: var(--accent); font-weight: 600; }
        .muted { color: var(--slate); font-size: 14px; font-family: var(--sans); }
        .flag { color: var(--warn); font-weight: 600; }
        .error { color: var(--warn); font-family: var(--sans); font-size: 13px; margin-top: -6px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; font-family: var(--sans); font-size: 14px; }
        th, td { text-align: left; padding: 8px 10px; border-bottom: 1px solid var(--line); }
        tbody tr { transition: background .12s ease; }
        tbody tr:hover { background: #faf9f6; }

        /* --- Dashboard helpers shared across student/lecturer/admin views --- */
        .subnav {
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
            margin: 18px 0 26px;
            border-bottom: 1px solid var(--line);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        .subnav a {
            padding: 9px 14px;
            font-size: 13.5px;
            color: var(--slate);
            text-decoration: none;
            border-bottom: 2px solid transparent;
            margin-bottom: -1px;
        }
        .subnav a:hover { color: var(--ink); }
        .subnav a.active { color: var(--ink); border-bottom-color: var(--accent); font-weight: 600; }
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }
        .stat-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 14px 16px;
            transition: transform .15s ease, box-shadow .15s ease;
        }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(28,43,51,.08); }
        .stat-card .value { font-size: 26px; font-weight: 700; color: var(--ink); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        .stat-card .label { font-size: 12px; color: var(--slate); text-transform: uppercase; letter-spacing: .04em; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        /* Panel headings inside a dashboard tab - a small accent rule underneath
           ties back to the sidebar's active-item marker, so the same visual
           language (a short teal bar) means "you are here" everywhere. */
        .panel-title {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            margin: 0 0 18px; padding-bottom: 10px;
            font-size: 22px;
            border-bottom: 2px solid var(--line);
            position: relative;
        }
        .panel-title::after {
            content: ''; position: absolute; left: 0; bottom: -2px;
            width: 34px; height: 2px; background: var(--accent);
        }
        .badge {
            display: inline-block;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
            padding: 2px 8px;
            border-radius: 10px;
            background: #eef2f1;
            color: var(--accent-dark);
        }
        .badge.role-administrator { background: #fbe7e0; color: var(--warn); }
        .badge.role-lecturer { background: #e2ecfa; color: #2a5a9c; }
        .badge.role-student { background: #eef2f1; color: var(--accent-dark); }
        .empty-state {
            color: var(--slate); font-size: 14px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            padding: 22px 16px; text-align: center; border: 1px dashed var(--line); border-radius: var(--radius);
        }

        /* --- Dashboard panel shell: one panel visible at a time, chosen by
           the global sidebar's ?panel= query string (see initDashSidebar) --- */
        .dash-shell {
            margin-top: 18px;
            min-height: 70vh;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            overflow: hidden;
            background: #fff;
        }
        .dash-main { padding: 24px 28px; }
        .dash-panel { display: none; }
        .dash-panel.active { display: block; animation: panelIn .25s ease; }
        @keyframes panelIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @media (prefers-reduced-motion: reduce) {
            * { animation-duration: .001ms !important; transition-duration: .001ms !important; }
        }
    </style>
</head>
<body class="@yield('body-class')">
    @php
        $panel = request()->query('panel');
        $onDashboard = request()->is('dashboard') || request()->is('dashboard/*');
        $onAdminDash = request()->is('dashboard/admin');
    @endphp
    <div class="app-shell">
        <aside class="app-sidebar">
            <div class="app-brand">
                <span style="display:flex; align-items:center; gap:10px;"><span class="app-brand-icon">🚀</span> SDF</span>
                <button type="button" class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Open menu" aria-expanded="false">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
            </div>
            <nav class="app-nav">
                <div class="app-nav-section">Workspace</div>
                <a href="/dashboard?panel=panel-groups" data-dash-panel="panel-groups" data-role="student,lecturer,administrator" class="app-nav-item {{ ($onDashboard && !$onAdminDash && ($panel === 'panel-groups' || !$panel)) || ($onAdminDash && $panel === 'panel-groups') ? 'active' : '' }}">
                    <span class="icon">👥</span> Groups
                </a>
                <!--a href="/dashboard?panel=panel-groups" data-dash-panel="panel-groups" data-role="student,lecturer,administrator" class="app-nav-item {{ (request()->is('groups/*') || request()->is('topics/*')) ? 'active' : '' }}">
                    <span class="icon">💬</span> Topics
                </a-->
                <a href="/dashboard?panel=panel-group-admin" data-dash-panel="panel-group-admin" id="navGroupAdmin" style="display:none;" class="app-nav-item {{ $panel === 'panel-group-admin' ? 'active' : '' }}">
                    <span class="icon">🛡️</span> Group Admin
                </a>

                <div class="app-nav-section" data-role="student,lecturer" style="display:none;" id="navSectionLearning">Learning</div>
                <a href="/dashboard?panel=panel-grades" data-dash-panel="panel-grades" data-role="student" style="display:none;" class="app-nav-item {{ $panel === 'panel-grades' ? 'active' : '' }}">
                    <span class="icon">🎓</span> My Grades
                </a>
                <a href="/dashboard?panel=panel-quizzes" data-dash-panel="panel-quizzes" data-role="student,lecturer" style="display:none;" class="app-nav-item {{ (request()->is('quizzes/*') || $panel === 'panel-quizzes') ? 'active' : '' }}">
                    <span class="icon">📝</span> Quizzes
                </a>
                <a href="/dashboard?panel=panel-criteria" data-dash-panel="panel-criteria" data-role="lecturer" style="display:none;" class="app-nav-item {{ $panel === 'panel-criteria' ? 'active' : '' }}">
                    <span class="icon">📊</span> Scoring Criteria
                </a>
                <a href="/dashboard?panel=panel-recommendations" data-dash-panel="panel-recommendations" data-role="student" style="display:none;" class="app-nav-item {{ $panel === 'panel-recommendations' ? 'active' : '' }}">
                    <span class="icon">✨</span> Recommended
                </a>
                <a href="/dashboard?panel=panel-notifications" data-dash-panel="panel-notifications" data-role="student,lecturer" style="display:none;" class="app-nav-item {{ $panel === 'panel-notifications' ? 'active' : '' }}">
                    <span class="icon">🔔</span> Notifications
                </a>

                <div class="app-nav-section" data-role="administrator" style="display:none;" id="navSectionAdmin">Administration</div>
                <a href="/dashboard?panel=panel-overview" data-dash-panel="panel-overview" data-role="administrator" style="display:none;" class="app-nav-item {{ $onAdminDash && ($panel === 'panel-overview' || !$panel) ? 'active' : '' }}">
                    <span class="icon">📈</span> System Overview
                </a>
                <a href="/dashboard?panel=panel-warnings" data-dash-panel="panel-warnings" data-role="administrator" style="display:none;" class="app-nav-item {{ $panel === 'panel-warnings' ? 'active' : '' }}">
                    <span class="icon">⚠️</span> Inactivity Warnings
                </a>
                <a href="/admin/users" data-role="administrator" style="display:none;" class="app-nav-item {{ request()->is('admin/users') ? 'active' : '' }}">
                    <span class="icon">🔑</span> Manage Users
                </a>

                <div class="app-nav-section">Account</div>
                <a href="/profile" data-role="student,lecturer,administrator" class="app-nav-item {{ request()->is('profile') ? 'active' : '' }}">
                    <span class="icon">👤</span> My Profile
                </a>
            </nav>
            <div class="app-sidebar-footer">
                <div class="app-avatar" id="sidebarAvatar">
                    <img id="sidebarAvatarImg" src="" alt="" style="display:none; width:100%; height:100%; object-fit:cover; border-radius:50%;">
                    <span id="sidebarAvatarInitials">?</span>
                </div>
                <div class="app-user-info">
                    <div class="app-user" id="sidebarUserName">&nbsp;</div>
                    <a href="#" id="logoutLink">Log out</a>
                </div>
            </div>
        </aside>
        <main class="app-main">
            <div class="content-col">
                @yield('content')
            </div>
        </main>
    </div>
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
        // Mobile hamburger menu: nav sits collapsed in normal document flow
        // (see the 760px query) and this just toggles it open/closed. No-op
        // on desktop since the button is display:none there.
        document.getElementById('mobileMenuToggle')?.addEventListener('click', () => {
            const nav = document.querySelector('.app-nav');
            const btn = document.getElementById('mobileMenuToggle');
            if (!nav || !btn) return;
            const isOpen = nav.classList.toggle('mobile-open');
            btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
        document.querySelectorAll('.app-nav-item').forEach(item => {
            item.addEventListener('click', () => {
                document.querySelector('.app-nav')?.classList.remove('mobile-open');
                document.getElementById('mobileMenuToggle')?.setAttribute('aria-expanded', 'false');
            });
        });

        document.getElementById('logoutLink')?.addEventListener('click', async (e) => {
            e.preventDefault();
            await api('/logout', { method: 'POST' });
            localStorage.removeItem('sdf_token');
            window.location = '/';
        });

        // Shared current-user/role lookup. Every dashboard page calls this
        // once on load rather than re-implementing its own /me + role logic.
        // NOTE: this only decides what the *page* shows - the real
        // enforcement always happens server-side (role:* middleware / the
        // per-group authorization check in StatisticsController). Hiding a
        // nav link or section here is a UX convenience, not a security
        // boundary, exactly like the rest of this app's API calls.
        window.CURRENT_USER = null;
        window.CURRENT_ROLE = 'student'; // 'student' | 'lecturer' | 'administrator'

        async function loadCurrentUser() {
            const me = await api('/me');
            if (!me) return null;
            window.CURRENT_USER = me;

            const roleNames = (me.roles || []).map(r => r.role_name);
            window.CURRENT_ROLE = roleNames.includes('Administrator') ? 'administrator'
                : roleNames.includes('Lecturer') ? 'lecturer'
                : 'student';

            document.querySelectorAll('[data-role]').forEach(el => {
                const roles = el.dataset.role.split(',');
                el.style.display = roles.includes(window.CURRENT_ROLE) ? '' : 'none';
            });

            // Rewrite sidebar links that point at a dashboard panel so they
            // go straight to this user's actual dashboard in one hop
            // (e.g. /dashboard/lecturer?panel=panel-quizzes) instead of
            // through the /dashboard role-detection bounce page. The bounce
            // still exists and still works (e.g. old bookmarks), this is
            // just a faster, more reliable path once we already know the role.
            const dashboardHome = window.CURRENT_ROLE === 'administrator' ? '/dashboard/admin'
                : window.CURRENT_ROLE === 'lecturer' ? '/dashboard/lecturer'
                : '/dashboard/student';
            document.querySelectorAll('a[data-dash-panel]').forEach(el => {
                el.href = dashboardHome + '?panel=' + el.dataset.dashPanel;
            });

            const nameEl = document.getElementById('sidebarUserName');
            const displayName = me.full_name || me.name || '';
            if (nameEl) nameEl.textContent = displayName;

            const avatarEl = document.getElementById('sidebarAvatar');
            const initials = displayName.trim().split(/\s+/).slice(0, 2).map(w => w[0]).join('').toUpperCase() || '?';
            updateSidebarAvatar(me.profile_picture ? ('/storage/' + me.profile_picture) : null, initials);

            return me;
        }

        // Shared so the profile page can call this the instant an upload
        // succeeds, instead of waiting for a full page reload to see it
        // reflected in the sidebar (which is shared across every dashboard).
        window.updateSidebarAvatar = function (imageUrl, initials) {
            const imgEl = document.getElementById('sidebarAvatarImg');
            const initialsEl = document.getElementById('sidebarAvatarInitials');
            if (!imgEl || !initialsEl) return;
            if (imageUrl) {
                imgEl.src = imageUrl;
                imgEl.style.display = 'block';
                initialsEl.style.display = 'none';
            } else {
                imgEl.style.display = 'none';
                initialsEl.style.display = '';
                if (initials) initialsEl.textContent = initials;
            }
        };
        // Shows the matching .dash-panel for whichever nav item was clicked
        // in the *global* sidebar (layouts/app.blade.php), which links to
        // pages like /dashboard?panel=panel-quizzes. This used to also wire
        // up an in-page sidebar, but that's gone now - the global sidebar is
        // the only navigation, so this just has to pick the right panel.
        function initDashSidebar(root = document, defaultPanel = null) {
            const panels = Array.from(root.querySelectorAll('.dash-panel'));
            if (!panels.length) return;
            

            function activate(targetId) {
                panels.forEach(p => p.classList.toggle('active', p.id === targetId));
            }

            const requestedPanel = new URLSearchParams(window.location.search).get('panel');
            const hasRequested = requestedPanel && panels.some(p => p.id === requestedPanel);
            if (hasRequested) activate(requestedPanel);
            else if (defaultPanel && panels.some(p => p.id === defaultPanel)) activate(defaultPanel);
            else activate(panels[0].id);
        }

        // The sidebar needs to know the user's role on *every* page (not
        // just dashboard pages) so it can show the right links and rewrite
        // them to the direct one-hop URL. Pages that also call
        // loadCurrentUser() themselves (dashboards) just get a second,
        // harmless /me lookup.
        if (!document.body.classList.contains('auth-page') && localStorage.getItem('sdf_token')) {
            loadCurrentUser();
        }
    </script>
    @yield('scripts')
</body>
</html>
