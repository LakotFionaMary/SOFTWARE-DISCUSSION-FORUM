@extends('layouts.app')
 
@section('title', 'Lecturer Dashboard')
 
@section('content')
<div class="eyebrow">Lecturer Dashboard</div>
<h1 id="welcome">Loading your dashboard…</h1>
 
<div class="dash-shell">
    <div class="dash-main">
        <!-- ================= MY GROUPS ================= -->
        <div class="dash-panel" id="panel-groups">
            <div class="section-title"><h2 style="margin:0;">Groups</h2></div>
            <p class="muted">Groups you own or administer. Statistics and the gradebook are only available for groups where you're the lecturer or an active group admin.</p>
 
            <div class="card" style="border-left: 4px solid #4f46e5; margin-top: 12px;">
                <h3>Create a new group</h3>
                <form id="createGroupForm">
                    <input type="text" id="groupName" placeholder="Group name (e.g. CS301 Databases)" required>
                    <textarea id="groupDescription" placeholder="What is this group for?" rows="2"></textarea>
                    <button class="btn" type="submit">Create group</button>
                </form>
            </div>
 
            <div id="groupsList" class="muted" style="margin-top: 14px;">Loading your groups…</div>
        </div>
 
        <!-- ================= QUIZZES ================= -->
        <div class="dash-panel" id="panel-quizzes">
            <div class="section-title"><h2 style="margin:0;">Quizzes</h2></div>
 
            <div class="card" style="border-left: 4px solid #e11d48;">
                <h3>Create a new quiz</h3>
                <p class="muted">Schedule a quiz and build as many multiple-choice questions as you need.</p>
                <button class="btn btn-secondary" id="toggleQuizFormBtn" type="button">Open quiz form</button>
 
                <form id="quizConfigForm" style="display: none; margin-top: 15px; border-top: 1px solid #e2e8f0; padding-top: 15px;">
                    <div style="margin-bottom: 10px;">
                        <label>Target Group:</label>
                        <select id="quizGroupId" required style="width: 100%; padding: 6px;"></select>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <label>Quiz Title:</label>
                        <input type="text" id="quizTitle" placeholder="e.g. Quiz 1" required style="width: 100%; padding: 6px;">
                    </div>
                    <div style="margin-bottom: 10px;">
                        <label>Scheduled Date:</label>
                        <input type="date" id="scheduledDate" required style="width: 100%; padding: 6px;">
                    </div>
                    <div style="margin-bottom: 10px;">
                        <label>Start Time (24h format HH:MM):</label>
                        <input type="text" id="startTime" placeholder="14:30" required style="width: 100%; padding: 6px;">
                    </div>
                    <div style="margin-bottom: 10px;">
                        <label>Duration (Minutes):</label>
                        <input type="number" id="durationMinutes" placeholder="30" required style="width: 100%; padding: 6px;">
                    </div>
 
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-top: 15px;">
                        <h4 style="color:#e11d48; margin:0;">Question Matrix</h4>
                        <button class="btn btn-secondary" type="button" id="addQuestionBtn">+ Add question</button>
                    </div>
 
                    <div id="questionMatrix"></div>
 
                    <button class="btn" type="submit" style="background-color: #e11d48; color: white; width: 100%; margin-top: 15px;">Save & Publish Quiz</button>
                </form>
            </div>
 
            <h3 style="margin-top: 20px;">Your quizzes</h3>
            <div id="lecturerQuizzes" class="card muted">Loading your quizzes…</div>
        </div>
 
        <!-- ================= SCORING CRITERIA ================= -->
        <div class="dash-panel" id="panel-criteria">
            <div class="section-title"><h2 style="margin:0;">Scoring Criteria</h2></div>
            <div class="card" style="border-left: 4px solid #16a34a;">
                <p class="muted">Define how much each activity is worth per group. A group with no criteria for an activity type earns students zero participation points for it, even if they post.</p>
 
                <label>Group:</label>
                <select id="criteriaGroupId" style="width: 100%; padding: 6px; margin-bottom: 10px;"></select>
 
                <div id="criteriaList" class="muted" style="margin-bottom: 12px;">Select a group above.</div>
 
                <form id="criteriaForm" style="border-top: 1px solid #e2e8f0; padding-top: 12px;">
                    <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: flex-end;">
                        <div style="flex: 2; min-width: 160px;">
                            <label>Description</label>
                            <input type="text" id="criteriaDescription" placeholder="e.g. Discussion post" required style="width: 100%; padding: 6px;">
                        </div>
                        <div style="flex: 1; min-width: 140px;">
                            <label>Activity type</label>
                            <select id="criteriaActivityType" style="width: 100%; padding: 6px;">
                                <option value="post">Post</option>
                                <option value="reply">Reply</option>
                                <option value="quiz_attempt">Quiz attempt</option>
                                <option value="topic_creation">Topic creation</option>
                            </select>
                        </div>
                        <div style="width: 100px;">
                            <label>Max marks</label>
                            <input type="number" id="criteriaMaxMarks" min="0" step="0.5" value="10" required style="width: 100%; padding: 6px;">
                        </div>
                        <button class="btn" type="submit" style="padding: 8px 16px;">Add rule</button>
                    </div>
                </form>
            </div>
        </div>
 
        <!-- ================= NOTIFICATIONS ================= -->
        <div class="dash-panel" id="panel-notifications">
            <div class="section-title"><h2 style="margin:0;">Notifications</h2></div>
            <div id="notifications" class="card muted">Loading notifications…</div>
        </div>
    </div>
