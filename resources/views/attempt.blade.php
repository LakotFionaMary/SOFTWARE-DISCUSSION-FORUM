@extends('layout')
@section('title', 'Attempt Quiz — SDF Platform')

@section('page-content')

<div class="qa-shell">

    {{-- ══ TOP BAR ══ --}}
    <div class="qa-topbar">
        <div class="qa-topbar-left">
            <h2 class="qa-quiz-title" id="qaTitle">Object Oriented Programming</h2>
            <span class="qa-meta">Software Engineering &nbsp;·&nbsp; 10 questions &nbsp;·&nbsp; 30 mins</span>
        </div>
        <div class="qa-timer-wrap">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span class="qa-timer" id="qaTimer">30:00</span>
        </div>
    </div>

    {{-- ══ PROGRESS BAR ══ --}}
    <div class="qa-progress-wrap">
        <div class="qa-progress-track">
            <div class="qa-progress-fill" id="qaProgressFill" style="width:10%"></div>
        </div>
        <span class="qa-progress-label" id="qaProgressLabel">Question 1 of 10</span>
    </div>

    {{-- ══ QUESTION DOTS ══ --}}
    <div class="qa-dots" id="qaDots"></div>

    {{-- ══ QUESTION CARD ══ --}}
    <div class="qa-card" id="qaCard">
        <div class="qa-q-number" id="qaQNumber">Question 1</div>
        <p class="qa-q-text" id="qaQText"></p>
        <div class="qa-options" id="qaOptions"></div>
        <div class="qa-short-wrap" id="qaShortWrap" style="display:none;">
            <textarea class="qa-short-input" id="qaShortInput" rows="4" placeholder="Type your answer here…"></textarea>
        </div>
    </div>

    {{-- ══ NAVIGATION ══ --}}
    <div class="qa-nav">
        <button class="qa-btn-prev" id="qaPrev" onclick="prevQuestion()">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Previous
        </button>
        <button class="qa-btn-next" id="qaNext" onclick="nextQuestion()">
            Next
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </button>
        <button class="qa-btn-submit" id="qaSubmit" onclick="openSubmitModal()" style="display:none;">
            Submit quiz
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </button>
    </div>

</div>

{{-- ══ SUBMIT CONFIRM MODAL ══ --}}
<div class="qa-overlay" id="submitOverlay">
    <div class="qa-modal">
        <div class="qa-modal-icon">
            <svg width="28" height="28" fill="none" stroke="#4f46e5" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <h3 class="qa-modal-title">Submit quiz?</h3>
        <p class="qa-modal-body" id="submitMsg">You have answered <strong id="answeredCount">0</strong> of <strong id="totalCount">10</strong> questions. Unanswered questions will be marked as 0.</p>
        <div class="qa-modal-actions">
            <button class="qa-btn-ghost" onclick="closeSubmitModal()">Review answers</button>
            <button class="qa-btn-confirm" onclick="submitQuiz()">Yes, submit</button>
        </div>
    </div>
</div>

{{-- Auto-submit modal --}}
<div class="qa-overlay" id="autoSubmitOverlay">
    <div class="qa-modal">
        <div class="qa-modal-icon" style="background:#fee2e2;">
            <svg width="28" height="28" fill="none" stroke="#ef4444" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <h3 class="qa-modal-title">Time's up!</h3>
        <p class="qa-modal-body">Your quiz has been automatically submitted.</p>
        <div class="qa-modal-actions">
            <a href="/quizzes/1/result" class="qa-btn-confirm">View results</a>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Quiz data (replace with @json($quiz) from controller) ──
