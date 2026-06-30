@extends('layout')
@section('title', 'Create Quiz — SDF Platform')

@section('page-content')

<div class="lq-shell">

    {{-- ══ PAGE HEADER ══ --}}
    <div class="lq-page-header">
        <div>
            <div class="lq-breadcrumb">
                <a href="/lecturer/dashboard">Lecturer Dashboard</a>
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                <span>Create Quiz</span>
            </div>
            <h1 class="lq-page-title">Quiz configuration and scheduling</h1>
        </div>
        <div class="lq-header-meta">
            <span class="lq-draft-pill">● Draft</span>
        </div>
    </div>

    <form method="POST" action="/lecturer/quizzes" id="quizForm">
        @csrf

        <div class="lq-grid">

            {{-- ══ LEFT: Config ══ --}}
            <div class="lq-card">
                <h2 class="lq-section-title">Quiz details</h2>

                {{-- Title --}}
                <div class="lq-field">
                    <label class="lq-label" for="quiz_title">Quiz title</label>
                    <input
                        type="text"
                        id="quiz_title"
                        name="quiz_title"
                        class="lq-input"
                        placeholder="e.g. Object oriented programming"
                        value="{{ old('quiz_title') }}"
                        required
                    >
                </div>

                {{-- Target category --}}
                <div class="lq-field">
                    <label class="lq-label" for="category">Target student category</label>
                    <div class="lq-select-wrap">
                        <select id="category" name="category" class="lq-select" required>
                            <option value="">— Select category —</option>
                            <option value="software_engineering" {{ old('category') == 'software_engineering' ? 'selected' : '' }}>Software Engineering</option>
                            <option value="computer_science"     {{ old('category') == 'computer_science'     ? 'selected' : '' }}>Computer Science</option>
                            <option value="information_tech"     {{ old('category') == 'information_tech'     ? 'selected' : '' }}>Information Technology</option>
                            <option value="networking"           {{ old('category') == 'networking'           ? 'selected' : '' }}>Networking</option>
                        </select>
                        <svg class="lq-select-arrow" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>

                {{-- Date / Time / Duration row --}}
                <div class="lq-row-3">
                    <div class="lq-field">
                        <label class="lq-label" for="quiz_date">Date</label>
                        <input type="date" id="quiz_date" name="quiz_date" class="lq-input" value="{{ old('quiz_date') }}" required>
                    </div>
                    <div class="lq-field">
                        <label class="lq-label" for="start_time">Start time</label>
                        <input type="time" id="start_time" name="start_time" class="lq-input" value="{{ old('start_time', '12:00') }}" required>
                    </div>
                    <div class="lq-field">
                        <label class="lq-label" for="duration">Duration</label>
                        <div class="lq-input-suffix">
                            <input type="number" id="duration" name="duration" class="lq-input" placeholder="30" min="5" max="180" value="{{ old('duration') }}" required>
                            <span class="lq-suffix">mins</span>
                        </div>
                    </div>
                </div>

                {{-- Instructions --}}
                <div class="lq-field">
                    <label class="lq-label" for="instructions">Instructions <span class="lq-optional">optional</span></label>
                    <textarea id="instructions" name="instructions" class="lq-textarea" rows="3" placeholder="e.g. Answer all questions. No calculators allowed.">{{ old('instructions') }}</textarea>
                </div>

                {{-- Attempt limit --}}
                <div class="lq-field">
                    <label class="lq-label">Attempt limit</label>
                    <div class="lq-radio-row">
                        <label class="lq-radio">
                            <input type="radio" name="attempts" value="1" {{ old('attempts','1') == '1' ? 'checked' : '' }}>
                            <span>1 attempt</span>
                        </label>
                        <label class="lq-radio">
                            <input type="radio" name="attempts" value="2" {{ old('attempts') == '2' ? 'checked' : '' }}>
                            <span>2 attempts</span>
                        </label>
                        <label class="lq-radio">
                            <input type="radio" name="attempts" value="unlimited" {{ old('attempts') == 'unlimited' ? 'checked' : '' }}>
                            <span>Unlimited</span>
                        </label>
                    </div>
                </div>

                {{-- Shuffle --}}
                <div class="lq-toggle-row">
                    <div>
                        <div class="lq-toggle-label">Shuffle questions</div>
                        <div class="lq-toggle-sub">Questions appear in random order for each student</div>
                    </div>
                    <label class="lq-toggle">
                        <input type="checkbox" name="shuffle" value="1" {{ old('shuffle') ? 'checked' : '' }}>
                        <span class="lq-toggle-track"><span class="lq-toggle-thumb"></span></span>
                    </label>
                </div>

                <div class="lq-toggle-row">
                    <div>
                        <div class="lq-toggle-label">Show results immediately</div>
                        <div class="lq-toggle-sub">Students see their score right after submitting</div>
                    </div>
                    <label class="lq-toggle">
                        <input type="checkbox" name="show_results" value="1" checked>
                        <span class="lq-toggle-track"><span class="lq-toggle-thumb"></span></span>
                    </label>
                </div>
            </div>

            {{-- ══ RIGHT: Question Builder ══ --}}
            <div class="lq-card">
                <div class="lq-qb-header">
                    <h2 class="lq-section-title">Question builder matrix</h2>
                    <span class="lq-q-count" id="qCount">0 questions</span>
                </div>

                {{-- Summary bar --}}
                <div class="lq-summary-bar">
                    <div class="lq-summary-item">
                        <span class="lq-summary-num" id="totalMarks">0</span>
                        <span class="lq-summary-label">Total marks</span>
                    </div>
                    <div class="lq-summary-divider"></div>
                    <div class="lq-summary-item">
                        <span class="lq-summary-num" id="mcqCount">0</span>
                        <span class="lq-summary-label">MCQ</span>
                    </div>
                    <div class="lq-summary-divider"></div>
                    <div class="lq-summary-item">
                        <span class="lq-summary-num" id="shortCount">0</span>
                        <span class="lq-summary-label">Short answer</span>
                    </div>
                    <div class="lq-summary-divider"></div>
                    <div class="lq-summary-item">
                        <span class="lq-summary-num" id="tfCount">0</span>
                        <span class="lq-summary-label">True/False</span>
                    </div>
                </div>

                {{-- Table --}}
                <div class="lq-table-wrap">
                    <table class="lq-table" id="questionsTable">
                        <thead>
                            <tr>
                                <th class="lq-th-num">#</th>
                                <th>Question</th>
                                <th>Type</th>
                                <th>Marks</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="questionRows">
                            {{-- Rows injected by JS --}}
                        </tbody>
                    </table>
                </div>

                {{-- Empty state --}}
                <div class="lq-empty" id="emptyState">
                    <svg width="36" height="36" fill="none" stroke="#94a3b8" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <p>No questions yet.<br>Click <strong>Add question</strong> to begin.</p>
                </div>

                {{-- Add question button --}}
                <button type="button" class="lq-add-btn" onclick="addQuestion()">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Add question
                </button>
            </div>

        </div>{{-- end grid --}}

        {{-- ══ ACTION BAR ══ --}}
        <div class="lq-action-bar">
            <a href="/lecturer/dashboard" class="lq-btn-ghost">Cancel</a>
            <button type="submit" name="action" value="draft" class="lq-btn-secondary">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                Save as draft
            </button>
            <button type="submit" name="action" value="publish" class="lq-btn-publish">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Save and publish
            </button>
        </div>

    </form>