</div>
@endsection
 
@section('scripts')
<script>
    if (!localStorage.getItem('sdf_token')) { window.location.href = '/'; }
 
    let myGroups = [];
 
    async function loadWelcome() {
        const me = await loadCurrentUser();
        if (!me) return;
        if (window.CURRENT_ROLE === 'student') {
            window.location.replace('/dashboard/student' + window.location.search);
            return;
        }
        if (window.CURRENT_ROLE === 'administrator') {
            window.location.replace('/dashboard/admin' + window.location.search);
            return;
        }
        document.getElementById('welcome').textContent = `Welcome, ${me.full_name}`;
    }
 
    async function loadGroups() {
        const data = await api('/groups');
        const groups = (data && (data.data || data)) || [];
        myGroups = groups;
 
        document.getElementById('groupsList').innerHTML = groups.map(g => `
            <div class="card">
                <strong><a href="/groups/${g.group_id}">${g.name}</a></strong>
                ${g.is_owner ? '<span class="badge role-lecturer" style="margin-left:8px;">Owner</span>' : ''}
                <div class="muted">${g.description ?? ''} · ${g.members_count ?? 0} members · ${g.topics_count ?? 0} topics</div>
                ${g.can_view_group_statistics ? `
                    <div style="margin-top: 8px;">
                        <a class="btn btn-secondary" href="/groups/${g.group_id}/statistics" style="padding: 4px 10px; font-size: 13px;">Statistics</a>
                        <a class="btn btn-secondary" href="/groups/${g.group_id}/gradebook" style="padding: 4px 10px; font-size: 13px; margin-left: 6px;">Gradebook</a>
                    </div>
                ` : ''}
            </div>
        `).join('') || '<div class="empty-state">You are not in any groups yet. Create one above.</div>';
 
        ['quizGroupId', 'criteriaGroupId'].forEach(id => {
            const dropdown = document.getElementById(id);
            if (!dropdown) return;
            if (groups.length > 0) {
                dropdown.innerHTML = '<option value="">Select a group</option>' +
                    groups.map(g => `<option value="${g.group_id}">${g.name}</option>`).join('');
            } else {
                dropdown.innerHTML = '<option value="" disabled>Create a group first</option>';
            }
        });
    }
 
    document.getElementById('createGroupForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        await api('/groups', {
            method: 'POST',
            body: { name: document.getElementById('groupName').value, description: document.getElementById('groupDescription').value },
        });
        e.target.reset();
        loadGroups();
    });
 
    document.getElementById('toggleQuizFormBtn').addEventListener('click', () => {
        const form = document.getElementById('quizConfigForm');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    });
 
    let questionRowCount = 0;
 
    function addQuestionRow() {
        questionRowCount++;
        const wrapper = document.createElement('div');
        wrapper.className = 'question-row';
        wrapper.id = `qrow-${questionRowCount}`;
        wrapper.style.cssText = 'background:#f8fafc; padding:10px; border-radius:4px; margin-top:10px; position:relative;';
        wrapper.innerHTML = `
            <button type="button" class="removeQuestionBtn" style="position:absolute; top:8px; right:8px; background:none; border:none; color:#e11d48; cursor:pointer; font-weight:bold;">✕ remove</button>
            <div class="muted" style="margin-bottom:6px;">Question ${questionRowCount}</div>
            <input type="text" class="qText" placeholder="Enter question..." required style="width: 100%; margin-bottom: 8px; padding: 6px;">
            <input type="text" class="qOptA" placeholder="Option A" required style="width: 100%; margin-bottom: 4px; padding: 6px;">
            <input type="text" class="qOptB" placeholder="Option B" required style="width: 100%; margin-bottom: 4px; padding: 6px;">
            <input type="text" class="qOptC" placeholder="Option C" required style="width: 100%; margin-bottom: 4px; padding: 6px;">
            <input type="text" class="qOptD" placeholder="Option D" required style="width: 100%; margin-bottom: 8px; padding: 6px;">
            <label>Correct Answer Option:</label>
            <select class="qCorrect"><option>A</option><option>B</option><option>C</option><option>D</option></select>
            <label style="margin-left:10px;">Marks:</label>
            <input type="number" class="qMarks" value="1" min="1" style="width:60px; padding:4px;">
        `;
        document.getElementById('questionMatrix').appendChild(wrapper);
 
        wrapper.querySelector('.removeQuestionBtn').addEventListener('click', () => {
            if (document.querySelectorAll('.question-row').length > 1) {
                wrapper.remove();
            } else {
                alert('A quiz needs at least one question.');
            }
        });
    }
 
    document.getElementById('addQuestionBtn').addEventListener('click', addQuestionRow);
 
    function resetQuestionMatrix() {
        document.getElementById('questionMatrix').innerHTML = '';
        questionRowCount = 0;
        addQuestionRow();
    }
    resetQuestionMatrix();
 
    document.getElementById('quizConfigForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const groupId = document.getElementById('quizGroupId').value;
        const questions = Array.from(document.querySelectorAll('.question-row')).map(row => ({
            question_text: row.querySelector('.qText').value,
            option_a: row.querySelector('.qOptA').value,
            option_b: row.querySelector('.qOptB').value,
            option_c: row.querySelector('.qOptC').value,
            option_d: row.querySelector('.qOptD').value,
            correct_option: row.querySelector('.qCorrect').value,
            marks: parseInt(row.querySelector('.qMarks').value) || 1,
        }));
 
        const payload = {
            title: document.getElementById('quizTitle').value,
            scheduled_date: document.getElementById('scheduledDate').value,
            start_time: document.getElementById('startTime').value,
            duration_minutes: parseInt(document.getElementById('durationMinutes').value),
            questions,
        };
 
        const res = await api(`/groups/${groupId}/quizzes`, { method: 'POST', body: payload });
        if (res && !res.errors) {
            alert(`Quiz scheduled with ${questions.length} question(s). It will open automatically at the scheduled time.`);
            e.target.reset();
            resetQuestionMatrix();
            document.getElementById('quizConfigForm').style.display = 'none';
            loadLecturerQuizzes();
        } else {
            alert('Failed to save. Check that every question row is filled in and start time is HH:MM (e.g. 14:00).');
        }
    });
 
    async function loadLecturerQuizzes() {
        const container = document.getElementById('lecturerQuizzes');
        const quizzes = await api('/me/quizzes') || [];
 
        container.innerHTML = quizzes.map(q => {
            const groupName = q.group?.name ?? 'Unknown group';
            const schedule = q.configuration ? `${q.configuration.scheduled_date} at ${q.configuration.start_time}` : '';
            let actions = '';
            if (q.status === 'Scheduled') {
                actions += `<button class="btn publish-quiz-btn" type="button" data-quiz-id="${q.quiz_id}" style="padding: 6px 12px; font-size: 13px;">Publish</button>`;
            } else if (q.status === 'Open') {
                actions += `<button class="btn btn-secondary close-quiz-btn" type="button" data-quiz-id="${q.quiz_id}" style="padding: 6px 12px; font-size: 13px; margin-left: 6px;">Close</button>`;
            }
            actions += `<button class="btn btn-secondary view-results-btn" type="button" data-quiz-id="${q.quiz_id}" style="padding: 6px 12px; font-size: 13px; margin-left: 6px;">View results</button>`;
 
            return `
                <div class="card">
                    <strong>${q.title}</strong> <span class="muted">(${groupName})</span>
                    <div class="muted">Status: ${q.status}${schedule ? ' · ' + schedule : ''}</div>
                    <div style="margin-top: 8px;">${actions}</div>
                    <div class="quiz-results" data-quiz-id="${q.quiz_id}" style="margin-top: 10px; display: none;"></div>
                </div>
            `;
        }).join('') || '<div class="empty-state">You have not created any quizzes yet.</div>';
 
        container.querySelectorAll('.publish-quiz-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                await api(`/quizzes/${btn.dataset.quizId}/publish`, { method: 'POST' });
                loadLecturerQuizzes();
            });
        });
        container.querySelectorAll('.close-quiz-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                await api(`/quizzes/${btn.dataset.quizId}/close`, { method: 'POST' });
                loadLecturerQuizzes();
            });
        });
        container.querySelectorAll('.view-results-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const quizId = btn.dataset.quizId;
                const resultsBox = container.querySelector(`.quiz-results[data-quiz-id="${quizId}"]`);
                if (resultsBox.style.display === 'block') { resultsBox.style.display = 'none'; return; }
 
                const results = await api(`/quizzes/${quizId}/results`) || [];
                resultsBox.innerHTML = results.length ? `
                    <table>
                        <thead><tr><th>Student</th><th>Score</th><th>Submitted</th></tr></thead>
                        <tbody>
                            ${results.map(r => `
                                <tr>
                                    <td>${r.user?.full_name ?? 'Unknown'}</td>
                                    <td>${r.score}</td>
                                    <td>${r.submitted_at ? new Date(r.submitted_at).toLocaleString() : ''}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                ` : '<div class="empty-state">No submissions yet.</div>';
                resultsBox.style.display = 'block';
            });
        });
    }
 
    async function loadCriteriaList(groupId) {
        const listEl = document.getElementById('criteriaList');
        if (!groupId) { listEl.textContent = 'Select a group above.'; return; }
 
        listEl.textContent = 'Loading…';
        const criteria = await api(`/groups/${groupId}/scoring-criteria`) || [];
        listEl.innerHTML = criteria.length ? `
            <table>
                <thead><tr><th>Description</th><th>Activity</th><th>Max marks</th></tr></thead>
                <tbody>
                    ${criteria.map(c => `
                        <tr><td>${c.description}</td><td>${c.activity_type}</td><td>${Number(c.max_marks).toFixed(2)}</td></tr>
                    `).join('')}
                </tbody>
            </table>
        ` : '<div class="empty-state">No scoring criteria set for this group yet.</div>';
    }
 
    document.getElementById('criteriaGroupId').addEventListener('change', (e) => {
        loadCriteriaList(e.target.value);
    });
 
    document.getElementById('criteriaForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const groupId = document.getElementById('criteriaGroupId').value;
        if (!groupId) { alert('Please select a group first.'); return; }
 
        const payload = {
            description: document.getElementById('criteriaDescription').value,
            activity_type: document.getElementById('criteriaActivityType').value,
            max_marks: parseFloat(document.getElementById('criteriaMaxMarks').value),
        };
 
        const res = await api(`/groups/${groupId}/scoring-criteria`, { method: 'POST', body: payload });
        if (res && !res.errors) {
            e.target.reset();
            document.getElementById('criteriaMaxMarks').value = 10;
            loadCriteriaList(groupId);
        } else {
            alert('Failed to save scoring criteria.');
        }
    });
 
    async function loadNotifications() {
        const data = await api('/notifications');
        const notifications = (data && (data.data || data)) || [];
        document.getElementById('notifications').innerHTML = notifications.map(n => `
            <div style="margin-bottom: 4px;"><strong>${n.type}</strong>: ${n.message}</div>
        `).join('') || '<div class="empty-state">No notifications yet.</div>';
    }
 
    async function init() {
        initDashSidebar(document, 'panel-groups');
        await loadWelcome();
        await loadGroups();
        loadLecturerQuizzes();
        loadNotifications();
    }
 
    init();
</script>
@endsection
 

