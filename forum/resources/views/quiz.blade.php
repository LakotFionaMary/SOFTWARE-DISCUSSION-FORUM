@extends('layout')
@section('title', 'My Quizzes — SDF Platform')

@section('page-content')

<div class="sq-shell">

    {{-- ══ HEADER ══ --}}
    <div class="sq-header">
        <div>
            <h1 class="sq-page-title">📝 My Quizzes</h1>
            <p class="sq-subtitle">Quizzes set by your lecturers appear here. Attempt them before the deadline.</p>
        </div>
        <div class="sq-header-right">
            <div class="sq-search-wrap">
                <svg class="sq-search-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
                <input type="text" class="sq-search" placeholder="Search quizzes…" oninput="searchQuizzes(this.value)">
            </div>
        </div>
    </div>

    {{-- ══ FILTER CHIPS ══ --}}
    <div class="sq-chips">
        <button class="sq-chip active" onclick="filterQuizzes('all', this)">All</button>
        <button class="sq-chip" onclick="filterQuizzes('open', this)">🟢 Open now</button>
        <button class="sq-chip" onclick="filterQuizzes('upcoming', this)">🕐 Upcoming</button>
        <button class="sq-chip" onclick="filterQuizzes('attempted', this)">✅ Attempted</button>
        <button class="sq-chip" onclick="filterQuizzes('closed', this)">🔒 Closed</button>
    </div>

    {{-- ══ QUIZ CARDS ══ --}}
    <div class="sq-grid" id="quizGrid"></div>

    {{-- Empty state --}}
    <div class="sq-empty" id="sqEmpty" style="display:none;">
        <svg width="48" height="48" fill="none" stroke="#94a3b8" stroke-width="1.3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        <p>No quizzes found in this category.</p>
    </div>

</div>
@endsection

@push('scripts')
<script>

const quizzes = [
    {
        id: 1,
        title: 'Object Oriented Programming',
        category: 'Software Engineering',
        lecturer: 'Dr. Okello James',
        date: '2026-06-27',
        time: '12:00',
        duration: 30,
        questions: 10,
        attempts_allowed: 1,
        status: 'open',
        attempted: false,
        score: null
    },
    {
        id: 2,
        title: 'Database Design Principles',
        category: 'Computer Science',
        lecturer: 'Dr. Namukasa Rita',
        date: '2026-07-10',
        time: '09:00',
        duration: 45,
        questions: 15,
        attempts_allowed: 2,
        status: 'upcoming',
        attempted: false,
        score: null
    },
    {
        id: 3,
        title: 'Networking Fundamentals',
        category: 'Information Technology',
        lecturer: 'Mr. Wasswa Paul',
        date: '2026-06-20',
        time: '14:00',
        duration: 60,
        questions: 20,
        attempts_allowed: 1,
        status: 'attempted',
        attempted: true,
        score: 72
    },
    {
        id: 4,
        title: 'Software Testing Methods',
        category: 'Software Engineering',
        lecturer: 'Dr. Okello James',
        date: '2026-06-15',
        time: '10:00',
        duration: 40,
        questions: 12,
        attempts_allowed: 1,
        status: 'closed',
        attempted: false,
        score: null
    }
];

let activeFilter = 'all';

const statusCfg = {
    open:      { label: '🟢 Open now',  cls: 'sq-badge-open',      canAttempt: true  },
    upcoming:  { label: '🕐 Upcoming',  cls: 'sq-badge-upcoming',  canAttempt: false },
    attempted: { label: '✅ Attempted', cls: 'sq-badge-attempted', canAttempt: false },
    closed:    { label: '🔒 Closed',    cls: 'sq-badge-closed',    canAttempt: false }
};