</div>

{{-- ══ QUESTION EDIT MODAL ══ --}}
<div class="lq-modal-overlay" id="modalOverlay" onclick="closeModalOutside(event)">
    <div class="lq-modal" id="questionModal">
        <div class="lq-modal-header">
            <h3 class="lq-modal-title" id="modalTitle">Edit question</h3>
            <button type="button" class="lq-modal-close" onclick="closeModal()">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="lq-modal-body">
            <div class="lq-field">
                <label class="lq-label">Question text</label>
                <textarea id="modalQuestionText" class="lq-textarea" rows="3" placeholder="Type your question here…"></textarea>
            </div>

            <div class="lq-row-2">
                <div class="lq-field">
                    <label class="lq-label">Question type</label>
                    <div class="lq-select-wrap">
                        <select id="modalType" class="lq-select" onchange="toggleOptions()">
                            <option value="MCQ">MCQ</option>
                            <option value="Short">Short answer</option>
                            <option value="TrueFalse">True / False</option>
                        </select>
                        <svg class="lq-select-arrow" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
                <div class="lq-field">
                    <label class="lq-label">Marks</label>
                    <input type="number" id="modalMarks" class="lq-input" value="2" min="1" max="20">
                </div>
            </div>

            {{-- MCQ options --}}
            <div id="mcqOptions">
                <label class="lq-label">Answer options <span class="lq-optional">(tick the correct one)</span></label>
                <div class="lq-options-list" id="optionsList">
                    <div class="lq-option-row">
                        <input type="radio" name="correct_opt" value="0" checked class="lq-opt-radio">
                        <input type="text" class="lq-input lq-opt-input" placeholder="Option A">
                        <button type="button" class="lq-opt-del" onclick="removeOption(this)">×</button>
                    </div>
                    <div class="lq-option-row">
                        <input type="radio" name="correct_opt" value="1" class="lq-opt-radio">
                        <input type="text" class="lq-input lq-opt-input" placeholder="Option B">
                        <button type="button" class="lq-opt-del" onclick="removeOption(this)">×</button>
                    </div>
                    <div class="lq-option-row">
                        <input type="radio" name="correct_opt" value="2" class="lq-opt-radio">
                        <input type="text" class="lq-input lq-opt-input" placeholder="Option C">
                        <button type="button" class="lq-opt-del" onclick="removeOption(this)">×</button>
                    </div>
                    <div class="lq-option-row">
                        <input type="radio" name="correct_opt" value="3" class="lq-opt-radio">
                        <input type="text" class="lq-input lq-opt-input" placeholder="Option D">
                        <button type="button" class="lq-opt-del" onclick="removeOption(this)">×</button>
                    </div>
                </div>
                <button type="button" class="lq-add-opt-btn" onclick="addOption()">+ Add option</button>
            </div>

            {{-- True/False options --}}
            <div id="tfOptions" style="display:none;">
                <label class="lq-label">Correct answer</label>
                <div class="lq-radio-row">
                    <label class="lq-radio"><input type="radio" name="tf_answer" value="true" checked><span>True</span></label>
                    <label class="lq-radio"><input type="radio" name="tf_answer" value="false"><span>False</span></label>
                </div>
            </div>
        </div>

        <div class="lq-modal-footer">
            <button type="button" class="lq-btn-ghost" onclick="closeModal()">Cancel</button>
            <button type="button" class="lq-btn-publish" onclick="saveQuestion()">Save question</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let questions = [];