const quiz = {
    id: 1,
    title: 'Object Oriented Programming',
    duration: 30, // minutes
    questions: [
        { id:1, text:'What is an object in OOP?', type:'MCQ', options:['A function','An instance of a class','A loop','A variable'], correct:1 },
        { id:2, text:'Which principle enables reuse via a parent class?', type:'MCQ', options:['Encapsulation','Polymorphism','Inheritance','Abstraction'], correct:2 },
        { id:3, text:'Is encapsulation the hiding of internal implementation details?', type:'TrueFalse', options:['True','False'], correct:0 },
        { id:4, text:'Which keyword is used to create a class in Java?', type:'MCQ', options:['def','class','struct','object'], correct:1 },
        { id:5, text:'What does polymorphism mean in OOP?', type:'MCQ', options:['One class, one form','Many classes, one interface','One function only','None of the above'], correct:1 },
        { id:6, text:'Briefly explain the concept of abstraction in OOP.', type:'Short', options:[], correct:null },
        { id:7, text:'A constructor must always return a value.', type:'TrueFalse', options:['True','False'], correct:1 },
        { id:8, text:'Which of the following is NOT an OOP language?', type:'MCQ', options:['Java','Python','C++','Assembly'], correct:3 },
        { id:9, text:'What is method overriding?', type:'MCQ', options:['Changing method name','Providing a new implementation in a subclass','Adding more parameters','Deleting a method'], correct:1 },
        { id:10, text:'Explain the difference between a class and an object.', type:'Short', options:[], correct:null }
    ]
};

let current   = 0;
let answers   = new Array(quiz.questions.length).fill(null);
let timeLeft  = quiz.duration * 60;
let timerInt  = null;

