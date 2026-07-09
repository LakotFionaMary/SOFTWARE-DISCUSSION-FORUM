@extends('layouts.app')

@section('title', 'Admin Overview Panel')

@section('content')
<div class="eyebrow" style="color: #2f6f5e;">Administration Hub</div>
<h1> Admin Dashboard</h1>
<p class="muted">This view is restricted to administrators and lecturers.</p>

<!-- COURSE SELECTION CARDS MATRIX -->
<div class="card" style="border-left: 4px solid #e11d48; margin-top: 20px;">
    <h3> Course Analytics Matrix</h3>
    <p class="muted">Select a course group below to view its real-time data, student participation, and active ban registers.</p>
    
    <!-- Active injection target for groups -->
    <div id="adminGroupList" style="margin-top: 15px; display: grid; gap: 12px;">
        <div class="muted">Loading system course groups...</div>
    </div>
</div>

<!-- WARNINGS AND INACTIVITY MONITORING LOG REGISTER -->
<div class="card" style="border-left: 4px solid #e11d48; margin-top: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h3 style="color: black; margin: 0;"> Automated Inactivity Warning Register</h3>
        <span class="eyebrow" style="font-size: 11px; color: black;">Status Monitor</span>
    </div>
    <p class="muted" style="margin-bottom: 16px;">Two active unresolved warnings automatically renders user into the account Blacklist register.</p>
    
    <table id="warningsTable">
        <thead>
            <tr>
                <th>Target User</th>
                <th>Course Group</th>
                <th>Warning Sequence</th>
                <th>Issue Timestamp</th>
                <th>Status Resolution / Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr><td colspan="5" class="muted" style="text-align: center; padding: 10px;">Querying warning data...</td></tr>
        </tbody>
    </table>
</div>

<!-- QUICK ACTIONS -->
<div class="card" style="margin-top: 16px;">
    <h3> Quick Action</h3>
    <div style="display: flex; gap: 10px; margin-top: 10px;">
        <button class="btn btn-secondary" onclick="window.location.href='/dashboard'">Return to Normal Dashboard</button>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
    if (typeof api !== 'function') {
        console.error("Global API framework utility missing.");
        document.getElementById('adminGroupList').textContent = "System linkage failure.";
        return;
    }

    // 1. POPULATE GROUP OPERATIONS LIST
    async function loadAdminGroups() {
        const listContainer = document.getElementById('adminGroupList');
        const data = await api('/groups');
        const groups = data?.data || data || [];

        if (groups.length === 0) {
            listContainer.innerHTML = '<div class="muted">No course groups have been created yet on the forum platform.</div>';
            return;
        }

        listContainer.innerHTML = groups.map(g => `
            <div class="card" style="margin-bottom: 0; padding: 14px 18px; display: flex; justify-content: space-between; align-items: center; border: 1px solid var(--line);">
                <div>
                    <strong style="font-size: 15px; color: var(--ink);">${g.name}</strong>
                    <div class="muted" style="font-size: 12px; margin-top: 2px;">
                        ID: ${g.group_id} · Members Enrolled: ${g.members_count ?? 0}
                    </div>
                </div>
                <div>
                    <a class="btn" href="/admin/statistics/${g.group_id}" style="background-color: #e11d48; padding: 6px 14px; font-size: 13px;">View Analytics</a>
                </div>
            </div>
        `).join('');
    }

    // 2. POPULATE DATABASE WARNINGS HISTORY REGISTER
    async function loadWarningRegister() {
        const tbody = document.querySelector('#warningsTable tbody');
       
        try {
            const responseData = await api('/moderation/warnings');
            let warnings = [];

            if (Array.isArray(responseData)) {
                warnings = responseData;
            } else if (responseData && Array.isArray(responseData.data)) {
                warnings = responseData.data;
            } else if (responseData && typeof responseData === 'object') {
                warnings = Object.values(responseData).filter(item => typeof item === 'object' && item !== null);
            }

            if (!warnings || warnings.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="muted" style="text-align: center; padding: 14px;">No inactivity warning flags currently recorded in database.</td></tr>';
                return;
            }

            tbody.innerHTML = warnings.map(w => {
                
                if (!w) return '';

                const userName = w.user ? (w.user.full_name || w.user.name || `User #${w.user_id}`) : `Deleted User #${w.user_id || 'Unknown'}`;
                const userEmail = w.user?.email ? `<br><small class="muted">${w.user.email}</small>` : '';
                
               
                const groupName = w.group ? (w.group.name || `Group #${w.group_id}`) : `Deleted Group #${w.group_id || 'Unknown'}`;
                
                const dateStr = w.issue_date ? new Date(w.issue_date).toLocaleString() : 'N/A';
                const sequenceNum = w.sequence_number ?? 1;
                const warningId = w.warning_id ?? 0;
                
                let actionMarkup = '';
                if (w.resolved) {
                    actionMarkup = '<span style="color: var(--accent); font-weight: 600;">✅ Resolved</span>';
                } else {
                    actionMarkup = `
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="flag" style="font-size: 13px;">⚠️ Active (#${sequenceNum})</span>
                            <button class="btn resolve-warn-btn" style="padding: 3px 8px; font-size: 12px; background-color: var(--accent);" data-id="${warningId}">Resolve</button>
                        </div>
                    `;
                }

                return `
                    <tr id="warning_row_${warningId}">
                        <td><strong>${userName}</strong>${userEmail}</td>
                        <td>${groupName}</td>
                        <td style="text-align: center; font-weight: bold;">Warning #${sequenceNum}</td>
                        <td>${dateStr}</td>
                        <td>${actionMarkup}</td>
                    </tr>
                `;
            }).join('');

        } catch (error) {
            console.error("Failed to parse warning register table data:", error);
            tbody.innerHTML = '<tr><td colspan="5" class="muted" style="text-align: center; padding: 14px; color: #e11d48;">Failed to populate warning data due to internal structural error.</td></tr>';
        }
    }

    // 3. ATTACH CLICK DELEGATION INTERCEPTOR FOR RESOLVING WARNINGS
    document.getElementById('warningsTable')?.addEventListener('click', async (e) => {
        if (e.target.classList.contains('resolve-warn-btn')) {
            const warningId = e.target.getAttribute('data-id');
            e.target.disabled = true;
            e.target.textContent = 'Processing...';

            const response = await api(`/moderation/warnings/${warningId}/resolve`, {
                method: 'POST'
            });

            if (response) {
                alert('Warning updated and resolved successfully!');
                const cell = e.target.parentElement;
                if (cell) {
                    cell.innerHTML = '<span style="color: var(--accent); font-weight: 600;">✅ Resolved</span>';
                }
            } else {
                alert('Failed to update tracking log record.');
                e.target.disabled = false;
                e.target.textContent = 'Resolve';
            }
        }
    });

    // Execute loaders simultaneously
    await Promise.all([loadAdminGroups(), loadWarningRegister()]);
});
</script>
@endsection