let editingIndex = -1;
let optionCount = 4;

// ── Add / Edit / Delete questions ──────────────────────
function addQuestion() {
    editingIndex = -1;
    optionCount = 4;
    document.getElementById('modalTitle').textContent = 'Add question';
    document.getElementById('modalQuestionText').value = '';
    document.getElementById('modalType').value = 'MCQ';
    document.getElementById('modalMarks').value = '2';
    resetOptions();
    toggleOptions();
    openModal();
}

function editQuestion(idx) {
    editingIndex = idx;
    const q = questions[idx];
    document.getElementById('modalTitle').textContent = 'Edit question';
    document.getElementById('modalQuestionText').value = q.text;
    document.getElementById('modalType').value = q.type;
    document.getElementById('modalMarks').value = q.marks;
    if (q.type === 'MCQ') {
        rebuildOptions(q.options, q.correct);
    } else if (q.type === 'TrueFalse') {
        document.querySelector(`input[name="tf_answer"][value="${q.correct}"]`).checked = true;
    }
    toggleOptions();
    openModal();
}

function deleteQuestion(idx) {
    questions.splice(idx, 1);
    renderTable();
}

function moveQuestion(idx, dir) {
    const swapIdx = idx + dir;
    if (swapIdx < 0 || swapIdx >= questions.length) return;
    [questions[idx], questions[swapIdx]] = [questions[swapIdx], questions[idx]];
    renderTable();
}