function renderQuizzes(data) {
    const grid  = document.getElementById('quizGrid');
    const empty = document.getElementById('sqEmpty');
    grid.innerHTML = '';

    if (!data.length) { empty.style.display = 'flex'; return; }
    empty.style.display = 'none';

    data.forEach(q => {
        const cfg      = statusCfg[q.status] || statusCfg.closed;
        const dateStr  = new Date(q.date + 'T' + q.time).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });
        const timeStr  = formatTime(q.time);
        const endTime  = getEndTime(q.date, q.time, q.duration);

        const card = document.createElement('div');
        card.className = `sq-card sq-card-${q.status}`;
        card.dataset.status = q.status;
        card.dataset.title  = q.title.toLowerCase();

        card.innerHTML = `
            <div class="sq-card-top">
                <span class="sq-badge ${cfg.cls}">${cfg.label}</span>
                <span class="sq-category">${esc(q.category)}</span>
            </div>
            <h3 class="sq-card-title">${esc(q.title)}</h3>
            <p class="sq-lecturer">👤 ${esc(q.lecturer)}</p>

            <div class="sq-meta-grid">
                <div class="sq-meta-item">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <span>${dateStr}</span>
                </div>
                <div class="sq-meta-item">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <span>${timeStr} — ${endTime}</span>
                </div>
                <div class="sq-meta-item">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>${q.duration} minutes</span>
                </div>
                <div class="sq-meta-item">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg>
                    <span>${q.questions} questions</span>
                </div>
            </div>

            ${q.status === 'upcoming' ? `
            <div class="sq-countdown" id="countdown-${q.id}">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Opens in: <strong id="cdtimer-${q.id}">calculating…</strong>
            </div>` : ''}

            ${q.status === 'attempted' ? `
            <div class="sq-score-bar">
                <div class="sq-score-label">Your score</div>
                <div class="sq-score-track">
                    <div class="sq-score-fill ${q.score >= 70 ? 'good' : q.score >= 50 ? 'mid' : 'low'}" style="width:${q.score}%"></div>
                </div>
                <span class="sq-score-pct ${q.score >= 70 ? 'good' : q.score >= 50 ? 'mid' : 'low'}">${q.score}%</span>
            </div>` : ''}

            <div class="sq-card-footer">
                <span class="sq-attempts-note">
                    ${q.attempts_allowed === 1 ? '1 attempt allowed' : q.attempts_allowed + ' attempts allowed'}
                </span>
                ${cfg.canAttempt
                    ? `<a href="/quizzes/${q.id}/attempt" class="sq-btn-attempt">Start quiz →</a>`
                    : q.status === 'attempted'
                        ? `<a href="/quizzes/${q.id}/result" class="sq-btn-review">View result →</a>`
                        : `<button class="sq-btn-disabled" disabled>${q.status === 'upcoming' ? 'Not open yet' : 'Closed'}</button>`
                }
            </div>`;

        grid.appendChild(card);

        // Start countdown for upcoming
        if (q.status === 'upcoming') startCountdown(q.id, q.date, q.time);
    });
}

function filterQuizzes(filter, btn) {
    activeFilter = filter;
    document.querySelectorAll('.sq-chip').forEach(c => c.classList.remove('active'));
    btn.classList.add('active');
    applyFilters();
}

function searchQuizzes(val) { applyFilters(); }

function applyFilters() {
    const search = document.querySelector('.sq-search').value.toLowerCase();
    const filtered = quizzes.filter(q => {
        const matchFilter = activeFilter === 'all' || q.status === activeFilter;
        const matchSearch = q.title.toLowerCase().includes(search) || q.category.toLowerCase().includes(search);
        return matchFilter && matchSearch;
    });
    renderQuizzes(filtered);
}

function startCountdown(id, date, time) {
    const target = new Date(date + 'T' + time).getTime();
    const el = document.getElementById('cdtimer-' + id);
    if (!el) return;
    const tick = () => {
        const diff = target - Date.now();
        if (diff <= 0) { el.textContent = 'Opening now — refresh!'; return; }
        const d = Math.floor(diff / 86400000);
        const h = Math.floor((diff % 86400000) / 3600000);
        const m = Math.floor((diff % 3600000) / 60000);
        const s = Math.floor((diff % 60000) / 1000);
        el.textContent = d > 0 ? `${d}d ${h}h ${m}m` : `${h}h ${m}m ${s}s`;
    };
    tick(); setInterval(tick, 1000);
}

function getEndTime(date, time, duration) {
    const d = new Date(date + 'T' + time);
    d.setMinutes(d.getMinutes() + duration);
    return formatTime(`${String(d.getHours()).padStart(2,'0')}:${String(d.getMinutes()).padStart(2,'0')}`);
}

function formatTime(t) {
    const [h, m] = t.split(':').map(Number);
    return `${((h%12)||12)}:${String(m).padStart(2,'0')} ${h>=12?'PM':'AM'}`;
}
function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

renderQuizzes(quizzes);
</script>
@endpush

<style>
.sq-shell { max-width: 1100px; margin: 0 auto; padding: 1.75rem 1.5rem 4rem; }

.sq-header { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:1.25rem; }
.sq-page-title { font-size:1.4rem; font-weight:700; color:var(--text-primary); }
.sq-subtitle { font-size:0.85rem; color:var(--text-muted); margin-top:0.25rem; }
.sq-search-wrap { position:relative; }
.sq-search-icon { position:absolute; left:0.75rem; top:50%; transform:translateY(-50%); color:var(--text-muted); pointer-events:none; }
.sq-search { padding:0.5rem 0.85rem 0.5rem 2.1rem; border:1.5px solid var(--border); border-radius:8px; font-size:0.875rem; font-family:'Inter',sans-serif; width:210px; background:white; color:var(--text-primary); transition:border-color 0.18s; }
.sq-search:focus { outline:none; border-color:var(--brand); box-shadow:0 0 0 3px rgba(79,70,229,0.1); }

