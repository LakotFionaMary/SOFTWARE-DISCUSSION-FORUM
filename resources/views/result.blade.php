@extends('layout')
@section('title', 'Quiz Result — SDF Platform')

@section('page-content')

<div class="qr-shell">

    {{-- ══ RESULT HERO ══ --}}
    <div class="qr-hero" id="qrHero">
        <div class="qr-score-ring" id="qrRing">
            <svg class="qr-ring-svg" viewBox="0 0 120 120">
                <circle class="qr-ring-bg"   cx="60" cy="60" r="50" fill="none" stroke-width="10"/>
                <circle class="qr-ring-fill" cx="60" cy="60" r="50" fill="none" stroke-width="10"
                    stroke-dasharray="314"
                    stroke-dashoffset="314"
                    id="qrRingFill"/>
            </svg>
            <div class="qr-ring-inner">
                <span class="qr-pct" id="qrPct">0%</span>
                <span class="qr-pct-label">Score</span>
            </div>
        </div>
        <div class="qr-hero-info">
            <div class="qr-verdict" id="qrVerdict"></div>
            <h1 class="qr-quiz-name" id="qrQuizName">Object Oriented Programming</h1>
            <p class="qr-hero-sub" id="qrHeroSub"></p>
            <div class="qr-stats-row">
                <div class="qr-stat"><span class="qr-stat-num" id="qrCorrect">0</span><span class="qr-stat-label">Correct</span></div>
                <div class="qr-stat"><span class="qr-stat-num" id="qrWrong">0</span><span class="qr-stat-label">Wrong</span></div>
                <div class="qr-stat"><span class="qr-stat-num" id="qrSkipped">0</span><span class="qr-stat-label">Skipped</span></div>
                <div class="qr-stat"><span class="qr-stat-num" id="qrTotal">0</span><span class="qr-stat-label">Total marks</span></div>
            </div>
        </div>
    </div>

    {{-- ══ ACTION BUTTONS ══ --}}
    <div class="qr-actions">
        <a href="/quizzes" class="qr-btn-ghost">← Back to quizzes</a>
        <button class="qr-btn-secondary" onclick="toggleReview()">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            <span id="reviewToggleLabel">Show answer review</span>
        </button>
    </div>

    {{-- ══ ANSWER REVIEW ══ --}}
    <div class="qr-review" id="qrReview" style="display:none;">
        <h2 class="qr-review-title">Answer Review</h2>
        <div id="qrReviewBody"></div>
    </div>

</div>

@endsection

@push('scripts')
<script>
// ── Quiz + student answers (replace with @json from controller) ──
const quiz = {
    id: 1,
    title: 'Object Oriented Programming',
    category: 'Software Engineering',
    total_marks: 20,
    questions: [
        { id:1, text:'What is an object in OOP?', type:'MCQ', options:['A function','An instance of a class','A loop','A variable'], correct:1, marks:2 },
        { id:2, text:'Which principle enables reuse via a parent class?', type:'MCQ', options:['Encapsulation','Polymorphism','Inheritance','Abstraction'], correct:2, marks:2 },
        { id:3, text:'Is encapsulation the hiding of internal implementation details?', type:'TrueFalse', options:['True','False'], correct:0, marks:2 },
        { id:4, text:'Which keyword is used to create a class in Java?', type:'MCQ', options:['def','class','struct','object'], correct:1, marks:2 },
        { id:5, text:'What does polymorphism mean in OOP?', type:'MCQ', options:['One class, one form','Many classes, one interface','One function only','None of the above'], correct:1, marks:2 },
        { id:6, text:'Briefly explain the concept of abstraction in OOP.', type:'Short', options:[], correct:null, marks:2 },
        { id:7, text:'A constructor must always return a value.', type:'TrueFalse', options:['True','False'], correct:1, marks:2 },
        { id:8, text:'Which of the following is NOT an OOP language?', type:'MCQ', options:['Java','Python','C++','Assembly'], correct:3, marks:2 },
        { id:9, text:'What is method overriding?', type:'MCQ', options:['Changing method name','Providing a new implementation in a subclass','Adding more parameters','Deleting a method'], correct:1, marks:2 },
        { id:10, text:'Explain the difference between a class and an object.', type:'Short', options:[], correct:null, marks:2 }
    ]
};

// Simulated student answers (replace with actual submitted answers from server)
const studentAnswers = [1, 2, 0, 1, 1, 'Abstraction hides complexity from the user.', 1, 3, 1, null];

// ── Calculate results ──────────────────────────────────
let correct = 0, wrong = 0, skipped = 0, earned = 0;

quiz.questions.forEach((q, i) => {
    const ans = studentAnswers[i];
    if (ans === null || ans === '') { skipped++; return; }
    if (q.type === 'Short') { earned += q.marks; correct++; return; } // short = manual marking, give marks for demo
    if (ans === q.correct) { correct++; earned += q.marks; }
    else wrong++;
});

const pct = Math.round((earned / quiz.total_marks) * 100);
const pass = pct >= 50;