function saveQuestion() {
    const text  = document.getElementById('modalQuestionText').value.trim();
    const type  = document.getElementById('modalType').value;
    const marks = parseInt(document.getElementById('modalMarks').value) || 2;
    if (!text) { alert('Please enter the question text.'); return; }

    let options = [], correct = null;
    if (type === 'MCQ') {
        document.querySelectorAll('.lq-opt-input').forEach(i => options.push(i.value.trim() || '(empty)'));
        const checked = document.querySelector('input[name="correct_opt"]:checked');
        correct = checked ? parseInt(checked.value) : 0;
    } else if (type === 'TrueFalse') {
        options = ['True', 'False'];
        const tf = document.querySelector('input[name="tf_answer"]:checked');
        correct = tf ? tf.value : 'true';
    } else {
        options = []; correct = null;
    }

    const q = { text, type, marks, options, correct };
    if (editingIndex >= 0) {
        questions[editingIndex] = q;
    } else {
        questions.push(q);
    }
    renderTable();
    closeModal();
}

// ── Render table ───────────────────────────────────────
function renderTable() {
    const tbody = document.getElementById('questionRows');
    const empty = document.getElementById('emptyState');
    const table = document.getElementById('questionsTable');
    tbody.innerHTML = '';

    if (questions.length === 0) {
        empty.style.display = 'flex';
        table.style.display = 'none';
    } else {
        empty.style.display = 'none';
        table.style.display = 'table';
        questions.forEach((q, i) => {
            const tr = document.createElement('tr');
            tr.className = 'lq-tr';
            tr.innerHTML = `
                <td class="lq-td-num">${i + 1}</td>
                <td class="lq-td-q">
                    <span class="lq-q-text">${escHtml(q.text)}</span>
                    ${q.type === 'MCQ' ? `<span class="lq-q-opts">${q.options.slice(0,2).map((o,j)=>`<span class="${j===q.correct?'lq-correct':''}">${String.fromCharCode(65+j)}. ${escHtml(o)}</span>`).join('')}…</span>` : ''}
                </td>
                <td><span class="lq-type-badge lq-type-${q.type.toLowerCase()}">${q.type === 'TrueFalse' ? 'T/F' : q.type}</span></td>
                <td class="lq-td-marks">${q.marks}</td>
                <td class="lq-td-actions">
                    <button type="button" class="lq-action-btn" onclick="moveQuestion(${i},-1)" title="Move up" ${i===0?'disabled':''}>↑</button>
                    <button type="button" class="lq-action-btn" onclick="moveQuestion(${i},1)" title="Move down" ${i===questions.length-1?'disabled':''}>↓</button>
                    <button type="button" class="lq-action-btn edit" onclick="editQuestion(${i})" title="Edit">✏️</button>
                    <button type="button" class="lq-action-btn del" onclick="deleteQuestion(${i})" title="Delete">🗑</button>
                </td>`;
            tbody.appendChild(tr);
        });
    }

    // Update summary
    document.getElementById('qCount').textContent = questions.length + (questions.length === 1 ? ' question' : ' questions');
    document.getElementById('totalMarks').textContent = questions.reduce((s, q) => s + q.marks, 0);
    document.getElementById('mcqCount').textContent   = questions.filter(q => q.type === 'MCQ').length;
    document.getElementById('shortCount').textContent = questions.filter(q => q.type === 'Short').length;
    document.getElementById('tfCount').textContent    = questions.filter(q => q.type === 'TrueFalse').length;

    // Inject hidden inputs for form submission
    document.querySelectorAll('.lq-hidden-q').forEach(e => e.remove());
    questions.forEach((q, i) => {
        addHidden(`questions[${i}][text]`,    q.text);
        addHidden(`questions[${i}][type]`,    q.type);
        addHidden(`questions[${i}][marks]`,   q.marks);
        addHidden(`questions[${i}][correct]`, q.correct);
        q.options.forEach((opt, j) => addHidden(`questions[${i}][options][${j}]`, opt));
    });
}

