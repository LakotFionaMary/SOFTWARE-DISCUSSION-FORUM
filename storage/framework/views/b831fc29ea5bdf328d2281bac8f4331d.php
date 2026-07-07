<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="eyebrow">Discussion Dashboard</div>
<h1 id="welcome">Loading your dashboard…</h1>

<!-- LECTURER SCREEN CONTROLS -->
<div id="lecturerControls" style="display: none;">


    <!-- Simple Quiz Creation Card -->
    <div class="card" style="border-left: 4px solid #e11d48; margin-bottom: 20px;">
        <h3>⚙️ Create a New Quiz</h3>
        <p class="muted">Schedule a quiz with one initial multiple-choice question for your group.</p>
        <button class="btn btn-secondary" id="toggleQuizFormBtn" type="button">Open Quiz Form</button>

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

            <h4 style="margin-top: 15px; color:#e11d48;">Question Details</h4>
            <div style="background: #f8fafc; padding: 10px; border-radius: 4px;">
                <input type="text" id="qText" placeholder="Enter question..." required style="width: 100%; margin-bottom: 8px; padding: 6px;">
                <input type="text" id="qOptA" placeholder="Option A" required style="width: 100%; margin-bottom: 4px; padding: 6px;">
                <input type="text" id="qOptB" placeholder="Option B" required style="width: 100%; margin-bottom: 4px; padding: 6px;">
                <input type="text" id="qOptC" placeholder="Option C" required style="width: 100%; margin-bottom: 4px; padding: 6px;">
                <input type="text" id="qOptD" placeholder="Option D" required style="width: 100%; margin-bottom: 8px; padding: 6px;">
                <label>Correct Answer Option:</label>
                <select id="qCorrect"><option>A</option><option>B</option><option>C</option><option>D</option></select>
            </div>

            <button class="btn" type="submit" style="background-color: #e11d48; color: white; width: 100%; margin-top: 15px;">Save & Publish Quiz</button>
        </form>
    </div>

    <!-- Lecturer's own quizzes: publish/close + view results -->
    <h2>Your quizzes</h2>
    <div id="lecturerQuizzes" class="card muted">Loading your quizzes…</div>
</div>

<!-- STUDENT HUB VIEW -->
<div id="studentControls" style="display: none;">
    <div class="card" style="background-color: #f8fafc; border-left: 4px solid #0ea5e9; margin-bottom: 20px;">
        <h3>🎓 Student Hub</h3>
        <p class="muted">Welcome to your forum dashboard. Browse your assigned groups and topics below.</p>
    </div>

    <h2>Published quizzes</h2>
    <div id="studentQuizzes" class="card muted">Loading published quizzes…</div>

    <h2>My grades</h2>
    <div id="studentGrades" class="card muted">Loading your grades…</div>
</div>

<!-- SHARED REGIONS -->
     <!-- Create Group Card -->
    <div class="card" style="border-left: 4px solid #4f46e5; margin-bottom: 20px;">
        <h3>Create a new Course Group</h3>
        <form id="createGroupForm">
            <input type="text" id="groupName" placeholder="Group name (e.g. CS301 Databases)" required>
            <textarea id="groupDescription" placeholder="What is this group for?" rows="2"></textarea>
            <button class="btn" type="submit">Create group</button>
        </form>
    </div>
<h2>Your groups</h2>
<div id="groups"></div>

<h2>Recommended topics</h2>
<div id="recommendations" class="card muted">Loading recommendations…</div>