// ── Render hero ────────────────────────────────────────
document.getElementById('qrQuizName').textContent = quiz.title;
document.getElementById('qrCorrect').textContent  = correct;
document.getElementById('qrWrong').textContent    = wrong;
document.getElementById('qrSkipped').textContent  = skipped;
document.getElementById('qrTotal').textContent    = `${earned}/${quiz.total_marks}`;

const verdict = document.getElementById('qrVerdict');
verdict.textContent = pass ? '🎉 Congratulations — Pass!' : '😔 Better luck next time — Fail';
verdict.className   = `qr-verdict ${pass ? 'pass' : 'fail'}`;

document.getElementById('qrHeroSub').textContent =
    `You scored ${earned} out of ${quiz.total_marks} marks · ${quiz.category}`;

// Ring animation
const ringFill = document.getElementById('qrRingFill');
const pctEl    = document.getElementById('qrPct');
const circumference = 314;
ringFill.style.stroke = pass ? (pct >= 70 ? '#10b981' : '#f59e0b') : '#ef4444';

let currentPct = 0;
const interval = setInterval(() => {
    currentPct++;
    pctEl.textContent = currentPct + '%';
    ringFill.style.strokeDashoffset = circumference - (circumference * currentPct / 100);
    if (currentPct >= pct) clearInterval(interval);
}, 15);

// Hero bg
document.getElementById('qrHero').classList.add(pass ? 'hero-pass' : 'hero-fail');

// ── Render review ──────────────────────────────────────
function toggleReview() {
    const rev = document.getElementById('qrReview');
    const lbl = document.getElementById('reviewToggleLabel');
    const open = rev.style.display === 'none';
    rev.style.display = open ? 'block' : 'none';
    lbl.textContent   = open ? 'Hide answer review' : 'Show answer review';
    if (open) buildReview();
}

function buildReview() {
    const body = document.getElementById('qrReviewBody');
    if (body.dataset.built) return;
    body.dataset.built = 1;

    quiz.questions.forEach((q, i) => {
        const ans = studentAnswers[i];
        const isCorrect = q.type === 'Short' ? true : (ans === q.correct);
        const isSkipped = ans === null || ans === '';

        let statusCls, statusLabel;
        if (isSkipped)       { statusCls = 'qr-rv-skipped'; statusLabel = 'Skipped'; }
        else if (isCorrect)  { statusCls = 'qr-rv-correct';  statusLabel = '✓ Correct'; }
        else                 { statusCls = 'qr-rv-wrong';    statusLabel = '✗ Wrong'; }

        const div = document.createElement('div');
        div.className = `qr-rv-card ${statusCls}`;

        let optionsHtml = '';
        if (q.type === 'MCQ' || q.type === 'TrueFalse') {
            optionsHtml = '<div class="qr-rv-options">' +
                q.options.map((opt, j) => {
                    let cls = 'qr-rv-opt';
                    if (j === q.correct)          cls += ' correct-opt';
                    if (j === ans && ans !== q.correct) cls += ' wrong-opt';
                    const icon = j === q.correct ? '✓' : (j === ans ? '✗' : '');
                    return `<div class="${cls}"><span class="qr-rv-opt-letter">${String.fromCharCode(65+j)}</span>${esc(opt)}${icon ? `<span class="qr-rv-opt-icon">${icon}</span>` : ''}</div>`;
                }).join('') + '</div>';
        } else {
            optionsHtml = `<div class="qr-rv-short-ans">
                <div class="qr-rv-short-label">Your answer:</div>
                <div class="qr-rv-short-text">${ans ? esc(String(ans)) : '<em>No answer</em>'}</div>
                <div class="qr-rv-short-note">Short answers are reviewed by your lecturer.</div>
            </div>`;
        }

        div.innerHTML = `
            <div class="qr-rv-header">
                <span class="qr-rv-qnum">Q${i+1}</span>
                <span class="qr-rv-status-badge ${statusCls}-badge">${statusLabel}</span>
                <span class="qr-rv-marks">${isCorrect && !isSkipped ? q.marks : 0}/${q.marks} marks</span>
            </div>
            <p class="qr-rv-qtext">${esc(q.text)}</p>
            ${optionsHtml}`;

        body.appendChild(div);
    });
}

function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
</script>
@endpush

<style>
.qr-shell { max-width:820px; margin:0 auto; padding:1.75rem 1.5rem 4rem; }

