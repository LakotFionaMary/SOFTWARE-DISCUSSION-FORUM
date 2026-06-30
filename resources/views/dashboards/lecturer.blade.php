@extends('layout')
@section('title', 'Lecturer Dashboard — SDF Platform')

@section('page-content')


@if (session('success'))
    <div class="lec-alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="lec-shell">

    {{-- Welcome banner --}}
    <div class="lec-banner">
        <div>
            <div class="lec-greeting">Good day,</div>
            <h1 class="lec-name">{{ auth()->user()->name }}</h1>
            <span class="lec-role-pill">Lecturer</span>
        </div>
        <div class="lec-banner-actions">
            <a href="/lecturer/quizzes/create" class="lec-btn-primary">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Create new quiz
            </a>
            <form method="POST" action="/logout" style="margin:0;">
                @csrf
                <button type="submit" class="lec-btn-logout">🚪 Logout</button>
            </form>
        </div>
    </div>

    {{-- Stats --}}
    <div class="lec-stats">
        <div class="lec-stat-card">
            <span class="lec-stat-num">{{ $published }}</span>
            <span class="lec-stat-label">Quizzes published</span>
        </div>
        <div class="lec-stat-card">
            <span class="lec-stat-num">{{$drafts }}</span>
            <span class="lec-stat-label">Drafts</span>
        </div>
        <div class="lec-stat-card">
            <span class="lec-stat-num">0</span>
            <span class="lec-stat-label">Students attempted</span>
        </div>
        <div class="lec-stat-card">
            <span class="lec-stat-num">—</span>
            <span class="lec-stat-label">Avg. score</span>
        </div>
    </div>

    {{-- My quizzes --}}
    <div class="lec-card">
        <div class="lec-card-header">
            <h2 class="lec-card-title">My quizzes</h2>
            <a href="/lecturer/quizzes/create" class="lec-link">+ Create quiz</a>
        </div>

        <div class="lec-empty-quizzes">
            <svg width="40" height="40" fill="none" stroke="#94a3b8" stroke-width="1.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            <p>No quizzes yet. <a href="/lecturer/quizzes/create">Create your first quiz →</a></p>
        </div>
    </div>

</div>
@endsection

<style>
.lec-shell { max-width: 960px; margin: 0 auto; padding: 1.75rem 1.5rem; }

.lec-banner {
    background: linear-gradient(135deg, #4f46e5, #6366f1);
    border-radius: 14px; padding: 1.5rem 1.75rem;
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 1.25rem; gap: 1rem; flex-wrap: wrap;
}
.lec-alert-success {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
    border-radius: 10px;
    padding: 0.85rem 1.25rem;
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 1.25rem;
}
.lec-greeting { font-size: 0.85rem; color: rgba(255,255,255,0.75); margin-bottom: 0.1rem; }
.lec-name { font-size: 1.4rem; font-weight: 700; color: white; }
.lec-role-pill { background: rgba(255,255,255,0.2); color: white; font-size: 0.75rem; font-weight: 600; padding: 2px 10px; border-radius: 999px; display: inline-block; margin-top: 0.4rem; }
.lec-banner-actions { display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap; }
.lec-btn-primary {
    display: flex; align-items: center; gap: 0.4rem;
    padding: 0.6rem 1.2rem;
    background: white; color: var(--brand);
    border-radius: 8px; font-size: 0.875rem; font-weight: 700;
    text-decoration: none; transition: opacity 0.18s;
    font-family: 'Inter', sans-serif;
}
.lec-btn-primary:hover { opacity: 0.9; }
.lec-btn-logout {
    padding: 0.6rem 1rem; background: rgba(239,68,68,0.15);
    border: 1px solid rgba(239,68,68,0.35); border-radius: 8px;
    color: white; font-size: 0.875rem; font-weight: 500;
    font-family: 'Inter', sans-serif; cursor: pointer; transition: background 0.18s;
}
.lec-btn-logout:hover { background: rgba(239,68,68,0.28); }

.lec-stats { display: grid; grid-template-columns: repeat(4,1fr); gap: 1rem; margin-bottom: 1.25rem; }
@media (max-width: 640px) { .lec-stats { grid-template-columns: 1fr 1fr; } }
.lec-stat-card { background: white; border: 1px solid var(--border); border-radius: 12px; padding: 1.1rem; text-align: center; }
.lec-stat-num { display: block; font-size: 1.6rem; font-weight: 700; color: var(--brand); }
.lec-stat-label { font-size: 0.75rem; color: var(--text-muted); font-weight: 500; }

.lec-card { background: white; border: 1px solid var(--border); border-radius: 14px; padding: 1.3rem 1.5rem; }
.lec-card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; padding-bottom: 0.7rem; border-bottom: 1px solid var(--border); }
.lec-card-title { font-size: 0.95rem; font-weight: 700; }
.lec-link { font-size: 0.85rem; color: var(--brand); font-weight: 600; text-decoration: none; }
.lec-link:hover { text-decoration: underline; }
.lec-empty-quizzes { display: flex; flex-direction: column; align-items: center; gap: 0.6rem; padding: 2rem; color: var(--text-muted); font-size: 0.875rem; text-align: center; }
.lec-empty-quizzes a { color: var(--brand); font-weight: 600; text-decoration: none; }
.lec-empty-quizzes a:hover { text-decoration: underline; }
</style>
