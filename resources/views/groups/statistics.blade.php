@extends('layouts.app')

@section('title', 'Group Statistics')

@section('content')
<div class="eyebrow">Statistics Module</div>
<h1 id="groupName">Group analytics</h1>

<div class="card" id="metrics">Loading…</div>

<h2>Struggling students (idle 7+ days)</h2>
<div class="card">
    <table id="strugglingTable">
        <thead><tr><th>Name</th><th>Last active</th></tr></thead>
        <tbody></tbody>
    </table>
</div>
@endsection

@section('scripts')
<script>
const groupId = {{ $group }};

async function loadStats() {
    const stats = await api(`/groups/${groupId}/statistics`);

    if (!stats || stats.message) {
        document.getElementById('groupName').textContent = 'Access denied';
        document.getElementById('metrics').innerHTML = `<p class="muted">${stats?.message ?? 'You do not have access to this group\'s statistics.'}</p>`;
        document.querySelector('#strugglingTable').style.display = 'none';
        return;
    }

    document.getElementById('groupName').textContent = `${stats.group} — analytics`;
    document.getElementById('metrics').innerHTML = `
        <table>
            <tr><td>Total posts</td><td>${stats.total_posts}</td></tr>
            <tr><td>Active contributors (7 days)</td><td>${stats.active_contributors}</td></tr>
            <tr><td>Currently banned</td><td>${stats.banned_individuals}</td></tr>
            <tr><td>Unanswered topics</td><td>${stats.unanswered_topics}</td></tr>
        </table>
    `;
    const tbody = document.querySelector('#strugglingTable tbody');
    tbody.innerHTML = (stats.struggling_students || []).map(s => `
        <tr><td>${s.full_name}</td><td>${s.last_active_at ? new Date(s.last_active_at).toLocaleDateString() : 'Never active'}</td></tr>
    `).join('') || '<tr><td colspan="2" class="muted">No struggling students right now.</td></tr>';
}

loadStats();
</script>
@endsection