<h2>Notifications</h2>
<div id="notifications" class="card muted">Loading notifications…</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    if (!localStorage.getItem('sdf_token')) {
        window.location.href = '/';
    }

    async function api(endpoint, options = {}) {
        const token = localStorage.getItem('sdf_token');
        options.headers = {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`,
            ...(options.headers || {})
        };
        if (options.body && typeof options.body === 'object') {
            options.body = JSON.stringify(options.body);
        }
        try {
            const response = await fetch(`/api${endpoint}`, options);
            if (response.status === 401) {
                localStorage.removeItem('sdf_token');
                window.location.href = '/';
                return null;
            }
            if (response.status === 204) return {};
            return await response.json();
        } catch (error) {
            console.error("API Error:", error);
            return null;
        }
    }

    let currentRole = 'student';

    async function loadMe() {
        const me = await api('/me');
        if (!me) return;

        // FIXED: Extract role from array using index [0] to match your AuthController response structure
        const rawRole = (me.roles && me.roles.length > 0) ? me.roles[0].role_name : 'Student';
        document.getElementById('welcome').textContent = `Welcome, ${me.full_name} (${rawRole})`;

        currentRole = rawRole.toLowerCase();
        if (currentRole === 'lecturer' || currentRole === 'administrator') {
            document.getElementById('lecturerControls').style.display = 'block';
        } else {
            document.getElementById('studentControls').style.display = 'block';
        }
    }

    let myGroups = [];

    async function loadGroups() {
        const data = await api('/groups');
        const groups = data.data || data || [];
        myGroups = groups;
        const container = document.getElementById('groups');

        const isStaff = currentRole === 'lecturer' || currentRole === 'administrator';

        container.innerHTML = groups.map(g => `
            <div class="card">
                <strong><a href="/groups/${g.group_id}">${g.name}</a></strong>
                <div class="muted">${g.description ?? ''} · ${g.members_count ?? 0} members · ${g.topics_count ?? 0} topics</div>
                ${isStaff ? `
                    <div style="margin-top: 8px;">
                        <a class="btn btn-secondary" href="/admin/statistics/${g.group_id}" style="padding: 4px 10px; font-size: 13px;">Statistics</a>
                        <a class="btn btn-secondary" href="/groups/${g.group_id}/gradebook" style="padding: 4px 10px; font-size: 13px; margin-left: 6px;">Gradebook</a>
                    </div>
                ` : ''}
            </div>
        `).join('') || '<div class="muted">You are not in any groups yet.</div>';

        const dropdown = document.getElementById('quizGroupId');
        if (dropdown && groups.length > 0) {
            dropdown.innerHTML = groups.map(g => `<option value="${g.group_id}">${g.name}</option>`).join('');
        } else if (dropdown) {
            dropdown.innerHTML = '<option value="" disabled>Create a group first</option>';
        }
    }

    /* ---------------- Lecturer: manage own quizzes ---------------- */

    async function loadLecturerQuizzes() {
        const container = document.getElementById('lecturerQuizzes');
        if (!container) return;

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
        }).join('') || '<div class="muted">You have not created any quizzes yet.</div>';

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

                if (resultsBox.style.display === 'block') {
                    resultsBox.style.display = 'none';
                    return;
                }

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
                ` : '<div class="muted">No submissions yet.</div>';
                resultsBox.style.display = 'block';
            });
        });
    }

    /* ---------------- Student: published quizzes + grades ---------------- */

    let myAttemptsByQuiz = {};

    async function loadStudentQuizzes() {
        const container = document.getElementById('studentQuizzes');
        if (!container) return;

        const attempts = await api('/me/quiz-attempts') || [];
        myAttemptsByQuiz = {};
        attempts.forEach(a => { myAttemptsByQuiz[a.quiz_id] = a; });

        const quizzes = await api('/me/quizzes') || [];

        container.innerHTML = quizzes.map(q => {
            const groupName = q.group?.name ?? 'Unknown group';
            const attempt = myAttemptsByQuiz[q.quiz_id];

            let action;
            if (attempt && attempt.submitted_at) {
                action = `<span class="muted">Submitted — your score: <strong>${attempt.score}</strong></span>`;
            } else if (q.status === 'Open') {
                action = `<a class="btn" href="/quizzes/${q.quiz_id}" style="padding: 6px 12px; font-size: 13px;">${attempt ? 'Resume quiz' : 'Take quiz'}</a>`;
            } else {
                action = '<span class="muted">Quiz closed — no attempt submitted.</span>';
            }

            return `
                <div class="card">
                    <strong>${q.title}</strong> <span class="muted">(${groupName})</span>
                    <div class="muted">Status: ${q.status}</div>
                    <div style="margin-top: 8px;">${action}</div>
                </div>
            `;
        }).join('') || '<div class="muted">No published quizzes right now.</div>';
    }

    async function loadMyGrades() {
        const container = document.getElementById('studentGrades');
        if (!container) return;

        if (!myGroups.length) {
            container.innerHTML = '<div class="muted">Join a group to see your grades.</div>';
            return;
        }

        const cards = await Promise.all(myGroups.map(async (g) => {
            const grade = await api(`/groups/${g.group_id}/my-grade`);
            if (!grade) return '';
            return `
                <div class="card">
                    <strong>${grade.group}</strong>
                    <div class="muted">Participation: ${Number(grade.participation_total).toFixed(2)} · Quizzes: ${Number(grade.quiz_total).toFixed(2)}</div>
                    <div><strong>Overall total: ${Number(grade.overall_total).toFixed(2)}</strong></div>
                </div>
            `;
        }));

        container.innerHTML = cards.join('') || '<div class="muted">No grades recorded yet.</div>';
    }

    async function loadRecommendations() {
        const recs = await api('/recommendations') || [];
        const container = document.getElementById('recommendations');
        container.innerHTML = recs.map(r => `
            <div><a href="/topics/${r.topic.topic_id}">${r.topic.title}</a></div>
        `).join('') || 'No recommendations yet.';
    }

    async function loadNotifications() {
        const data = await api('/notifications');
        const notifications = data.data || data || [];
        const container = document.getElementById('notifications');
        container.innerHTML = notifications.map(n => `
            <div style="margin-bottom: 4px;"><strong>${n.type}</strong>: ${n.message}</div>
        `).join('') || 'No notifications yet.';
    }

    document.getElementById('createGroupForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        await api('/groups', {
            method: 'POST',
            body: { name: document.getElementById('groupName').value, description: document.getElementById('groupDescription').value },
        });
        loadGroups();
        e.target.reset();
    });

    document.getElementById('toggleQuizFormBtn').addEventListener('click', () => {
        const form = document.getElementById('quizConfigForm');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    });

    document.getElementById('quizConfigForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const groupId = document.getElementById('quizGroupId').value;

        const payload = {
            title: document.getElementById('quizTitle').value,
            scheduled_date: document.getElementById('scheduledDate').value,
            start_time: document.getElementById('startTime').value,
            duration_minutes: parseInt(document.getElementById('durationMinutes').value),
            questions: [{
                question_text: document.getElementById('qText').value,
                option_a: document.getElementById('qOptA').value,
                option_b: document.getElementById('qOptB').value,
                option_c: document.getElementById('qOptC').value,
                option_d: document.getElementById('qOptD').value,
                correct_option: document.getElementById('qCorrect').value,
                marks: 1
            }]
        };

        const res = await api(`/groups/${groupId}/quizzes`, { method: 'POST', body: payload });
        if (res && !res.errors) {
            alert('Quiz scheduled and group users notified successfully!');
            e.target.reset();
            document.getElementById('quizConfigForm').style.display = 'none';
            loadLecturerQuizzes();
        } else {
            alert('Failed to save. Make sure your start time format is HH:MM (e.g. 14:00)');
        }
    });

    async function init() {
        await loadMe();
        await loadGroups();

        if (currentRole === 'lecturer' || currentRole === 'administrator') {
            loadLecturerQuizzes();
        } else {
            loadStudentQuizzes();
            loadMyGrades();
        }

        loadRecommendations();
        loadNotifications();
    }

    init();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /data/data/com.termux/files/home/forumG/resources/views/dashboard/index.blade.php ENDPATH**/ ?>