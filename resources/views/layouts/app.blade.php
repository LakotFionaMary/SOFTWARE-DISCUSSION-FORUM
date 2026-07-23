<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Smart Discussion Forum')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
@vite(['resources/js/app.js'])
 

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
/* for notifications */
        .notif-card {
    display: flex; align-items: flex-start; gap: 12px;
    padding: 14px 16px; border: 1px solid var(--line); border-radius: 10px;
    background: #fff; margin-bottom: 10px; cursor: pointer;
    border-left: 3px solid transparent;
    transition: box-shadow .15s ease, transform .15s ease, border-color .15s ease;
}
.notif-card:hover { box-shadow: 0 3px 12px rgba(0,0,0,0.08); transform: translateY(-1px); }
.notif-card.unread { background: #fafcfb; }

.notif-icon {
    width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; font-size: 16px;
    background: #eef2f1; color: var(--accent);
}

/* Post / Reply — blue family */
.notif-card.post .notif-icon, .notif-card.reply .notif-icon { background: #dbeafe; color: #1d4ed8; }
.notif-card.post.unread, .notif-card.reply.unread { border-left-color: #1d4ed8; }

/* Quiz — amber family */
.notif-card.quiz .notif-icon { background: #fef3c7; color: #b45309; }
.notif-card.quiz.unread { border-left-color: #b45309; }

/* Flag / moderation — red family */
.notif-card.flag .notif-icon { background: #fee2e2; color: #dc2626; }
.notif-card.flag.unread { border-left-color: #dc2626; }

/* Warning — orange family */
.notif-card.warning .notif-icon { background: #ffedd5; color: #c2410c; }
.notif-card.warning.unread { border-left-color: #c2410c; }

/* Blacklist — dark/severe */
.notif-card.blacklist .notif-icon { background: #ede9fe; color: #6d28d9; }
.notif-card.blacklist.unread { border-left-color: #6d28d9; }

.notif-body { flex: 1; min-width: 0; }
.notif-title { font-weight: 600; font-size: 14px; margin-bottom: 2px; }
.notif-message { font-size: 13.5px; color: var(--slate); }
.notif-time { font-size: 12px; color: var(--slate); margin-top: 4px; }
.notif-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--accent); flex-shrink: 0; margin-top: 6px; }

.nav-badge {
    background: #dc2626; color: #fff; font-size: 11px; font-weight: 700;
    min-width: 18px; height: 18px; border-radius: 9px; padding: 0 5px;
    display: none; align-items: center; justify-content: center; margin-left: auto;
}
.nav-badge.show { display: inline-flex; }
        /* ---------- Global top bar: full-width strip above the shell ----------
           Cream, same tone as the page background, so it reads as part of
           the canvas rather than a separate colored banner. Holds the brand
           on the left and the signed-in user's welcome message on the
           right - both used to live lower down (sidebar brand row / a
           per-dashboard <h1>) and are now consolidated up here so every
           page gets the same top-of-screen chrome. */
        .app-topbar {
            display: flex; align-items: center; justify-content: space-between;
            gap: 16px;
            padding: 16px 28px;
            background: var(--paper);
            border-bottom: 1px solid var(--line);
            position: sticky;
            top: 0;
            z-index: 40;
        }
        .app-topbar-brand {
            display: flex; align-items: center; gap: 10px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-weight: 800; letter-spacing: .03em; text-transform: uppercase;
            font-size: 17px;
            color: var(--ink);
        }
        .app-topbar-brand-icon {
            font-size: 16px; width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;
            background: rgba(47,111,94,.14); border-radius: 8px;
            flex-shrink: 0;
        }
        .app-topbar-welcome {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-weight: 700;
            font-size: 15px;
            color: var(--warn);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        @media (max-width: 760px) {
            .app-topbar { padding: 12px 16px; }
            .app-topbar-brand { font-size: 14px; }
            .app-topbar-welcome { font-size: 13px; }
        }
        /* ---------- Global app shell: sidebar (left) + content (right) ----------
           This is the one consistent frame every page gets via
           @@extends('layouts.app'). Individual pages just @@yield('content')
           into the right-hand pane; they don't need to know the sidebar
           exists. Auth pages (login/register) opt out via a body class. */
        .app-shell { display: flex; align-items: stretch; min-height: calc(100vh - 65px); }
        .app-sidebar {
            width: 248px;
            flex-shrink: 0;
            background: linear-gradient(180deg, var(--ink) 0%, #142027 100%);
            color: var(--paper);
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 65px;
            height: calc(100vh - 65px);
        }
        /* Hamburger toggle: only ever shown on mobile (see the 760px query
           below). Hidden here so it takes no space/has no effect on desktop. */
        .mobile-menu-toggle {
            display: none;
            flex-shrink: 0;
            width: 38px; height: 38px;
            align-items: center; justify-content: center;
            background: rgba(28,43,51,.06);
            border: 1px solid transparent;
            border-radius: 8px;
            font-size: 18px;
            color: var(--ink);
            cursor: pointer;
            padding: 0;
            transition: background .15s ease, transform .1s ease;
        }
        .mobile-menu-toggle:hover { background: rgba(28,43,51,.1); }
        .mobile-menu-toggle:active { transform: scale(.94); }
        /* Dimmed backdrop behind the mobile drawer; click to dismiss */
        .sidebar-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(15,22,26,.45);
            z-index: 90;
            opacity: 0;
            transition: opacity .2s ease;
        }
        .sidebar-overlay.show { display: block; opacity: 1; }
        /* Small header inside the sidebar, mobile-only: repeats the brand
           and gives an explicit close (✕) affordance for the drawer. */
        .app-sidebar-mobile-header { display: none; }
        .app-nav { display: flex; flex-direction: column; padding: 18px 10px 4px; overflow-y: auto; flex: 1; }
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
            display: flex; align-items: center; justify-content: center;
        }
        .app-sidebar-footer .app-user-info { min-width: 0; }
        .app-sidebar-footer .app-user { opacity: .9; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .app-sidebar-footer a { color: var(--paper); opacity: .6; text-decoration: none; font-size: 12px; transition: opacity .12s ease; }
        .app-sidebar-footer a:hover { opacity: 1; text-decoration: underline; }
        .app-main { flex: 1; min-width: 0; padding: 0; }
        /* content-col is the one wrapper every page's @@yield('content') sits
           in (see the <main> markup below). Making IT the flush white panel
           - instead of the old padded, narrower, centered column - means
           every page gets a full-bleed white area that starts exactly where
           the top bar ends and touches the sidebar, the right edge, and the
           bottom of the screen; only the top bar stays cream. */
        .app-main > .content-col {
            background: #fff;
            min-height: calc(100vh - 65px);
            border-left: 1px solid var(--line);
            padding: 32px 28px 60px;
        }
        /* Dashboard/chat pages already build their own flush white box
           (.dash-shell / .chat-thread) for the panel-switching UI, so let
           content-col pass through untouched there to avoid a white-on-white
           double border. */
        .app-main:has(.dash-shell) > .content-col,
        .app-main:has(.chat-thread) > .content-col {
            background: transparent;
            min-height: 0;
            border-left: none;
            padding: 0;
        }
        .app-main:has(.dash-shell) .dash-shell,
        .app-main:has(.chat-thread) .chat-thread {
            margin-top: 0;
            border: none;
            border-left: 1px solid var(--line);
            border-radius: 0;
            min-height: calc(100vh - 65px);
        }
        @media (max-width: 760px) {
            .mobile-menu-toggle { display: inline-flex; }
            .app-shell { flex-direction: column; }
            /* The sidebar becomes a fixed-position drawer that slides in
               from the left over the content, rather than squeezing into
               the page as a horizontal strip. Closed by default. */
            .app-sidebar {
                position: fixed;
                top: 0; left: 0;
                width: 82%;
                max-width: 300px;
                height: 100vh;
                flex-direction: column;
                align-items: stretch;
                z-index: 100;
                transform: translateX(-100%);
                transition: transform .25s ease;
                box-shadow: 4px 0 28px rgba(0,0,0,.28);
            }
            .app-sidebar.mobile-open { transform: translateX(0); }
            .app-sidebar-mobile-header {
                display: flex; align-items: center; justify-content: space-between;
                gap: 10px;
                padding: 16px 16px 14px;
                border-bottom: 1px solid rgba(255,255,255,.12);
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
                font-weight: 800; letter-spacing: .03em; text-transform: uppercase;
                font-size: 14px;
                color: var(--paper);
            }
            .app-sidebar-mobile-header .close-btn {
                width: 30px; height: 30px;
                display: inline-flex; align-items: center; justify-content: center;
                background: rgba(255,255,255,.08);
                border: none; border-radius: 7px;
                color: var(--paper);
                font-size: 15px;
                cursor: pointer;
                opacity: .8;
                transition: opacity .15s ease, background .15s ease;
            }
            .app-sidebar-mobile-header .close-btn:hover { opacity: 1; background: rgba(255,255,255,.16); }
            .app-nav { flex-direction: column; overflow-x: visible; overflow-y: auto; padding: 10px 10px 4px; }
            .app-nav-section { display: block; }
            .app-nav-item { padding: 11px 12px; flex-shrink: 0; }
            .app-nav-item:hover { transform: none; }
            /* Keep the vertical accent bar (desktop style) rather than the
               old bottom-edge bar, since the drawer is vertical again. */
            .app-nav-item.active::before { left: -10px; top: 50%; bottom: auto; transform: translateY(-50%); width: 3px; height: 18px; border-radius: 0 3px 3px 0; }
            .app-sidebar-footer { border-top: 1px solid rgba(255,255,255,.12); padding: 14px 16px; }
        }
        /* Auth pages (login/register/rules) render full-bleed, no sidebar */
        body.auth-page .app-topbar { display: none; }
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
    <div class="app-topbar">
        <button type="button" class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Open menu" aria-expanded="false" aria-controls="appSidebar">☰</button>
        <div class="app-topbar-brand"><span class="app-topbar-brand-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" className="size-6">
  <path d="M11.7 2.805a.75.75 0 0 1 .6 0A60.65 60.65 0 0 1 22.83 8.72a.75.75 0 0 1-.231 1.337 49.948 49.948 0 0 0-9.902 3.912l-.003.002c-.114.06-.227.119-.34.18a.75.75 0 0 1-.707 0A50.88 50.88 0 0 0 7.5 12.173v-.224c0-.131.067-.248.172-.311a54.615 54.615 0 0 1 4.653-2.52.75.75 0 0 0-.65-1.352 56.123 56.123 0 0 0-4.78 2.589 1.858 1.858 0 0 0-.859 1.228 49.803 49.803 0 0 0-4.634-1.527.75.75 0 0 1-.231-1.337A60.653 60.653 0 0 1 11.7 2.805Z" />
  <path d="M13.06 15.473a48.45 48.45 0 0 1 7.666-3.282c.134 1.414.22 2.843.255 4.284a.75.75 0 0 1-.46.711 47.87 47.87 0 0 0-8.105 4.342.75.75 0 0 1-.832 0 47.87 47.87 0 0 0-8.104-4.342.75.75 0 0 1-.461-.71c.035-1.442.121-2.87.255-4.286.921.304 1.83.634 2.726.99v1.27a1.5 1.5 0 0 0-.14 2.508c-.09.38-.222.753-.397 1.11.452.213.901.434 1.346.66a6.727 6.727 0 0 0 .551-1.607 1.5 1.5 0 0 0 .14-2.67v-.645a48.549 48.549 0 0 1 3.44 1.667 2.25 2.25 0 0 0 2.12 0Z" />
  <path d="M4.462 19.462c.42-.419.753-.89 1-1.395.453.214.902.435 1.347.662a6.742 6.742 0 0 1-1.286 1.794.75.75 0 0 1-1.06-1.06Z" />
</svg>
        </span> Smart Discussion Forum</div>
        <div class="app-topbar-welcome" id="topbarWelcome">&nbsp;</div>
    </div>
    <div class="app-shell">
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <aside class="app-sidebar" id="appSidebar">
            <div class="app-sidebar-mobile-header">
                Menu
                <button type="button" class="close-btn" id="sidebarCloseBtn" aria-label="Close menu">✕</button>
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
                <a href="/dashboard?panel=panel-notifications" data-dash-panel="panel-notifications" data-role="student,lecturer" style="display:none;" class="app-nav-item {{ $panel === 'panel-notifications' ? 'active' : '' }}" onclick="markNotificationsSeen()">
    <span class="icon">🔔</span> Notifications
    <span class="nav-badge" id="notifBadge"></span>
</a>

                <div class="app-nav-section" data-role="administrator" style="display:none;" id="navSectionAdmin">Administration</div>
                <a href="/dashboard?panel=panel-overview" data-dash-panel="panel-overview" data-role="administrator" style="display:none;" class="app-nav-item {{ $onAdminDash && ($panel === 'panel-overview' || !$panel) ? 'active' : '' }}">
                    <span class="icon">📈</span> System Overview
                </a>
                <a href="/dashboard?panel=panel-warnings" data-dash-panel="panel-warnings" data-role="administrator" style="display:none;" class="app-nav-item {{ $panel === 'panel-warnings' ? 'active' : '' }}">
                    <span class="icon">⚠️</span> Inactivity Warnings
                </a>
                <a href="/dashboard?panel=panel-blacklists" data-dash-panel="panel-blacklists" data-role="administrator" style="display:none;" class="app-nav-item {{ $panel === 'panel-blacklists' ? 'active' : '' }}">
                    <span class="icon">🚫</span> Blacklisted Users
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
                <div class="app-avatar" id="sidebarAvatar">?</div>
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
/* for notifications */
         function notifIconMeta(type) {
    const t = (type || '').toLowerCase();
    if (t.includes('quiz'))       return { icon: '📝', cls: 'quiz' };
    if (t.includes('blacklist'))  return { icon: '🔒', cls: 'blacklist' };
    if (t.includes('warning'))    return { icon: '⚠️', cls: 'warning' };
    if (t === 'reply')            return { icon: '↩️', cls: 'reply' };
    if (t.includes('new post'))   return { icon: '💬', cls: 'post' };
    if (t.includes('general'))    return { icon: '🚩', cls: 'flag' }; // your flag notifications use type 'General'
    return { icon: '🔔', cls: '' };

}

function relativeTime(dt) {
    if (!dt) return '';
    const mins = Math.floor((Date.now() - new Date(dt).getTime()) / 60000);
    if (mins < 1) return 'just now';
    if (mins < 60) return `${mins}m ago`;
    const hrs = Math.floor(mins / 60);
    if (hrs < 24) return `${hrs}h ago`;
    const days = Math.floor(hrs / 24);
    if (days < 7) return `${days}d ago`;
    return new Date(dt).toLocaleDateString();
}

function updateNotifBadge(count) {
    const badge = document.getElementById('notifBadge');
    if (!badge) return;
    if (count > 0) {
        badge.textContent = count > 9 ? '9+' : count;
        badge.classList.add('show');
    } else {
        badge.classList.remove('show');
    }
}
window.updateNotifBadge = updateNotifBadge;

async function refreshNotifBadge() {
    if (!localStorage.getItem('sdf_token')) return;
    const data = await api('/notifications/unread-count');
    updateNotifBadge(data?.unread_count ?? 0);
}
window.refreshNotifBadge = refreshNotifBadge;

window.markNotificationsSeen = async function () {
    await api('/notifications/read-all', { method: 'PATCH', body: {} });
    updateNotifBadge(0);
};

/* ---------- Live push (optional layer on top of polling) ---------- */
let _notifChannelJoined = false;
let _notifEchoWaitAttempts = 0;

function initNotificationChannel() {
    if (_notifChannelJoined) return;
    if (!window.CURRENT_USER) return;

    if (typeof window.Echo === 'undefined') {
        _notifEchoWaitAttempts++;
        if (_notifEchoWaitAttempts > 20) {
            console.warn('Echo never loaded after 10s - relying on polling only this session.');
            return;
        }
        setTimeout(initNotificationChannel, 500);
        return;
    }

    _notifChannelJoined = true;
    window.Echo.private(`user.${window.CURRENT_USER.user_id}`)
        .listen('.notification.new', (e) => {
            const badge = document.getElementById('notifBadge');
            const current = (badge && badge.classList.contains('show')) ? (parseInt(badge.textContent, 10) || 0) : 0;
            updateNotifBadge(current + 1);

            if (typeof window.prependLiveNotification === 'function') {
                window.prependLiveNotification(e);
            }
        })
        .error((error) => {
            console.error('Notification channel subscription error:', error);
        });
}
        // Mobile hamburger menu: the sidebar itself becomes a fixed drawer
        // (see the 760px query) that slides in over the content, with a
        // dimmed overlay behind it. No-op on desktop since the toggle
        // button is display:none there.
        (function () {
            const toggleBtn = document.getElementById('mobileMenuToggle');
            const closeBtn = document.getElementById('sidebarCloseBtn');
            const sidebar = document.getElementById('appSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (!sidebar || !overlay) return;

            function openSidebar() {
                sidebar.classList.add('mobile-open');
                overlay.classList.add('show');
                toggleBtn?.setAttribute('aria-expanded', 'true');
                document.body.style.overflow = 'hidden';
            }
            function closeSidebar() {
                sidebar.classList.remove('mobile-open');
                overlay.classList.remove('show');
                toggleBtn?.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            }
            toggleBtn?.addEventListener('click', () => {
                sidebar.classList.contains('mobile-open') ? closeSidebar() : openSidebar();
            });
            closeBtn?.addEventListener('click', closeSidebar);
            overlay.addEventListener('click', closeSidebar);
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') closeSidebar();
            });
            document.querySelectorAll('.app-nav-item').forEach(item => {
                item.addEventListener('click', closeSidebar);
            });
        })();

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

            const topbarWelcomeEl = document.getElementById('topbarWelcome');
            if (topbarWelcomeEl) topbarWelcomeEl.textContent = displayName ? `Welcome, ${displayName}` : '';

            const avatarEl = document.getElementById('sidebarAvatar');
            if (avatarEl && displayName) {
                const initials = displayName.trim().split(/\s+/).slice(0, 2).map(w => w[0]).join('').toUpperCase();
                avatarEl.textContent = initials || '?';
            }

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
        //changed to accommodate notifications
       if (!document.body.classList.contains('auth-page') && localStorage.getItem('sdf_token')) {
    loadCurrentUser().then(initNotificationChannel);
    refreshNotifBadge();
    setInterval(refreshNotifBadge, 20000); // safety net if the socket drops or never connects
}
    </script>
    @yield('scripts')
</body>
</html>
