@extends('layouts.app')
 
@section('title', 'Administrator Dashboard')
 
@section('content')
<div class="eyebrow">Administrator Dashboard</div>
<h1 id="welcome">Loading your dashboard…</h1>
 
<div class="dash-shell">
    <div class="dash-main">
        <!-- ================= SYSTEM OVERVIEW ================= -->
        <div class="dash-panel" id="panel-overview">
            <div class="section-title"><h2 style="margin:0;">System Overview</h2></div>
            <div id="systemStats" class="stat-grid">
                <div class="stat-card"><div class="value">…</div><div class="label">Loading</div></div>
            </div>
            <div id="usersByRole" class="card muted" style="margin-top: 4px;"></div>
        </div>
 
        <!-- ================= GROUPS ================= -->
        <div class="dash-panel" id="panel-groups">
            <div class="section-title">
                <h2 style="margin:0;">Groups</h2>
            </div>
            <p class="muted">As an administrator you can view statistics and the gradebook for every group on the platform.</p>
            <div id="groupsList" class="muted">Loading groups…</div>
        </div>
 
        <!-- ================= INACTIVITY WARNINGS ================= -->
        <div class="dash-panel" id="panel-warnings">
            <div class="section-title"><h2 style="margin:0;">Inactivity Warnings</h2></div>
            <p class="muted">Users flagged for inactivity, most recent first.</p>
            <div id="warningsList" class="card muted">Loading…</div>
        </div>
    </div>
</div>
@endsection
 
@section('scripts')
<script>
    if (!localStorage.getItem('sdf_token')) { window.location.href = '/'; }
 
    async function loadWelcome() {
        const me = await loadCurrentUser();
        if (!me) return;
        if (window.CURRENT_ROLE !== 'administrator') {
            window.location.replace((window.CURRENT_ROLE === 'lecturer' ? '/dashboard/lecturer' : '/dashboard/student') + window.location.search);
            return;
        }
        document.getElementById('welcome').textContent = `Welcome, ${me.full_name}`;
    }
 
    async function loadSystemStats() {
        const stats = await api('/statistics/system');
        if (!stats) return;
 
        const cards = [
            ['Total users', stats.total_users],
            ['Total groups', stats.total_groups],
            ['Total topics', stats.total_topics],
            ['Total posts', stats.total_posts],
            ['Total replies', stats.total_replies],
            ['Active (7 days)', stats.active_users_last_7_days],
            ['Currently blacklisted', stats.currently_blacklisted_users],
        ];
 
        document.getElementById('systemStats').innerHTML = cards.map(([label, value]) => `
            <div class="stat-card">
                <div class="value">${value ?? 0}</div>
                <div class="label">${label}</div>
            </div>
        `).join('');
 
        const roleEntries = Object.entries(stats.users_by_role || {});
        document.getElementById('usersByRole').innerHTML = roleEntries.length ? `
            <strong>Users by role</strong>
            <div style="margin-top: 8px;">
                ${roleEntries.map(([role, count]) => `
                    <span class="badge role-${role.toLowerCase()}" style="margin-right: 8px;">${role}: ${count}</span>
                `).join('')}
            </div>
        ` : '';
    }
 
    async function loadGroups() {
        const data = await api('/groups');
        const groups = (data && (data.data || data)) || [];
 
        document.getElementById('groupsList').innerHTML = groups.map(g => `
            <div class="card">
                <strong><a href="/groups/${g.group_id}">${g.name}</a></strong>
                <div class="muted">${g.description ?? ''} · ${g.members_count ?? 0} members · ${g.topics_count ?? 0} topics</div>
                <div style="margin-top: 8px;">
                    <a class="btn btn-secondary" href="/groups/${g.group_id}/statistics" style="padding: 4px 10px; font-size: 13px;">Statistics</a>
                    <a class="btn btn-secondary" href="/groups/${g.group_id}/gradebook" style="padding: 4px 10px; font-size: 13px; margin-left: 6px;">Gradebook</a>
                </div>
            </div>
        `).join('') || '<div class="empty-state">No groups have been created yet.</div>';
    }
 
    async function loadWarnings() {
        const tbody = document.getElementById('warningsBody');
        const responseData = await api('/moderation/warnings');
        let warnings = [];
        if (Array.isArray(responseData)) warnings = responseData;
        else if (responseData && Array.isArray(responseData.data)) warnings = responseData.data;
 
        if (!warnings.length) {
            document.getElementById('warningsList').innerHTML = '<div class="empty-state">No inactivity warnings on record.</div>';
            return;
        }
 
        document.getElementById('warningsList').innerHTML = `
            <table id="warningsTable">
                <thead><tr><th>User</th><th>Group</th><th>Sequence</th><th>Issued</th><th>Status</th></tr></thead>
                <tbody id="warningsBody"></tbody>
            </table>
        `;
 
        document.getElementById('warningsBody').innerHTML = warnings.map(w => {
            const userName = w.user ? (w.user.full_name || `User #${w.user_id}`) : `Deleted user #${w.user_id ?? '?'}`;
            const groupName = w.group ? w.group.name : `Group #${w.group_id ?? '?'}`;
            const dateStr = w.issue_date ? new Date(w.issue_date).toLocaleString() : 'N/A';
            const sequence = w.sequence_number ?? 1;
            const status = w.resolved
                ? '<span style="color: var(--accent); font-weight:600;">Resolved</span>'
                : `<button class="btn resolve-warn-btn" style="padding:3px 8px; font-size:12px;" data-id="${w.warning_id}">Resolve</button>`;
 
            return `
                <tr id="warning_row_${w.warning_id}">
                    <td>${userName}</td>
                    <td>${groupName}</td>
                    <td>#${sequence}</td>
                    <td>${dateStr}</td>
                    <td>${status}</td>
                </tr>
            `;
        }).join('');
    }
 
    document.addEventListener('click', async (e) => {
        if (!e.target.classList.contains('resolve-warn-btn')) return;
        const warningId = e.target.getAttribute('data-id');
        e.target.disabled = true;
        e.target.textContent = 'Processing…';
        const response = await api(`/moderation/warnings/${warningId}/resolve`, { method: 'POST' });
        if (response) {
            e.target.parentElement.innerHTML = '<span style="color: var(--accent); font-weight:600;">Resolved</span>';
        } else {
            e.target.disabled = false;
            e.target.textContent = 'Resolve';
        }
    });
 
    async function init() {
        initDashSidebar(document, 'panel-overview');
        await loadWelcome();
        loadSystemStats();
        loadGroups();
        loadWarnings();
    }
 
    init();
</script>
@endsection
 
