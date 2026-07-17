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
        <!-- MODIFIED: inactivity warnings and flagged content (posts/replies
             flagged by lecturers or student group admins) are now merged
             into a single list here, sorted most recent first, instead of
             living in two separately-headed tables. -->
        <div class="dash-panel" id="panel-warnings">
            <div class="section-title"><h2 style="margin:0;">Inactivity Warnings and Flags</h2></div>
            <p class="muted">Inactivity warnings and content flagged by lecturers or student group admins, most recent first.</p>
            <div id="warningsList" class="card muted">Loading…</div>
        </div>

        <!-- ================= BLACKLISTED USERS ================= -->
        <div class="dash-panel" id="panel-blacklists">
            <div class="section-title"><h2 style="margin:0;">Blacklisted Users</h2></div>
            <p class="muted">Currently-active suspensions. "Whole account" bans (issued for prolonged inactivity) block login entirely; group bans only block that one group. Lift ends a suspension immediately.</p>
            <div id="blacklistsList" class="card muted">Loading…</div>
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
            window.location.replace(window.CURRENT_ROLE === 'lecturer' ? '/dashboard/lecturer' : '/dashboard/student');
            return;
        }
        document.getElementById('welcome').textContent = `Welcome, ${me.full_name}`;
    }

    async function loadSystemStats() {
        // Matches GET /statistics/system (role:Administrator) in routes/api.php
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
        // Matches GET /groups in routes/api.php
        const data = await api('/groups');
        const groups = (data && (data.data || data)) || [];

        // Group name is plain text (no link into the general group/posts
        // page, which is where messaging lives) — Statistics and Gradebook
        // stay as admin-facing actions.
        document.getElementById('groupsList').innerHTML = groups.map(g => `
            <div class="card">
                <strong>${g.name}</strong>
                <div class="muted">${g.description ?? ''} · ${g.members_count ?? 0} members · ${g.topics_count ?? 0} topics</div>
                <div style="margin-top: 8px;">
                    <a class="btn btn-secondary" href="/groups/${g.group_id}/statistics" style="padding: 4px 10px; font-size: 13px;">Statistics</a>
                    <a class="btn btn-secondary" href="/groups/${g.group_id}/gradebook" style="padding: 4px 10px; font-size: 13px; margin-left: 6px;">Gradebook</a>
                </div>
            </div>
        `).join('') || '<div class="empty-state">No groups have been created yet.</div>';
    }

    // MODIFIED: loadWarnings() and loadFlaggedContent() have been merged
    // into loadWarningsAndFlags(), which fetches both inactivity warnings
    // and flagged-content notifications, normalizes them into a common row
    // shape, sorts the combined list by date (most recent first), and
    // renders them into ONE table under the "Inactivity Warnings" heading —
    // so flagged posts/replies from both the lecturer dashboard and the
    // student group-admin dashboard show up right alongside inactivity
    // warnings instead of in their own separate section.
    //
    // Flagged content still comes from GET /notifications (5.10
    // Notification Module) filtered down to flag-related entries, since
    // there's no dedicated "list flagged content" route in routes/api.php.
    // PostController::flag() and ReplyController::flag() both notify every
    // Administrator ('Post Flagged' / 'Reply Flagged') regardless of
    // whether the flag came from a lecturer or a student group admin, which
    // is what this filter matches on.
    async function loadWarningsAndFlags() {
        // Independent try/catches: if one endpoint fails, the other still
        // renders instead of the whole panel silently going blank (a
        // regression from merging these into one function — the original
        // two-function version ran them independently).
        let warnings = [];
        try {
            // Matches GET /moderation/warnings in routes/api.php
            const warningsResp = await api('/moderation/warnings');
            if (Array.isArray(warningsResp)) warnings = warningsResp;
            else if (warningsResp && Array.isArray(warningsResp.data)) warnings = warningsResp.data;
        } catch (err) {
            console.error('Failed to load inactivity warnings:', err);
        }

        let notifications = [];
        try {
            const notifResp = await api('/notifications');
            notifications = (notifResp && (notifResp.data || notifResp)) || [];
        } catch (err) {
            console.error('Failed to load notifications:', err);
        }

        // FIXED: `is_read` was compared with a plain `!n.is_read`, which
        // assumes it always arrives as a real JS boolean. If the backend
        // Notification model doesn't cast that column to boolean, a tinyint
        // read/write can serialize as the STRING "0" — and `!"0"` is
        // `false` in JS (a non-empty string is truthy), so every
        // notification silently looked "already read" and got filtered
        // out, no matter how fresh it was. This normalizes true/1/"1" as
        // read and everything else as unread, regardless of which form the
        // API sends.
        const isRead = (v) => v === true || v === 1 || v === '1';
        const flagged = notifications.filter(n =>
            !isRead(n.is_read) && ((n.type || '').toLowerCase().includes('flag') || (n.message || '').toLowerCase().includes('flag'))
        );

        const warningRows = warnings.map(w => {
            const userName = w.user ? (w.user.full_name || `User #${w.user_id}`) : `Deleted user #${w.user_id ?? '?'}`;
            const groupName = w.group ? w.group.name : `Group #${w.group_id ?? '?'}`;
            const dateStr = w.issue_date ? new Date(w.issue_date).toLocaleString() : 'N/A';
            const sequence = w.sequence_number ?? 1;
            const status = w.resolved
                ? '<span style="color: var(--accent); font-weight:600;">Resolved</span>'
                : `<button class="btn resolve-warn-btn" style="padding:3px 8px; font-size:12px;" data-id="${w.warning_id}">Resolve</button>`;

            return {
                sortValue: w.issue_date ? new Date(w.issue_date).getTime() : 0,
                html: `
                    <tr id="warning_row_${w.warning_id}">
                        <td><span class="badge">Inactivity</span></td>
                        <td>${userName} · ${groupName} · #${sequence}</td>
                        <td>${dateStr}</td>
                        <td>${status}</td>
                    </tr>
                `
            };
        });

        const flagRows = flagged.map(n => {
            const id = n.notification_id ?? n.id;
            const dateStr = n.created_at ? new Date(n.created_at).toLocaleString() : 'N/A';
            // FIXED: type is now always 'General' (see PostController::flag()
            // / ReplyController::flag() — the notifications.type column is a
            // strict ENUM that doesn't include 'Post Flagged'/'Reply Flagged',
            // so those values were changed to reuse 'General'). The Post vs
            // Reply distinction lives in related_type instead, which is a
            // plain unconstrained string column.
            const kind = n.related_type || 'Content';

            return {
                sortValue: n.created_at ? new Date(n.created_at).getTime() : 0,
                html: `
                    <tr id="flag_notif_${id}">
                        <td><span class="badge" style="background:#dc2626;">${kind} flagged</span></td>
                        <td>${n.message}</td>
                        <td>${dateStr}</td>
                        <td><button class="btn dismiss-flag-btn" style="padding:3px 8px; font-size:12px;" data-id="${id}">Dismiss</button></td>
                    </tr>
                `
            };
        });

        const rows = [...warningRows, ...flagRows].sort((a, b) => b.sortValue - a.sortValue);

        if (!rows.length) {
            document.getElementById('warningsList').innerHTML = '<div class="empty-state">No inactivity warnings or flagged content right now.</div>';
            return;
        }

        document.getElementById('warningsList').innerHTML = `
            <table id="warningsTable">
                <thead><tr><th>Type</th><th>Details</th><th>Date</th><th>Status</th></tr></thead>
                <tbody id="warningsBody">${rows.map(r => r.html).join('')}</tbody>
            </table>
        `;
    }

    // Matches GET /moderation/blacklists in routes/api.php
    async function loadBlacklists() {
        let blacklists = [];
        try {
            const resp = await api('/moderation/blacklists');
            if (Array.isArray(resp)) blacklists = resp;
            else if (resp && Array.isArray(resp.data)) blacklists = resp.data;
        } catch (err) {
            console.error('Failed to load blacklists:', err);
        }

        if (!blacklists.length) {
            document.getElementById('blacklistsList').innerHTML = '<div class="empty-state">No one is currently blacklisted.</div>';
            return;
        }

        const scopeLabel = (reason) => reason === 'inactivity'
            ? '<span class="badge" style="background:#dc2626;">Whole account</span>'
            : '<span class="badge">This group only</span>';

        const rows = blacklists.map(b => {
            const userName = b.user ? (b.user.full_name || `User #${b.user_id}`) : `Deleted user #${b.user_id ?? '?'}`;
            const groupName = b.group ? b.group.name : `Group #${b.group_id ?? '?'}`;
            const endStr = b.end_date ? new Date(b.end_date).toLocaleString() : 'N/A';

            return `
                <tr id="blacklist_row_${b.blacklist_id}">
                    <td>${scopeLabel(b.reason)}</td>
                    <td>${userName} · ${groupName}</td>
                    <td>${endStr}</td>
                    <td><button class="btn lift-blacklist-btn" style="padding:3px 8px; font-size:12px;" data-id="${b.blacklist_id}">Lift</button></td>
                </tr>
            `;
        }).join('');

        document.getElementById('blacklistsList').innerHTML = `
            <table id="blacklistsTable">
                <thead><tr><th>Scope</th><th>Member</th><th>Ends</th><th></th></tr></thead>
                <tbody id="blacklistsBody">${rows}</tbody>
            </table>
        `;
    }

    // Single delegated handler covers both the "Resolve" (inactivity
    // warning) and "Dismiss" (flagged content) buttons, since both kinds of
    // rows now live in the same #warningsBody table.
    document.addEventListener('click', async (e) => {
        if (e.target.classList.contains('lift-blacklist-btn')) {
            const id = e.target.getAttribute('data-id');
            e.target.disabled = true;
            e.target.textContent = 'Lifting…';
            // Matches POST /moderation/blacklists/{blacklist}/lift in routes/api.php
            const response = await api(`/moderation/blacklists/${id}/lift`, { method: 'POST' });
            if (response) {
                const row = document.getElementById(`blacklist_row_${id}`);
                if (row) row.remove();
                const body = document.getElementById('blacklistsBody');
                if (body && !body.querySelectorAll('tr').length) {
                    document.getElementById('blacklistsList').innerHTML = '<div class="empty-state">No one is currently blacklisted.</div>';
                }
            } else {
                e.target.disabled = false;
                e.target.textContent = 'Lift';
            }
            return;
        }


        if (e.target.classList.contains('resolve-warn-btn')) {
            const warningId = e.target.getAttribute('data-id');
            e.target.disabled = true;
            e.target.textContent = 'Processing…';
            // Matches POST /moderation/warnings/{warning}/resolve in routes/api.php
            const response = await api(`/moderation/warnings/${warningId}/resolve`, { method: 'POST' });
            if (response) {
                e.target.parentElement.innerHTML = '<span style="color: var(--accent); font-weight:600;">Resolved</span>';
            } else {
                e.target.disabled = false;
                e.target.textContent = 'Resolve';
            }
            return;
        }

        if (e.target.classList.contains('dismiss-flag-btn')) {
            const id = e.target.getAttribute('data-id');
            e.target.disabled = true;
            e.target.textContent = 'Processing…';
            // Matches PATCH /notifications/{notification}/read in routes/api.php
            const response = await api(`/notifications/${id}/read`, { method: 'PATCH' });
            if (response) {
                const row = document.getElementById(`flag_notif_${id}`);
                if (row) row.remove();
                const body = document.getElementById('warningsBody');
                if (body && !body.querySelectorAll('tr').length) {
                    document.getElementById('warningsList').innerHTML = '<div class="empty-state">No inactivity warnings or flagged content right now.</div>';
                }
            } else {
                e.target.disabled = false;
                e.target.textContent = 'Dismiss';
            }
            return;
        }
    });

    async function init() {
        initDashSidebar();
        await loadWelcome();
        loadSystemStats();
        loadGroups();
        loadWarningsAndFlags();
        loadBlacklists();
    }

    init();
</script>
@endsection
 