function addHidden(name, value) {
    const inp = document.createElement('input');
    inp.type = 'hidden'; inp.name = name; inp.value = value;
    inp.className = 'lq-hidden-q';
    document.getElementById('quizForm').appendChild(inp);
}

// ── Options ────────────────────────────────────────────
function resetOptions() {
    document.getElementById('optionsList').innerHTML = '';
    ['Option A','Option B','Option C','Option D'].forEach((p, i) => appendOption(p, i, i === 0));
    optionCount = 4;
}

function rebuildOptions(opts, correct) {
    document.getElementById('optionsList').innerHTML = '';
    opts.forEach((o, i) => {
        appendOption(`Option ${String.fromCharCode(65+i)}`, i, i === correct, o);
    });
    optionCount = opts.length;
}

function appendOption(placeholder, idx, checked = false, value = '') {
    const div = document.createElement('div');
    div.className = 'lq-option-row';
    div.innerHTML = `
        <input type="radio" name="correct_opt" value="${idx}" ${checked ? 'checked' : ''} class="lq-opt-radio">
        <input type="text" class="lq-input lq-opt-input" placeholder="${placeholder}" value="${escHtml(value)}">
        <button type="button" class="lq-opt-del" onclick="removeOption(this)">×</button>`;
    document.getElementById('optionsList').appendChild(div);
}

function addOption() {
    appendOption(`Option ${String.fromCharCode(65 + optionCount)}`, optionCount);
    optionCount++;
}

function removeOption(btn) {
    const rows = document.querySelectorAll('.lq-option-row');
    if (rows.length <= 2) { alert('Need at least 2 options.'); return; }
    btn.closest('.lq-option-row').remove();
    // Re-index radio values
    document.querySelectorAll('.lq-opt-radio').forEach((r, i) => r.value = i);
}

function toggleOptions() {
    const type = document.getElementById('modalType').value;
    document.getElementById('mcqOptions').style.display  = type === 'MCQ'       ? 'block' : 'none';
    document.getElementById('tfOptions').style.display   = type === 'TrueFalse' ? 'block' : 'none';
}

// ── Modal ──────────────────────────────────────────────
function openModal()  { document.getElementById('modalOverlay').classList.add('open'); }
function closeModal() { document.getElementById('modalOverlay').classList.remove('open'); }
function closeModalOutside(e) { if (e.target === document.getElementById('modalOverlay')) closeModal(); }

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Init
renderTable();
</script>
@endpush

<style>
/* ══════════════════════════════════════════
   LECTURER QUIZ DASHBOARD STYLES
══════════════════════════════════════════ */
.lq-shell {
    max-width: 1200px;
    margin: 0 auto;
    padding: 1.75rem 1.5rem 6rem;
}

/* Page header */
.lq-page-header {
    display: flex; align-items: flex-start; justify-content: space-between;
    margin-bottom: 1.5rem;
}
.lq-breadcrumb {
    display: flex; align-items: center; gap: 0.4rem;
    font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.4rem;
}
.lq-breadcrumb a { color: var(--brand); text-decoration: none; }
.lq-breadcrumb a:hover { text-decoration: underline; }
.lq-page-title { font-size: 1.35rem; font-weight: 700; color: var(--text-primary); }
.lq-draft-pill {
    background: #fef3c7; color: #92400e;
    font-size: 0.75rem; font-weight: 600;
    padding: 0.3rem 0.8rem; border-radius: 999px;
    margin-top: 0.3rem;
}