/* Hero */
.qr-hero {
    background:white; border:1.5px solid var(--border); border-radius:18px;
    padding:2rem; display:flex; align-items:center; gap:2rem;
    margin-bottom:1.25rem; flex-wrap:wrap;
    box-shadow:0 4px 20px rgba(0,0,0,0.07);
}
.qr-hero.hero-pass { border-top:4px solid #10b981; }
.qr-hero.hero-fail { border-top:4px solid #ef4444; }

/* Score ring */
.qr-score-ring { position:relative; width:130px; height:130px; flex-shrink:0; }
.qr-ring-svg { width:100%; height:100%; transform:rotate(-90deg); }
.qr-ring-bg   { stroke:#f1f5f9; }
.qr-ring-fill { stroke:#10b981; stroke-linecap:round; transition:stroke-dashoffset 0.05s linear; }
.qr-ring-inner { position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; }
.qr-pct { font-size:1.6rem; font-weight:800; color:var(--text-primary); line-height:1; }
.qr-pct-label { font-size:0.7rem; color:var(--text-muted); font-weight:500; }

/* Hero info */
.qr-hero-info { flex:1; min-width:200px; }
.qr-verdict { font-size:1rem; font-weight:700; margin-bottom:0.35rem; }
.qr-verdict.pass { color:#059669; }
.qr-verdict.fail { color:#dc2626; }
.qr-quiz-name { font-size:1.25rem; font-weight:700; color:var(--text-primary); margin-bottom:0.25rem; }
.qr-hero-sub { font-size:0.82rem; color:var(--text-muted); margin-bottom:1rem; }
.qr-stats-row { display:flex; gap:1.5rem; flex-wrap:wrap; }
.qr-stat { text-align:center; }
.qr-stat-num { display:block; font-size:1.3rem; font-weight:700; color:var(--brand); }
.qr-stat-label { font-size:0.7rem; color:var(--text-muted); font-weight:500; }

/* Actions */
.qr-actions { display:flex; gap:0.75rem; margin-bottom:1.5rem; flex-wrap:wrap; }
.qr-btn-ghost { padding:0.55rem 1.1rem; background:white; border:1.5px solid var(--border); border-radius:8px; font-size:0.875rem; font-family:'Inter',sans-serif; font-weight:500; color:var(--text-muted); cursor:pointer; text-decoration:none; transition:all 0.18s; }
.qr-btn-ghost:hover { border-color:var(--brand); color:var(--brand); }
.qr-btn-secondary { display:flex; align-items:center; gap:0.45rem; padding:0.55rem 1.1rem; background:#eef2ff; border:1.5px solid #c7d2fe; border-radius:8px; font-size:0.875rem; font-family:'Inter',sans-serif; font-weight:600; color:var(--brand); cursor:pointer; transition:all 0.18s; }
.qr-btn-secondary:hover { background:#e0e7ff; }

/* Review */
.qr-review { }
.qr-review-title { font-size:1.05rem; font-weight:700; color:var(--text-primary); margin-bottom:1rem; padding-bottom:0.6rem; border-bottom:1.5px solid var(--border); }

.qr-rv-card { background:white; border:1.5px solid var(--border); border-radius:12px; padding:1.1rem 1.25rem; margin-bottom:0.85rem; }
.qr-rv-correct  { border-left:4px solid #10b981; }
.qr-rv-wrong    { border-left:4px solid #ef4444; }
.qr-rv-skipped  { border-left:4px solid #94a3b8; }

.qr-rv-header { display:flex; align-items:center; gap:0.65rem; margin-bottom:0.6rem; flex-wrap:wrap; }
.qr-rv-qnum { font-size:0.72rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em; }
.qr-rv-status-badge { font-size:0.72rem; font-weight:700; padding:2px 8px; border-radius:999px; }
.qr-rv-correct-badge  { background:#dcfce7; color:#166534; }
.qr-rv-wrong-badge    { background:#fee2e2; color:#991b1b; }
.qr-rv-skipped-badge  { background:#f1f5f9; color:#64748b; }
.qr-rv-marks { margin-left:auto; font-size:0.8rem; font-weight:700; color:var(--text-primary); }

.qr-rv-qtext { font-size:0.9rem; font-weight:600; color:var(--text-primary); margin-bottom:0.85rem; line-height:1.5; }

.qr-rv-options { display:grid; grid-template-columns:1fr 1fr; gap:0.45rem; }
@media(max-width:540px){ .qr-rv-options { grid-template-columns:1fr; } }
.qr-rv-opt { display:flex; align-items:center; gap:0.55rem; padding:0.5rem 0.75rem; border:1.5px solid var(--border); border-radius:8px; font-size:0.83rem; color:var(--text-muted); background:#f8fafc; position:relative; }
.qr-rv-opt.correct-opt { border-color:#10b981; background:#f0fdf4; color:#166534; font-weight:600; }
.qr-rv-opt.wrong-opt   { border-color:#ef4444; background:#fff1f2; color:#991b1b; font-weight:600; }
.qr-rv-opt-letter { width:22px; height:22px; border-radius:50%; background:var(--border); display:flex; align-items:center; justify-content:center; font-size:0.68rem; font-weight:700; flex-shrink:0; }
.qr-rv-opt.correct-opt .qr-rv-opt-letter { background:#10b981; color:white; }
.qr-rv-opt.wrong-opt   .qr-rv-opt-letter { background:#ef4444; color:white; }
.qr-rv-opt-icon { margin-left:auto; font-weight:700; }

.qr-rv-short-ans { background:#f8fafc; border:1.5px solid var(--border); border-radius:8px; padding:0.75rem 1rem; }
.qr-rv-short-label { font-size:0.72rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.35rem; }
.qr-rv-short-text { font-size:0.875rem; color:var(--text-primary); line-height:1.5; margin-bottom:0.5rem; }
.qr-rv-short-note { font-size:0.75rem; color:#f59e0b; font-weight:500; }
</style>
