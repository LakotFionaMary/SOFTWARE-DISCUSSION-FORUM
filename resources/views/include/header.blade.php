{{-- resources/views/include/header.blade.php --}}
<nav class="navbar">

    {{-- Brand --}}
    <a href="/" class="nav-brand">
        <div class="nav-brand-icon">SDF</div>
        <span class="nav-brand-text">🚀 SOFTWARE DISCUSSION FORUM</span>
    </a>

    {{-- Hamburger (mobile) --}}
    <button class="hamburger" onclick="toggleMenu()" aria-label="Toggle menu">
        <span></span><span></span><span></span>
    </button>

    <ul class="nav-links">

        {{-- GUEST: only Login & Register --}}
        @guest
            <li>
                <a href="/login" class="nav-item {{ request()->is('login') ? 'active' : '' }}">
                    🔑 Login
                </a>
            </li>
            <li>
                <a href="/register" class="nav-item {{ request()->is('register') ? 'active' : '' }}">
                    📝 Register
                </a>
            </li>
        @endguest

        {{-- AUTH: full student nav --}}
        @auth
            <li>
                <a href="/dashboard" class="nav-item {{ request()->is('dashboard') ? 'active' : '' }}">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    Discussions
                </a>
            </li>

            <li>
                <a href="/quizzes" class="nav-item {{ request()->is('quizzes*') ? 'active' : '' }}">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    Quizzes
                </a>
            </li>

            <li>
                <a href="/notifications" class="nav-item {{ request()->is('notifications*') ? 'active' : '' }}">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    Notifications
                    <span class="badge">3</span>
                </a>
            </li>

            <li>
                <a href="/profile" class="nav-item {{ request()->is('profile*') ? 'active' : '' }}">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    👤 {{ Auth::user()->name }}
                </a>
            </li>

            <li>
                <form method="POST" action="/logout" style="margin:0;">
                    @csrf
                    <button type="submit" class="nav-item logout-btn">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        🚪 Logout
                    </button>
                </form>
            </li>
        @endauth

    </ul>

</nav>
