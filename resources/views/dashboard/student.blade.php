@extends('layouts.app')

@section('title', 'Student Dashboard')

@section('content')
<style>
    /* ---------- Groups panel: single drill-down view (groups -> topics -> posts) ---------- */
    #groupsBrowserContent { margin-top: 12px; }

    .back-link { display: inline-block; color: var(--accent); font-weight: 600; cursor: pointer; }
    .back-link:hover { text-decoration: underline; }

    .group-item, .topic-item {
        display: flex; align-items: center; justify-content: space-between; gap: 10px;
        padding: 12px 4px; border-bottom: 1px solid var(--line); cursor: pointer;
    }
    .group-item:last-child, .topic-item:last-child { border-bottom: none; }
    .group-item:hover, .topic-item:hover { background: #eef2f1; }
    .group-item .group-info { min-width: 0; }
    .group-item .group-info strong, .topic-item strong { display: block; font-size: 15px; }
    .group-item .group-info .muted, .topic-item .muted { font-size: 12.5px; }
    .group-item .join-btn {
        flex-shrink: 0; padding: 5px 12px; font-size: 12.5px; border-radius: 14px;
        background: var(--accent); color: #fff; border: none; cursor: pointer;
    }
    .group-item .join-btn:hover { background: var(--accent-dark); }

    /* Chat thread + composer, reused from the standalone topic page so the
       inline preview here looks/feels the same. No fixed height/scrolling —
       it simply grows with the conversation. */
    .chat-thread {
        display: flex; flex-direction: column; gap: 4px;
        background: var(--paper); border: 1px solid var(--line); border-radius: var(--radius);
        padding: 16px; min-height: 260px; flex: 1;
    }
    .msg-group { display: flex; flex-direction: column; margin: 10px 0; max-width: 78%; }
    .msg-group.mine { align-self: flex-end; align-items: flex-end; }
    .msg-group.theirs { align-self: flex-start; align-items: flex-start; }
    /* Reply thread: a connecting guide line + indent applied straight to the
       message itself (one modifier class) instead of an extra wrapper div. */
    .msg-group.is-reply { margin-left: 26px; max-width: calc(78% - 26px); padding-left: 14px; border-left: 2px solid var(--line); }

    .bubble {
        padding: 8px 12px; border-radius: 12px;
        font-size: 14px; line-height: 1.4; word-wrap: break-word;
    }
    .msg-group.mine .bubble { background: #d9fdd3; border-bottom-right-radius: 3px; }
    .msg-group.theirs .bubble { background: #fff; border: 1px solid var(--line); border-bottom-left-radius: 3px; }
    .bubble-author { display: block; font-size: 12px; font-weight: 600; color: var(--accent); margin-bottom: 2px; }
    .msg-group.mine .bubble-author { display: none; }
    .bubble-text { margin: 0; white-space: pre-wrap; }

    .msg-actions { display: flex; align-items: center; gap: 8px; margin: 4px 2px 0; font-size: 11.5px; }
    .msg-group.mine .msg-actions { flex-direction: row-reverse; }
    .msg-actions .reply-link,
    .msg-actions .forward-link { color: var(--accent); cursor: pointer; }
    .msg-actions .reply-link:hover,
    .msg-actions .forward-link:hover { text-decoration: underline; }
    .msg-actions .msg-time { color: var(--slate); }

    .composer {
        display: flex; align-items: flex-end; gap: 8px; margin-top: 14px;
        background: #fff; border: 1px solid var(--line); border-radius: 24px; padding: 8px 8px 8px 16px;
    }
    .composer textarea {
        flex: 1; border: none; resize: none; outline: none; font-size: 14px; padding: 6px 0;
        max-height: 120px; font-family: inherit;
    }
    .composer-send {
        background: var(--accent); color: #fff; border: none; border-radius: 50%;
        width: 38px; height: 38px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; cursor: pointer;
    }
    .composer-send svg { width: 18px; height: 18px; }

    /* Icon button specific layout matching setup adjustments */
    .icon-btn {
        display: inline-flex; align-items: center; justify-content: center;
        background: transparent; border: none; color: var(--slate);
        border-radius: 50%; width: 32px; height: 32px; cursor: pointer; padding: 0;
    }
    .icon-btn svg { width: 16px; height: 16px; flex-shrink: 0; }
    .icon-btn:hover { background: rgba(0,0,0,.06); color: var(--accent); }

    /* ---------- Forward message modal ---------- */
    .modal-overlay {
        position: fixed; inset: 0; background: rgba(15, 23, 20, 0.45);
        display: none; align-items: center; justify-content: center; z-index: 1000; padding: 16px;
    }
    .modal-overlay.open { display: flex; }
    .modal-box {
        background: #fff; border-radius: var(--radius); padding: 20px;
        width: 100%; max-width: 420px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    .modal-preview {
        background: #f3f8f5; border-left: 3px solid var(--accent); padding: 8px 10px;
        border-radius: 0 6px 6px 0; font-size: 13px; max-height: 90px; overflow-y: auto; white-space: pre-wrap;
    }
    .modal-box select {
        width: 100%; padding: 7px; border: 1px solid var(--line); border-radius: 6px; font-family: inherit;
    }
</style>

<div class="eyebrow">Student Dashboard</div>
<h1 id="welcome">Loading your dashboard…</h1>

<div class="dash-shell">
    <nav class="dash-sidebar">
        <a href="#" class="dash-sidebar-item" data-target="panel-groups"><span class="icon">👥</span> Groups</a>
        <a href="#" class="dash-sidebar-item" id="groupAdminTab" data-target="panel-group-admin" style="display:none;"><span class="icon">🛡️</span> Group Admin</a>
        <a href="#" class="dash-sidebar-item" data-target="panel-grades"><span class="icon">🎓</span> My Grades</a>
        <a href="#" class="dash-sidebar-item" data-target="panel-quizzes"><span class="icon">📝</span> Quizzes</a>
        <a href="#" class="dash-sidebar-item" data-target="panel-recommendations"><span class="icon">✨</span> Recommendations</a>
        <a href="#" class="dash-sidebar-item" data-target="panel-notifications"><span class="icon">🔔</span> Notifications</a>
    </nav>

    <div class="dash-main">
        <div class="dash-panel" id="panel-group-admin">
            <div class="section-title"><h2 style="margin:0;">Group Admin</h2></div>
            <p class="muted">Groups you administer. As a group admin you can view full group statistics, the same view a lecturer sees for their own groups.</p>
            <div id="groupAdminList"></div>
        </div>

        <div class="dash-panel" id="panel-groups">
            <div class="section-title"><h2 style="margin:0;">Groups</h2></div>
            <div class="card" id="groupsBrowserContent">Loading groups…</div>
        </div>

        <div class="dash-panel" id="panel-grades">
            <div class="section-title"><h2 style="margin:0;">My Grades</h2></div>
            <div id="studentGrades" class="card muted">Loading your grades…</div>
        </div>

        <div class="dash-panel" id="panel-quizzes">
            <div class="section-title"><h2 style="margin:0;">Published Quizzes</h2></div>
            <div id="studentQuizzes" class="card muted">Loading published quizzes…</div>
        </div>

        <div class="dash-panel" id="panel-recommendations">
            <div class="section-title"><h2 style="margin:0;">Recommended Topics</h2></div>
            <div id="recommendations" class="card muted">Loading recommendations…</div>
        </div>

        <div class="dash-panel" id="panel-notifications">
            <div class="section-title"><h2 style="margin:0;">Notifications</h2></div>
            <div id="notifications" class="card muted">Loading notifications…</div>
        </div>
    </div>
</div>

<div class="modal-overlay" id="forwardModalOverlay">
    <div class="modal-box">
        <h3 style="margin-top:0;">Forward message</h3>
        <div class="modal-preview" id="forwardPreview"></div>

        <label class="muted" style="display:block; margin:14px 0 4px;">Group</label>
        <select id="forwardGroupSelect"></select>

        <label class="muted" style="display:block; margin:12px 0 4px;">Topic</label>
        <select id="forwardTopicSelect"></select>

        <div style="display:flex; gap:8px; margin-top:18px; justify-content:flex-end;">
            <button class="btn secondary" type="button" onclick="closeForwardModal()">Cancel</button>
            <button class="btn" type="button" onclick="confirmForward()">Forward</button>
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
        if (window.CURRENT_ROLE !== 'student') {
            window.location.replace(window.CURRENT_ROLE === 'administrator' ? '/dashboard/admin' : '/dashboard/lecturer');
            return;
        }
        document.getElementById('welcome').textContent = `Welcome, ${me.full_name}`;
    }

    /* ---------- Groups panel: single drill-down view ---------- */
    let browseView = 'groups'; // 'groups' | 'topics' | 'posts'
    let activeBrowseGroupId = null;
    let activeBrowseGroupName = '';
    let activeBrowseTopicId = null;
    let activeBrowseTopicTitle = '';
    let currentTopicMessages = []; 

    function escAttr(str) {
        return (str || '').replace(/'/g, "\\'");
    }

    async function loadGroups() {
        const data = await api('/groups');
        const groups = (data && (data.data || data)) || [];
        myGroups = groups;
        renderGroupAdminPanel(groups);
        renderGroupsBrowser();
    }

    function renderGroupsBrowser() {
        const el = document.getElementById('groupsBrowserContent');
        if (browseView === 'topics') {
            el.innerHTML = topicsViewHtml();
            loadBrowseTopics();
        } else if (browseView === 'posts') {
            el.innerHTML = postsViewHtml();
            loadBrowsePosts();
        } else {
            el.innerHTML = groupsViewHtml();
        }
    }

    function groupsViewHtml() {
        const rows = myGroups.map(g => {
            const joined = g.is_member || g.is_group_admin;
            return `
                <div class="group-item" data-group-id="${g.group_id}" onclick="openGroupTopics(${g.group_id}, '${escAttr(g.name)}')">
                    <div class="group-info">
                        <strong>${g.name}</strong>
                        <div class="muted">${g.members_count ?? 0} members · ${g.topics_count ?? 0} topics</div>
                    </div>
                    ${joined
                        ? '<span class="badge role-student">Joined</span>'
                        : `<button type="button" class="join-btn" onclick="joinGroupInline(event, ${g.group_id})">Join</button>`
                    }
                </div>
            `;
        }).join('') || '<div class="empty-state">No groups exist yet.</div>';

        const createGroupCard = `
            <div class="card" style="border-left: 4px solid #4f46e5; margin-top: 12px;">
                <h3>Create a new group</h3>
                <form id="createGroupForm">
                    <input type="text" id="groupName" placeholder="Group name (e.g. CS301 Databases)" required style="width:100%; padding:7px; margin-bottom:8px;">
                    <textarea id="groupDescription" placeholder="What is this group for?" rows="2" style="width:100%; padding:7px; margin-bottom:8px;"></textarea>
                    <button class="btn" type="submit">Create group</button>
                </form>
            </div>
        `;

        return rows + createGroupCard;
    }

    function topicsViewHtml() {
        return `
            <a class="back-link" onclick="browseGoBack()">← Back to groups</a>
            <h3 style="margin: 12px 0 2px;">${activeBrowseGroupName}</h3>
            <p class="muted" style="margin: 0 0 14px;">Topics in this group</p>
            <form id="newTopicFormInline" style="margin-bottom:14px;">
                <input type="text" id="newTopicTitleInline" placeholder="New topic title…" required style="width:100%; padding:7px; margin-bottom:6px;">
                <button class="btn" type="submit" style="width:100%;">+ New Topic</button>
            </form>
            <div id="groupTopicsList" class="muted">Loading topics…</div>
        `;
    }

    function postsViewHtml() {
        return `
            <a class="back-link" onclick="browseGoBack()">← Back to topics</a>
            <div style="display:flex; align-items:center; justify-content:space-between; margin: 12px 0 14px;">
                <h3 style="margin:0;">${activeBrowseTopicTitle}</h3>
                <button class="icon-btn" type="button" onclick="exportDashTopicPdf()" title="Download PDF Export">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                </button>
            </div>
            <div class="chat-thread" id="dashPosts"><div class="muted">Loading messages…</div></div>
            <form class="composer" id="dashComposerForm">
                <textarea id="dashComposerInput" rows="1" placeholder="Type a message…" required
                    oninput="this.style.height='auto'; this.style.height=(this.scrollHeight)+'px';"></textarea>
                <button class="composer-send" type="submit" title="Send">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                </button>
            </form>
        `;
    }

    function openGroupTopics(groupId, groupName) {
        activeBrowseGroupId = groupId;
        activeBrowseGroupName = groupName;
        activeBrowseTopicId = null;
        browseView = 'topics';
        renderGroupsBrowser();
    }
    window.openGroupTopics = openGroupTopics;

    function openTopicPosts(topicId, title) {
        activeBrowseTopicId = topicId;
        activeBrowseTopicTitle = title;
        browseView = 'posts';
        renderGroupsBrowser();
    }
    window.openTopicPosts = openTopicPosts;

    function browseGoBack() {
        if (browseView === 'posts') {
            browseView = 'topics';
            activeBrowseTopicId = null;
        } else if (browseView === 'topics') {
            browseView = 'groups';
            activeBrowseGroupId = null;
        }
        renderGroupsBrowser();
    }
    window.browseGoBack = browseGoBack;

    async function joinGroupInline(event, groupId) {
        event.stopPropagation();
        const ok = window.confirm('By joining, you agree to the group rules (see /group-rules). Continue?');
        if (!ok) return;

        const response = await api(`/groups/${groupId}/join`, { method: 'POST', body: { rules_accepted: true } });
        if (response && response.message && !response.group_id) {
            alert(response.message);
        }
        await loadGroups();
    }
    window.joinGroupInline = joinGroupInline;

    function renderGroupAdminPanel(groups) {
        const adminGroups = groups.filter(g => g.is_group_admin);
        const tab = document.getElementById('groupAdminTab');

        tab.style.display = adminGroups.length ? 'flex' : 'none';

        document.getElementById('groupAdminList').innerHTML = adminGroups.map(g => `
            <div class="card">
                <strong>${g.name}</strong>
                <div class="muted">${g.members_count ?? 0} members · ${g.topics_count ?? 0} topics</div>
                <div style="margin-top: 8px;">
                    <a class="btn btn-secondary" href="/groups/${g.group_id}/statistics" style="padding: 4px 10px; font-size: 13px;">View group statistics</a>
                </div>
            </div>
        `).join('');
    }

    function timeOnly(dt) {
        if (!dt) return '';
        return new Date(dt).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    async function loadBrowseTopics() {
        if (!activeBrowseGroupId) return;
        const listEl = document.getElementById('groupTopicsList');
        if (!listEl) return;

        const data = await api(`/groups/${activeBrowseGroupId}/topics`);
        const topics = (data && (data.data || data)) || [];

        listEl.innerHTML = topics.map(t => `
            <div class="topic-item" data-topic-id="${t.topic_id}" onclick="openTopicPosts(${t.topic_id}, '${escAttr(t.title)}')">
                <strong>${t.title}</strong>
                <div class="muted">${t.posts_count ?? 0} ${(t.posts_count === 1) ? 'reply' : 'replies'}</div>
            </div>
        `).join('') || '<div class="empty-state">No topics yet — start one above.</div>';
    }

    async function loadBrowsePosts() {
        if (!activeBrowseTopicId) return;
        const container = document.getElementById('dashPosts');
        if (!container) return;

        const t = await api(`/topics/${activeBrowseTopicId}`);
        if (!t || t.message) {
            container.innerHTML = `<div class="muted">${(t && t.message) || 'This topic could not be loaded.'}</div>`;
            return;
        }

        const myId = window.CURRENT_USER ? window.CURRENT_USER.user_id : null;
        const posts = t.posts || [];

        currentTopicMessages = [];

        container.innerHTML = posts.map(p => {
            const mine = p.author_id === myId;
            const side = mine ? 'mine' : 'theirs';
            const authorName = p.author ? (p.author.full_name || p.author.name) : 'User';

            const repliesHtml = (p.replies || []).map(r => {
                const replyMine = r.author_id === myId;
                const replySide = replyMine ? 'mine' : 'theirs';
                const replyAuthorName = r.author ? (r.author.full_name || r.author.name) : 'User';
                return renderMsgGroup(replySide, replyAuthorName, r.content, timeOnly(r.replied_at || r.created_at), true);
            }).join('');

            return renderMsgGroup(side, authorName, p.content, timeOnly(p.posted_at || p.created_at), false) + repliesHtml;
        }).join('') || '<div class="muted">No messages yet in this topic — start the discussion below.</div>';

        container.scrollTop = container.scrollHeight;
    }

    function renderMsgGroup(side, authorName, content, time, isReply) {
        const msgIndex = currentTopicMessages.length;
        currentTopicMessages.push({ author: authorName, content });

        return `
            <div class="msg-group ${side}${isReply ? ' is-reply' : ''}">
                <div class="bubble">
                    <span class="bubble-author">${authorName}</span>
                    <p class="bubble-text">${content}</p>
                </div>
                <div class="msg-actions">
                    <a class="reply-link" onclick="focusComposerWithMention('${authorName.replace(/'/g, "\\'")}')">Reply</a>
                    <a class="forward-link" onclick="openForwardModal(${msgIndex})">Forward</a>
                    <span class="msg-time">${time}</span>
                </div>
            </div>
        `;
    }

    function focusComposerWithMention(authorName) {
        const textarea = document.getElementById('dashComposerInput');
        if (!textarea) return;
        if (!textarea.value.trim()) {
            textarea.value = `@${authorName} `;
            textarea.style.height = 'auto';
            textarea.style.height = (textarea.scrollHeight) + 'px';
        }
        textarea.focus();
    }
    window.focusComposerWithMention = focusComposerWithMention;

    async function exportDashTopicPdf() {
        if (!activeBrowseTopicId) return;
        try {
            const token = localStorage.getItem('sdf_token');
            const headers = { 'Accept': 'application/pdf' };
            if (token) headers['Authorization'] = `Bearer ${token}`;

            const response = await fetch(window.location.origin + `/api/topics/${activeBrowseTopicId}/export`, { method: 'GET', headers });
            if (!response.ok) throw new Error(`Server returned status ${response.status}`);

            const pdfBlob = await response.blob();
            if (pdfBlob.size === 0) throw new Error('The server generated an empty file.');

            const blobUrl = window.URL.createObjectURL(pdfBlob);
            const link = document.createElement('a');
            link.style.display = 'none';
            link.href = blobUrl;
            link.download = `topic-${activeBrowseTopicId}.pdf`;
            document.body.appendChild(link);
            link.click();
            setTimeout(() => { link.remove(); window.URL.revokeObjectURL(blobUrl); }, 150);
        } catch (err) {
            alert(`Failed to export PDF: ${err.message}`);
        }
    }
    window.exportDashTopicPdf = exportDashTopicPdf;

    /* ---------- Forward message modal ---------- */
    let forwardMessageIndex = null;

    function openForwardModal(msgIndex) {
        const msg = currentTopicMessages[msgIndex];
        if (!msg) return;
        forwardMessageIndex = msgIndex;

        document.getElementById('forwardPreview').textContent = `${msg.author}: ${msg.content}`;

        const groupSelect = document.getElementById('forwardGroupSelect');
        groupSelect.innerHTML = myGroups.map(g => `<option value="${g.group_id}">${g.name}</option>`).join('')
            || '<option value="">You have not joined any groups</option>';
        groupSelect.onchange = () => populateForwardTopics(groupSelect.value);

        document.getElementById('forwardModalOverlay').classList.add('open');

        if (myGroups.length) {
            populateForwardTopics(myGroups[0].group_id);
        } else {
            document.getElementById('forwardTopicSelect').innerHTML = '';
        }
    }
    window.openForwardModal = openForwardModal;

    async function populateForwardTopics(groupId) {
        const topicSelect = document.getElementById('forwardTopicSelect');
        if (!groupId) { topicSelect.innerHTML = ''; return; }
        topicSelect.innerHTML = '<option>Loading…</option>';

        const data = await api(`/groups/${groupId}/topics`);
        const topics = (data && (data.data || data)) || [];
        topicSelect.innerHTML = topics.map(t => `<option value="${t.topic_id}">${t.title}</option>`).join('')
            || '<option value="">No topics in this group</option>';
    }

    function closeForwardModal() {
        document.getElementById('forwardModalOverlay').classList.remove('open');
        forwardMessageIndex = null;
    }
    window.closeForwardModal = closeForwardModal;

    async function confirmForward() {
        if (forwardMessageIndex === null) return;
        const msg = currentTopicMessages[forwardMessageIndex];
        const topicId = document.getElementById('forwardTopicSelect').value;
        if (!msg || !topicId) return;

        const forwardedContent = `Forwarded from ${msg.author}:\n${msg.content}`;
        await api(`/topics/${topicId}/posts`, { method: 'POST', body: { content: forwardedContent, exclude_user_ids: [] } });

        closeForwardModal();

        if (activeBrowseTopicId && Number(topicId) === activeBrowseTopicId) {
            loadBrowsePosts();
        }
    }
    window.confirmForward = confirmForward;

    document.getElementById('groupsBrowserContent').addEventListener('submit', async (e) => {
        if (e.target && e.target.id === 'createGroupForm') {
            e.preventDefault();
            const nameInput = document.getElementById('groupName');
            const descInput = document.getElementById('groupDescription');
            const response = await api('/groups', { method: 'POST', body: { name: nameInput.value, description: descInput.value } });
            if (response && response.message && !response.group_id) {
                alert(response.message);
                return;
            }
            nameInput.value = '';
            descInput.value = '';
            loadGroups();
        } else if (e.target && e.target.id === 'newTopicFormInline') {
            e.preventDefault();
            if (!activeBrowseGroupId) return;
            const input = document.getElementById('newTopicTitleInline');
            await api(`/groups/${activeBrowseGroupId}/topics`, { method: 'POST', body: { title: input.value } });
            input.value = '';
            loadBrowseTopics();
        } else if (e.target && e.target.id === 'dashComposerForm') {
            e.preventDefault();
            if (!activeBrowseTopicId) return;
            const textarea = document.getElementById('dashComposerInput');
            await api(`/topics/${activeBrowseTopicId}/posts`, { method: 'POST', body: { content: textarea.value, exclude_user_ids: [] } });
            textarea.value = '';
            textarea.style.height = 'auto';
            loadBrowsePosts();
        }
    });

    async function loadMyGrades() {
        const container = document.getElementById('studentGrades');
        if (!myGroups.length) {
            container.innerHTML = '<div class="empty-state">Join a group to see your grades.</div>';
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
        container.innerHTML = cards.join('') || '<div class="empty-state">No grades recorded yet.</div>';
    }

    let myAttemptsByQuiz = {};

    async function loadStudentQuizzes() {
        const container = document.getElementById('studentQuizzes');
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
        }).join('') || '<div class="empty-state">No published quizzes right now.</div>';
    }

    async function loadRecommendations() {
        const recs = await api('/recommendations') || [];
        document.getElementById('recommendations').innerHTML = recs.map(r => `
            <div><a href="/topics/${r.topic.topic_id}">${r.topic.title}</a></div>
        `).join('') || '<div class="empty-state">No recommendations yet.</div>';
    }

    async function loadNotifications() {
        const data = await api('/notifications');
        const notifications = (data && (data.data || data)) || [];
        document.getElementById('notifications').innerHTML = notifications.map(n => `
            <div style="margin-bottom: 4px;"><strong>${n.type}</strong>: ${n.message}</div>
        `).join('') || '<div class="empty-state">No notifications yet.</div>';
    }

    async function init() {
        initDashSidebar();
        await loadWelcome();
        await loadGroups();
        loadMyGrades();
        loadStudentQuizzes();
        loadRecommendations();
        loadNotifications();
    }

    init();
</script>
@endsection