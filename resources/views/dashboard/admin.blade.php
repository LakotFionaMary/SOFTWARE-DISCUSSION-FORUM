@extends('layouts.app')

@section('title', 'Administrator Dashboard')

@section('content')
<div class="dash-shell">
   

    <div class="dash-main">
        <!-- ================= SYSTEM OVERVIEW ================= -->
        <div class="dash-panel" id="panel-overview">
            <div class="section-title" style="display: flex; justify-content: space-between; align-items: center;">
                <h2 style="margin:0;">System Overview</h2>
                <button class="btn secondary" id="refreshStatsBtn" onclick="loadSystemStats()" style="padding: 4px 10px; font-size: 13px;">Refresh</button>
            </div>
            <div id="systemStats" class="stat-grid">
                <div class="stat-card"><div class="value">…</div><div class="label">Loading</div></div>
            </div>
            <div id="usersByRole" class="card muted" style="margin-top: 4px;"></div>
        </div>

        <!-- ================= GROUPS ================= -->
        <div class="dash-panel" id="panel-groups">
            <div class="section-title" style="display: flex; justify-content: space-between; align-items: center;">
                <h2 style="margin:0;">Groups</h2>
                <!-- Step 6 Added: Action button to download the currently viewed group metrics copy -->
                <button class="btn" id="exportReportBtn" onclick="exportAnalyticsReport()" style="display: none;">Download and Export Report</button>
            </div>
            <p class="muted">As an administrator you can view statistics and the gradebook for every group on the platform.</p>
            
            <!-- Step 5 Added: Inline placeholder canvas area to render graphic charts safely under groups -->
            <div id="groupStatsVisualization" class="card" style="margin-bottom: 16px; padding: 16px; display: none;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <strong id="visualChartTitle">Group Statistics Visualization</strong>
                    <button class="btn secondary" style="padding: 2px 8px; font-size: 11px;" onclick="closeGroupStatsView()">Hide Charts</button>
                </div>
                <!-- MODIFIED CONTAINER STYLING: Added position relative and explicit height to force Chart.js to paint correctly -->
                <div style="position: relative; height: 250px; width: 100%;">
                    <canvas id="adminMetricsChart"></canvas>
                </div>
            </div>

            <div id="groupsList" class="muted">Loading groups…</div>
        </div>

        <!-- ================= INACTIVITY WARNINGS ================= -->
        <!-- MODIFIED: inactivity warnings and flagged content (posts/replies
             flagged by lecturers or student group admins) are now merged
             into a single list here, sorted most recent first, instead of
             living in two separately-headed tables. -->
        <div class="dash-panel" id="panel-warnings">
            <div class="section-title"><h2 style="margin:0;">Inactivity Warnings and Flags</h2></div>
            
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
<!-- FIXED: chart.js used to be loaded here from https://cdn.jsdelivr.net/npm/chart.js.
     That's an external network dependency - if it's blocked or unreachable
     (firewall, offline dev environment, ad-blocker, etc.) this script tag
     fails silently and every `new Chart(...)` call below throws "Chart is
     not defined". It's now bundled through Vite instead (see
     resources/js/app.js, which imports chart.js and exposes it as
     window.Chart) - no separate request, no external dependency to go down. -->
<script>
    if (!localStorage.getItem('sdf_token')) { window.location.href = '/'; }

    // Step 5 & 6 dynamic additions: State trackers tracking active visualization matrices
    let activeChartInstance = null;
    let currentSelectedGroupData = null;

    async function loadWelcome() {
        const me = await loadCurrentUser();
        if (!me) return;
        if (window.CURRENT_ROLE !== 'administrator') {
            window.location.replace(window.CURRENT_ROLE === 'lecturer' ? '/dashboard/lecturer' : '/dashboard/student');
            return;
        }
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
                    <!-- Added Option: Dedicated Visual Charting option positioned directly alongside existing triggers -->
                    <button class="btn btn-secondary" onclick="viewInlineGroupStats(${g.group_id}, '${g.name.replace(/'/g, "\\'")}')" style="padding: 4px 10px; font-size: 13px; margin-left: 6px; background: var(--accent); color: white;">Visual Charts</button>
                </div>
            </div>
        `).join('') || '<div class="empty-state">No groups have been created yet.</div>';
    }

    // Step 5 Added Hook: Captures data streams dynamically and plots graphical models matching Step 3 metrics 
    async function viewInlineGroupStats(groupId, groupName) {
        const stats = await api(`/groups/${groupId}/statistics`) || await api(`/statistics/system`); 
        if (!stats) return;

        currentSelectedGroupData = { group_id: groupId, group_name: groupName, ...stats };

        document.getElementById('visualChartTitle').textContent = `${groupName} — Metric Analysis Charts`;
        document.getElementById('groupStatsVisualization').style.display = 'block';
        document.getElementById('exportReportBtn').style.display = 'inline-block';
        
        // MODIFIED: Hide the groups container list when visual view targets are active
        document.getElementById('groupsList').style.display = 'none';

        const ctx = document.getElementById('adminMetricsChart').getContext('2d');
        if (activeChartInstance) { activeChartInstance.destroy(); }

        activeChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Members Count', 'Topics', 'Published Posts', 'Internal Replies', 'Active Users'],
                datasets: [{
                    label: `Live Aggregation Stream for ${groupName}`,
                    data: [
                        stats.members_count ?? stats.total_users ?? 0,
                        stats.topics_count ?? stats.total_topics ?? 0,
                        stats.posts_count ?? stats.total_posts ?? 0,
                        stats.replies_count ?? stats.total_replies ?? 0,
                        stats.active_contributors ?? stats.active_users_last_7_days ?? 0
                    ],
                    backgroundColor: [
                        'rgb(54, 126, 235)',
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(255, 206, 86, 0.6)',
                        
                        'rgba(255, 64, 223, 0.6)',
                        'rgba(153, 102, 255, 0.6)'
                    ],
                    borderColor: [
                        'rgb(54, 126, 235)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 206, 86, 1)',
                        
                        'rgba(255, 64, 223, 0.6)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });

        document.getElementById('panel-groups').scrollIntoView({ behavior: 'smooth' });
    }

    // Step 5 UI Visibility Management Addition
    function closeGroupStatsView() {
        document.getElementById('groupStatsVisualization').style.display = 'none';
        document.getElementById('exportReportBtn').style.display = 'none';
        
        // MODIFIED: Make the main groups card directory visible again when closing chart layouts
        document.getElementById('groupsList').style.display = 'block';

        if (activeChartInstance) {
            activeChartInstance.destroy();
            activeChartInstance = null;
        }
    }

    // Step 6 Added Hook: Performs a seamless local raw compilation file save 
    function exportAnalyticsReport() {
        if (!currentSelectedGroupData) {
            alert("No data snapshot active to download at this time.");
            return;
        }

        const dataLayout = {
            report_profile: "Physical Document Analytics Summary Report",
            saved_at: new Date().toLocaleString(),
            payload: currentSelectedGroupData
        };

        const convertedData = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(dataLayout, null, 4));
        const dummyNode = document.createElement('a');
        dummyNode.setAttribute("href", convertedData);
        dummyNode.setAttribute("download", `group_${currentSelectedGroupData.group_id}_analytics_report.json`);
        document.body.appendChild(dummyNode);
        dummyNode.click();
        dummyNode.remove();
    }

  
    async function loadWarningsAndFlags() {
       
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

       
        setInterval(() => {
            loadSystemStats();
        }, 20000);
    }

    init();
</script>
@endsection
 
