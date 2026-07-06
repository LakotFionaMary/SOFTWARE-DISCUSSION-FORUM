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
    document.getElementById('quizTitle').textContent = attempt.quiz.title;

    const duration = attempt.quiz.configuration?.duration_minutes ?? 10;
    secondsLeft = duration * 60;
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
    resultBox.innerHTML = `<h3>Score: ${result.score}</h3><p class="muted">${autoSubmitted ? 'Auto-submitted when time expired.' : 'Submitted manually.'}</p>`;
}

document.getElementById('submitBtn').addEventListener('click', () => submitQuiz(false));

startQuiz();
</script>
@endsection