/* Two-column grid */
.lq-grid {
    display: grid;
    grid-template-columns: 1fr 1.3fr;
    gap: 1.25rem;
    align-items: start;
    margin-bottom: 1.25rem;
}
@media (max-width: 900px) { .lq-grid { grid-template-columns: 1fr; } }

/* Cards */
.lq-card {
    background: white;
    border-radius: 14px;
    padding: 1.5rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    border: 1px solid var(--border);
}
.lq-section-title {
    font-size: 0.95rem; font-weight: 700; color: var(--text-primary);
    margin-bottom: 1.1rem;
    padding-bottom: 0.7rem;
    border-bottom: 1px solid var(--border);
}

/* Fields */
.lq-field { margin-bottom: 1rem; }
.lq-label {
    display: block; margin-bottom: 0.4rem;
    font-size: 0.82rem; font-weight: 600; color: var(--text-primary);
}
.lq-optional { font-weight: 400; color: var(--text-muted); margin-left: 0.3rem; }
.lq-input {
    width: 100%; padding: 0.55rem 0.85rem;
    border: 1.5px solid var(--border); border-radius: 8px;
    font-size: 0.9rem; font-family: 'Inter', sans-serif;
    color: var(--text-primary); background: white;
    transition: border-color 0.18s, box-shadow 0.18s;
}
.lq-input:focus { outline: none; border-color: var(--brand); box-shadow: 0 0 0 3px rgba(79,70,229,0.1); }
.lq-textarea {
    width: 100%; padding: 0.55rem 0.85rem;
    border: 1.5px solid var(--border); border-radius: 8px;
    font-size: 0.875rem; font-family: 'Inter', sans-serif;
    color: var(--text-primary); resize: vertical; line-height: 1.5;
    transition: border-color 0.18s;
}
.lq-textarea:focus { outline: none; border-color: var(--brand); box-shadow: 0 0 0 3px rgba(79,70,229,0.1); }

.lq-select-wrap { position: relative; }
.lq-select {
    width: 100%; padding: 0.55rem 2rem 0.55rem 0.85rem;
    border: 1.5px solid var(--border); border-radius: 8px;
    font-size: 0.9rem; font-family: 'Inter', sans-serif;
    color: var(--text-primary); background: white;
    appearance: none; cursor: pointer;
    transition: border-color 0.18s;
}
.lq-select:focus { outline: none; border-color: var(--brand); box-shadow: 0 0 0 3px rgba(79,70,229,0.1); }
.lq-select-arrow { position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); pointer-events: none; }

.lq-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.75rem; }
.lq-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
.lq-input-suffix { position: relative; }
.lq-input-suffix .lq-input { padding-right: 2.5rem; }
.lq-suffix {
    position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%);
    font-size: 0.78rem; color: var(--text-muted); pointer-events: none;
}

/* Radio */
.lq-radio-row { display: flex; gap: 1rem; flex-wrap: wrap; }
.lq-radio {
    display: flex; align-items: center; gap: 0.4rem;
    font-size: 0.875rem; cursor: pointer; color: var(--text-primary);
}
.lq-radio input { accent-color: var(--brand); width: 15px; height: 15px; }

/* Toggle */
.lq-toggle-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0.75rem 0; border-top: 1px solid var(--border);
    gap: 1rem;
}
.lq-toggle-label { font-size: 0.875rem; font-weight: 600; color: var(--text-primary); }
.lq-toggle-sub { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.15rem; }
.lq-toggle { position: relative; cursor: pointer; flex-shrink: 0; }
.lq-toggle input { opacity: 0; width: 0; height: 0; position: absolute; }
.lq-toggle-track {
    display: block; width: 40px; height: 22px;
    background: var(--border); border-radius: 999px;
    transition: background 0.2s;
    position: relative;
}
.lq-toggle input:checked + .lq-toggle-track { background: var(--brand); }
.lq-toggle-thumb {
    position: absolute; top: 3px; left: 3px;
    width: 16px; height: 16px; background: white;
    border-radius: 50%; transition: left 0.2s;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}