// ── Timer ──────────────────────────────────────────────
function startTimer() {
    timerInt = setInterval(() => {
        timeLeft--;
        const m = Math.floor(timeLeft / 60);
        const s = timeLeft % 60;
        const el = document.getElementById('qaTimer');
        el.textContent = `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
        if (timeLeft <= 300) el.classList.add('warning');
        if (timeLeft <= 60)  el.classList.add('danger');
        if (timeLeft <= 0)   { clearInterval(timerInt); autoSubmit(); }
    }, 1000);
}

function autoSubmit() {
    document.getElementById('autoSubmitOverlay').style.display = 'flex';
    // TODO: POST /quizzes/{id}/submit with answers
}

// ── Render question ────────────────────────────────────
function renderQuestion() {
    const q   = quiz.questions[current];
    const tot = quiz.questions.length;

    document.getElementById('qaQNumber').textContent = `Question ${current + 1}`;
    document.getElementById('qaQText').textContent   = q.text;
    document.getElementById('qaProgressFill').style.width = `${((current + 1) / tot) * 100}%`;
    document.getElementById('qaProgressLabel').textContent = `Question ${current + 1} of ${tot}`;

    // Options
    const optWrap   = document.getElementById('qaOptions');
    const shortWrap = document.getElementById('qaShortWrap');
    const shortIn   = document.getElementById('qaShortInput');
    optWrap.innerHTML = '';

    if (q.type === 'Short') {
        optWrap.style.display   = 'none';
        shortWrap.style.display = 'block';
        shortIn.value = answers[current] || '';
        shortIn.oninput = () => { answers[current] = shortIn.value; updateDots(); };
    } else {
        optWrap.style.display   = 'grid';
        shortWrap.style.display = 'none';
        q.options.forEach((opt, i) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = `qa-option ${answers[current] === i ? 'selected' : ''}`;
            btn.innerHTML = `<span class="qa-opt-letter">${String.fromCharCode(65+i)}</span>${esc(opt)}`;
            btn.onclick = () => selectOption(i);
            optWrap.appendChild(btn);
        });
    }

    // Nav buttons
    document.getElementById('qaPrev').disabled   = current === 0;
    document.getElementById('qaNext').style.display   = current < tot - 1 ? 'flex' : 'none';
    document.getElementById('qaSubmit').style.display = current === tot - 1 ? 'flex' : 'none';

    updateDots();

    // Animate card
    const card = document.getElementById('qaCard');
    card.classList.remove('qa-enter');
    void card.offsetWidth;
    card.classList.add('qa-enter');
}

function selectOption(i) {
    answers[current] = i;
    renderQuestion();
}

function nextQuestion() {
    if (current < quiz.questions.length - 1) { current++; renderQuestion(); }
}
function prevQuestion() {
    if (current > 0) { current--; renderQuestion(); }
}

// ── Dots ───────────────────────────────────────────────
function updateDots() {
    const wrap = document.getElementById('qaDots');
    wrap.innerHTML = '';
    quiz.questions.forEach((q, i) => {
        const d = document.createElement('button');
        d.type = 'button';
        d.className = `qa-dot ${i === current ? 'active' : ''} ${answers[i] !== null ? 'answered' : ''}`;
        d.title = `Q${i+1}`;
        d.onclick = () => { current = i; renderQuestion(); };
        wrap.appendChild(d);
    });
}

// ── Submit ─────────────────────────────────────────────
function openSubmitModal() {
    const answered = answers.filter(a => a !== null).length;
    document.getElementById('answeredCount').textContent = answered;
    document.getElementById('totalCount').textContent    = quiz.questions.length;
    document.getElementById('submitOverlay').style.display = 'flex';
}
function closeSubmitModal() { document.getElementById('submitOverlay').style.display = 'none'; }

function submitQuiz() {
    clearInterval(timerInt);
    closeSubmitModal();
    // TODO: POST /quizzes/{id}/submit → { answers: answers }
    // For demo, redirect to result page
    window.location.href = `/quizzes/${quiz.id}/result`;
}

function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

// ── Init ───────────────────────────────────────────────
document.getElementById('autoSubmitOverlay').style.display = 'none';
document.getElementById('submitOverlay').style.display     = 'none';
renderQuestion();
startTimer();
</script>
@endpush

<style>
.qa-shell { max-width:780px; margin:0 auto; padding:1.75rem 1.5rem 4rem; }

/* Topbar */
.qa-topbar { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; margin-bottom:1.25rem; flex-wrap:wrap; }
.qa-quiz-title { font-size:1.2rem; font-weight:700; color:var(--text-primary); }
.qa-meta { font-size:0.8rem; color:var(--text-muted); display:block; margin-top:0.2rem; }
.qa-timer-wrap { display:flex; align-items:center; gap:0.5rem; background:white; border:1.5px solid var(--border); border-radius:10px; padding:0.55rem 1rem; font-weight:700; font-size:1rem; color:var(--text-primary); box-shadow:0 2px 8px rgba(0,0,0,0.06); white-space:nowrap; flex-shrink:0; }
.qa-timer.warning { color:#d97706; }
.qa-timer.danger  { color:#ef4444; animation:pulse-red 1s infinite; }
@keyframes pulse-red { 0%,100%{opacity:1} 50%{opacity:0.5} }

/* Progress */
.qa-progress-wrap { display:flex; align-items:center; gap:0.85rem; margin-bottom:1rem; }
.qa-progress-track { flex:1; height:7px; background:#e2e8f0; border-radius:999px; overflow:hidden; }
.qa-progress-fill { height:100%; background:linear-gradient(90deg,#4f46e5,#818cf8); border-radius:999px; transition:width 0.4s ease; }
.qa-progress-label { font-size:0.78rem; color:var(--text-muted); font-weight:600; white-space:nowrap; }

/* Dots */
.qa-dots { display:flex; gap:0.4rem; flex-wrap:wrap; margin-bottom:1.25rem; }
.qa-dot { width:28px; height:28px; border-radius:50%; border:2px solid var(--border); background:white; cursor:pointer; transition:all 0.18s; font-size:0.7rem; font-weight:600; color:var(--text-muted); display:flex; align-items:center; justify-content:center; }
.qa-dot:hover   { border-color:var(--brand); color:var(--brand); }
.qa-dot.answered{ background:#eef2ff; border-color:var(--brand); color:var(--brand); }
.qa-dot.active  { background:var(--brand); border-color:var(--brand); color:white; }

/* Question card */
.qa-card { background:white; border:1.5px solid var(--border); border-radius:16px; padding:1.75rem; margin-bottom:1.25rem; box-shadow:0 2px 12px rgba(0,0,0,0.06); }
.qa-card.qa-enter { animation:slideIn 0.25s ease; }
@keyframes slideIn { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }
.qa-q-number { font-size:0.75rem; font-weight:700; color:var(--brand); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.6rem; }
.qa-q-text { font-size:1.05rem; font-weight:600; color:var(--text-primary); line-height:1.55; margin-bottom:1.25rem; }

/* Options */
.qa-options { display:grid; grid-template-columns:1fr 1fr; gap:0.65rem; }
@media(max-width:540px){ .qa-options { grid-template-columns:1fr; } }
.qa-option { display:flex; align-items:center; gap:0.75rem; padding:0.75rem 1rem; border:1.5px solid var(--border); border-radius:10px; background:white; cursor:pointer; font-size:0.9rem; font-family:'Inter',sans-serif; color:var(--text-primary); text-align:left; transition:all 0.18s; }
.qa-option:hover    { border-color:var(--brand); background:#f5f3ff; }
.qa-option.selected { border-color:var(--brand); background:#eef2ff; color:var(--brand); font-weight:600; }
.qa-opt-letter { width:26px; height:26px; border-radius:50%; background:var(--border); display:flex; align-items:center; justify-content:center; font-size:0.75rem; font-weight:700; flex-shrink:0; transition:all 0.18s; }
.qa-option.selected .qa-opt-letter { background:var(--brand); color:white; }

/* Short answer */
.qa-short-input { width:100%; padding:0.75rem 1rem; border:1.5px solid var(--border); border-radius:10px; font-size:0.9rem; font-family:'Inter',sans-serif; resize:vertical; line-height:1.6; transition:border-color 0.18s; }
.qa-short-input:focus { outline:none; border-color:var(--brand); box-shadow:0 0 0 3px rgba(79,70,229,0.1); }

/* Nav */
.qa-nav { display:flex; align-items:center; gap:0.75rem; justify-content:space-between; }
.qa-btn-prev,.qa-btn-next,.qa-btn-submit { display:flex; align-items:center; gap:0.4rem; padding:0.65rem 1.3rem; border-radius:10px; font-size:0.875rem; font-family:'Inter',sans-serif; font-weight:600; cursor:pointer; transition:all 0.18s; border:none; }
.qa-btn-prev { background:white; border:1.5px solid var(--border); color:var(--text-muted); }
.qa-btn-prev:hover:not([disabled]) { border-color:var(--brand); color:var(--brand); }
.qa-btn-prev[disabled] { opacity:0.4; cursor:default; }
.qa-btn-next { background:linear-gradient(135deg,#4f46e5,#6366f1); color:white; margin-left:auto; box-shadow:0 2px 8px rgba(79,70,229,0.28); }
.qa-btn-next:hover { opacity:0.9; transform:translateY(-1px); }
.qa-btn-submit { background:linear-gradient(135deg,#059669,#10b981); color:white; margin-left:auto; box-shadow:0 2px 8px rgba(16,185,129,0.3); }
.qa-btn-submit:hover { opacity:0.9; transform:translateY(-1px); }

/* Modals */
.qa-overlay { position:fixed; inset:0; background:rgba(15,23,42,0.5); backdrop-filter:blur(3px); display:flex; align-items:center; justify-content:center; z-index:9999; padding:1rem; }
.qa-modal { background:white; border-radius:16px; padding:2rem; max-width:420px; width:100%; text-align:center; box-shadow:0 20px 60px rgba(0,0,0,0.2); }
.qa-modal-icon { width:56px; height:56px; background:#eef2ff; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1rem; }
.qa-modal-title { font-size:1.1rem; font-weight:700; margin-bottom:0.6rem; }
.qa-modal-body { font-size:0.875rem; color:var(--text-muted); line-height:1.6; margin-bottom:1.5rem; }
.qa-modal-actions { display:flex; justify-content:center; gap:0.75rem; flex-wrap:wrap; }
.qa-btn-ghost { padding:0.6rem 1.1rem; background:none; border:1.5px solid var(--border); border-radius:8px; font-size:0.875rem; font-family:'Inter',sans-serif; font-weight:500; color:var(--text-muted); cursor:pointer; }
.qa-btn-ghost:hover { border-color:var(--text-muted); color:var(--text-primary); }
.qa-btn-confirm { display:inline-flex; align-items:center; padding:0.6rem 1.3rem; background:linear-gradient(135deg,#4f46e5,#6366f1); color:white; border:none; border-radius:8px; font-size:0.875rem; font-family:'Inter',sans-serif; font-weight:600; cursor:pointer; text-decoration:none; }
.qa-btn-confirm:hover { opacity:0.9; }
</style>
