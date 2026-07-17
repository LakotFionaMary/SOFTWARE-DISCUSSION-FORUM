@extends('layouts.app')

@section('title', 'Quiz')

@section('content')
<div class="eyebrow">Quiz</div>
<h1 id="quizTitle">Loading…</h1>
<div class="card">
    <strong>Time remaining: <span id="timer">--:--</span></strong>
</div>
<form id="quizForm"></form>
<button class="btn" id="submitBtn">Submit quiz</button>
<div id="result" class="card" style="display:none;"></div>
@endsection

@section('scripts')
<script>
const quizId = {{ $quiz }};
let attempt = null;
let secondsLeft = 0;
let timerHandle = null;

async function startQuiz() {
    attempt = await api(`/quizzes/${quizId}/attempts/start`, { method: 'POST' });

    // The quiz isn't startable yet/anymore (not open, blacklisted, opens later,
    // or its scheduled window already ended) — the API returns a message and
    // no attempt_id in that case.
    if (!attempt || !attempt.attempt_id) {
        document.getElementById('quizTitle').textContent = 'Quiz unavailable';
        document.getElementById('quizForm').innerHTML = `
            <div class="card">${(attempt && attempt.message) || 'This quiz could not be started right now.'}</div>
        `;
        document.getElementById('submitBtn').style.display = 'none';
        return;
    }

    document.getElementById('quizTitle').textContent = attempt.quiz.title;

    // seconds_remaining is computed server-side from the quiz's actual
    // scheduled end time (start_time + duration_minutes), so every student
    // sees a countdown to the same real clock moment rather than a fresh
    // timer starting whenever they happen to click "Take quiz".
    secondsLeft = (typeof attempt.seconds_remaining === 'number')
        ? attempt.seconds_remaining
        : (attempt.quiz.configuration?.duration_minutes ?? 10) * 60;

    if (secondsLeft <= 0) {
        submitQuiz(true);
        return;
    }

    tickTimer();
    timerHandle = setInterval(tickTimer, 1000);

    renderQuestions(attempt.quiz.questions || []);
}

function tickTimer() {
    if (secondsLeft <= 0) {
        clearInterval(timerHandle);
        submitQuiz(true); // timer expiry -> auto-submit
        return;
    }
    secondsLeft--;
    const m = String(Math.floor(secondsLeft / 60)).padStart(2, '0');
    const s = String(secondsLeft % 60).padStart(2, '0');
    document.getElementById('timer').textContent = `${m}:${s}`;
}

function renderQuestions(questions) {
    document.getElementById('quizForm').innerHTML = questions.map((q, i) => `
        <div class="card">
            <strong>${i + 1}. ${q.question_text}</strong>
            ${['A','B','C','D'].map(opt => `
                <label>
                    <input type="radio" name="q${q.question_id}" value="${opt}" style="width:auto; margin-right:6px;">
                    ${opt}) ${q['option_' + opt.toLowerCase()]}
                </label>
            `).join('')}
        </div>
    `).join('');
}

async function submitQuiz(autoSubmitted = false) {
    clearInterval(timerHandle);
    const answers = Array.from(document.querySelectorAll('#quizForm .card')).map(card => {
        const radio = card.querySelector('input[type=radio]:checked');
        const name = card.querySelector('input[type=radio]').name;
        const questionId = parseInt(name.replace('q', ''));
        return { question_id: questionId, selected_option: radio ? radio.value : null };
    });

    const result = await api(`/attempts/${attempt.attempt_id}/submit`, {
        method: 'POST',
        body: { answers, auto_submitted: autoSubmitted },
    });

    document.getElementById('submitBtn').disabled = true;
    const resultBox = document.getElementById('result');
    resultBox.style.display = 'block';

    // If this window was auto-launched by the dashboard when the quiz's
    // configured time arrived (rather than opened by the student clicking
    // a link themselves), close it automatically a few seconds after an
    // auto-submit so it doesn't linger once the quiz window has ended.
    const isAutoLaunchedPopup = !!window.opener;
    const closeNote = (autoSubmitted && isAutoLaunchedPopup)
        ? ' This window will close automatically in a few seconds.'
        : '';

    resultBox.innerHTML = `<h3>Score: ${result.score}</h3><p class="muted">${autoSubmitted ? 'Auto-submitted when time expired.' : 'Submitted manually.'}${closeNote}</p>`;

    if (autoSubmitted && isAutoLaunchedPopup) {
        setTimeout(() => window.close(), 5000);
    }
}

document.getElementById('submitBtn').addEventListener('click', () => submitQuiz(false));

startQuiz();
</script>
@endsection