.lq-toggle input:checked + .lq-toggle-track .lq-toggle-thumb { left: 21px; }

/* Question builder */
.lq-qb-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; padding-bottom: 0.7rem; border-bottom: 1px solid var(--border); }
.lq-q-count { font-size: 0.78rem; font-weight: 600; color: var(--text-muted); background: var(--bg); padding: 0.25rem 0.7rem; border-radius: 999px; }

/* Summary bar */
.lq-summary-bar {
    display: flex; align-items: center;
    background: #f8fafc; border: 1px solid var(--border);
    border-radius: 10px; padding: 0.65rem 1rem;
    margin-bottom: 1rem; gap: 0;
}
.lq-summary-item { flex: 1; text-align: center; }
.lq-summary-num { display: block; font-size: 1.25rem; font-weight: 700; color: var(--brand); line-height: 1; }
.lq-summary-label { font-size: 0.7rem; color: var(--text-muted); font-weight: 500; }
.lq-summary-divider { width: 1px; background: var(--border); align-self: stretch; margin: 0 0.5rem; }

/* Table */
.lq-table-wrap { overflow-x: auto; margin-bottom: 0.75rem; }
.lq-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
.lq-table thead th {
    padding: 0.55rem 0.7rem; text-align: left;
    font-size: 0.75rem; font-weight: 600; color: var(--text-muted);
    border-bottom: 1.5px solid var(--border);
    background: #f8fafc; text-transform: uppercase; letter-spacing: 0.04em;
}
.lq-th-num { width: 36px; }
.lq-tr { border-bottom: 1px solid var(--border); transition: background 0.12s; }
.lq-tr:hover { background: #f8fafc; }
.lq-td-num { padding: 0.7rem 0.7rem; color: var(--text-muted); font-weight: 600; font-size: 0.8rem; width: 36px; }
.lq-td-q { padding: 0.7rem 0.7rem; }
.lq-q-text { display: block; font-weight: 500; color: var(--text-primary); margin-bottom: 0.2rem; }
.lq-q-opts { display: flex; gap: 0.5rem; flex-wrap: wrap; }
.lq-q-opts span { font-size: 0.72rem; color: var(--text-muted); }
.lq-q-opts .lq-correct { color: var(--emerald); font-weight: 600; }
.lq-td-marks { padding: 0.7rem 0.7rem; font-weight: 700; color: var(--text-primary); text-align: center; width: 60px; }
.lq-td-actions { padding: 0.7rem 0.7rem; white-space: nowrap; width: 120px; }

.lq-type-badge { font-size: 0.7rem; font-weight: 700; padding: 2px 8px; border-radius: 999px; }
.lq-type-mcq       { background: #ede9fe; color: #5b21b6; }
.lq-type-short     { background: #dcfce7; color: #166534; }
.lq-type-truefalse { background: #fef3c7; color: #92400e; }

.lq-action-btn {
    background: none; border: none; cursor: pointer; padding: 0.2rem 0.35rem;
    border-radius: 5px; font-size: 0.82rem; transition: background 0.15s; color: var(--text-muted);
}
.lq-action-btn:hover:not([disabled]) { background: var(--border); }
.lq-action-btn[disabled] { opacity: 0.3; cursor: default; }
.lq-action-btn.edit:hover { background: #ede9fe; }
.lq-action-btn.del:hover  { background: #fee2e2; }

/* Empty state */
.lq-empty {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: 2rem; text-align: center; color: var(--text-muted);
    gap: 0.6rem; font-size: 0.875rem; line-height: 1.6;
}

/* Add question button */
.lq-add-btn {
    display: flex; align-items: center; justify-content: center; gap: 0.5rem;
    width: 100%; padding: 0.65rem;
    border: 1.5px dashed var(--brand); border-radius: 9px;
    background: none; color: var(--brand);
    font-size: 0.875rem; font-family: 'Inter', sans-serif; font-weight: 600;
    cursor: pointer; transition: all 0.18s;
    margin-top: 0.5rem;
}
.lq-add-btn:hover { background: #eef2ff; border-style: solid; }

/* Action bar */
.lq-action-bar {
    display: flex; align-items: center; justify-content: flex-end; gap: 0.75rem;
    background: white; border: 1px solid var(--border);
    border-radius: 12px; padding: 1rem 1.25rem;
    box-shadow: 0 -2px 12px rgba(0,0,0,0.05);
}
.lq-btn-ghost {
    padding: 0.6rem 1.1rem; background: none;
    border: 1.5px solid var(--border); border-radius: 8px;
    font-size: 0.875rem; font-family: 'Inter', sans-serif; font-weight: 500;
    color: var(--text-muted); cursor: pointer; text-decoration: none;
    transition: all 0.18s;
}
.lq-btn-ghost:hover { border-color: var(--text-muted); color: var(--text-primary); }
.lq-btn-secondary {
    display: flex; align-items: center; gap: 0.4rem;
    padding: 0.6rem 1.1rem;
    background: #f1f5f9; border: 1.5px solid var(--border); border-radius: 8px;
    font-size: 0.875rem; font-family: 'Inter', sans-serif; font-weight: 600;
    color: var(--text-primary); cursor: pointer; transition: all 0.18s;
}
.lq-btn-secondary:hover { background: var(--border); }
.lq-btn-publish {
    display: flex; align-items: center; gap: 0.4rem;
    padding: 0.6rem 1.3rem;
    background: linear-gradient(135deg, #059669, #10b981);
    border: none; border-radius: 8px;
    font-size: 0.875rem; font-family: 'Inter', sans-serif; font-weight: 600;
    color: white; cursor: pointer; transition: opacity 0.18s, transform 0.1s;
    box-shadow: 0 2px 8px rgba(16,185,129,0.35);
}
.lq-btn-publish:hover { opacity: 0.92; transform: translateY(-1px); }

/* ── Modal ── */
.lq-modal-overlay {
    position: fixed; inset: 0;
    background: rgba(15,23,42,0.45);
    backdrop-filter: blur(3px);
    display: none; align-items: center; justify-content: center;
    z-index: 9999; padding: 1rem;
}
.lq-modal-overlay.open { display: flex; }
.lq-modal {
    background: white; border-radius: 16px;
    width: 100%; max-width: 540px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
    overflow: hidden;
}
.lq-modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1.1rem 1.3rem;
    border-bottom: 1px solid var(--border);
}
.lq-modal-title { font-size: 1rem; font-weight: 700; }
.lq-modal-close {
    background: none; border: none; cursor: pointer; color: var(--text-muted);
    padding: 0.25rem; border-radius: 6px; transition: background 0.15s;
}
.lq-modal-close:hover { background: var(--border); color: var(--text-primary); }
.lq-modal-body { padding: 1.2rem 1.3rem; max-height: 65vh; overflow-y: auto; }
.lq-modal-footer {
    display: flex; justify-content: flex-end; gap: 0.65rem;
    padding: 0.9rem 1.3rem; border-top: 1px solid var(--border);
    background: #f8fafc;
}

/* Options */
.lq-options-list { display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 0.6rem; }
.lq-option-row { display: flex; align-items: center; gap: 0.5rem; }
.lq-opt-radio { accent-color: var(--brand); width: 15px; height: 15px; flex-shrink: 0; }
.lq-opt-input { flex: 1; }
.lq-opt-del {
    background: none; border: none; cursor: pointer; color: var(--text-muted);
    font-size: 1.1rem; padding: 0.1rem 0.3rem; border-radius: 4px;
    transition: background 0.15s, color 0.15s; flex-shrink: 0;
}
.lq-opt-del:hover { background: #fee2e2; color: var(--rose); }
.lq-add-opt-btn {
    background: none; border: none; color: var(--brand);
    font-size: 0.82rem; font-weight: 600; font-family: 'Inter', sans-serif;
    cursor: pointer; padding: 0.25rem 0;
}
.lq-add-opt-btn:hover { text-decoration: underline; }
</style>