.sq-chips { display:flex; gap:0.4rem; flex-wrap:wrap; margin-bottom:1.25rem; }
.sq-chip { padding:0.3rem 0.85rem; border-radius:999px; border:1.5px solid var(--border); background:white; font-size:0.8rem; font-family:'Inter',sans-serif; font-weight:500; color:var(--text-muted); cursor:pointer; transition:all 0.18s; }
.sq-chip:hover { border-color:var(--brand); color:var(--brand); }
.sq-chip.active { background:var(--brand); border-color:var(--brand); color:white; }

.sq-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:1.1rem; }

.sq-card { background:white; border:1.5px solid var(--border); border-radius:14px; padding:1.25rem; display:flex; flex-direction:column; gap:0.7rem; transition:box-shadow 0.18s, transform 0.18s; }
.sq-card:hover { box-shadow:0 6px 24px rgba(0,0,0,0.09); transform:translateY(-2px); }
.sq-card-open     { border-top:3px solid #10b981; }
.sq-card-upcoming { border-top:3px solid #f59e0b; }
.sq-card-attempted{ border-top:3px solid #4f46e5; }
.sq-card-closed   { border-top:3px solid #cbd5e1; opacity:0.8; }

.sq-card-top { display:flex; align-items:center; justify-content:space-between; gap:0.5rem; }
.sq-badge { font-size:0.72rem; font-weight:700; padding:3px 9px; border-radius:999px; white-space:nowrap; }
.sq-badge-open      { background:#dcfce7; color:#166534; }
.sq-badge-upcoming  { background:#fef3c7; color:#92400e; }
.sq-badge-attempted { background:#eef2ff; color:#3730a3; }
.sq-badge-closed    { background:#f1f5f9; color:#64748b; }
.sq-category { font-size:0.72rem; font-weight:600; color:var(--text-muted); background:#f1f5f9; padding:2px 8px; border-radius:999px; }

.sq-card-title { font-size:1rem; font-weight:700; color:var(--text-primary); line-height:1.35; }
.sq-lecturer { font-size:0.8rem; color:var(--text-muted); }

.sq-meta-grid { display:grid; grid-template-columns:1fr 1fr; gap:0.45rem; }
.sq-meta-item { display:flex; align-items:center; gap:0.4rem; font-size:0.78rem; color:var(--text-muted); }

.sq-countdown { display:flex; align-items:center; gap:0.4rem; font-size:0.8rem; color:#d97706; background:#fef3c7; border-radius:8px; padding:0.45rem 0.7rem; font-weight:500; }

.sq-score-bar { display:flex; align-items:center; gap:0.65rem; }
.sq-score-label { font-size:0.75rem; font-weight:600; color:var(--text-muted); white-space:nowrap; }
.sq-score-track { flex:1; height:7px; background:#f1f5f9; border-radius:999px; overflow:hidden; }
.sq-score-fill { height:100%; border-radius:999px; transition:width 0.6s ease; }
.sq-score-fill.good { background:#10b981; }
.sq-score-fill.mid  { background:#f59e0b; }
.sq-score-fill.low  { background:#ef4444; }
.sq-score-pct { font-size:0.82rem; font-weight:700; white-space:nowrap; }
.sq-score-pct.good { color:#059669; }
.sq-score-pct.mid  { color:#d97706; }
.sq-score-pct.low  { color:#dc2626; }

.sq-card-footer { display:flex; align-items:center; justify-content:space-between; margin-top:auto; padding-top:0.5rem; border-top:1px solid var(--border); }
.sq-attempts-note { font-size:0.75rem; color:var(--text-muted); }
.sq-btn-attempt { padding:0.45rem 1rem; background:linear-gradient(135deg,#4f46e5,#6366f1); color:white; border-radius:8px; font-size:0.82rem; font-weight:700; font-family:'Inter',sans-serif; text-decoration:none; transition:opacity 0.18s; white-space:nowrap; }
.sq-btn-attempt:hover { opacity:0.88; }
.sq-btn-review { padding:0.45rem 1rem; background:#eef2ff; color:var(--brand); border-radius:8px; font-size:0.82rem; font-weight:700; font-family:'Inter',sans-serif; text-decoration:none; transition:background 0.18s; white-space:nowrap; }
.sq-btn-review:hover { background:#e0e7ff; }
.sq-btn-disabled { padding:0.45rem 1rem; background:#f1f5f9; color:#94a3b8; border:none; border-radius:8px; font-size:0.82rem; font-weight:600; font-family:'Inter',sans-serif; cursor:not-allowed; white-space:nowrap; }

.sq-empty { display:flex; flex-direction:column; align-items:center; gap:0.75rem; padding:4rem 2rem; text-align:center; color:var(--text-muted); font-size:0.875rem; }
</style>
