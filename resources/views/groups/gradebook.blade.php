@extends('layouts.app')

@section('title', 'Gradebook')

@section('content')
<div class="eyebrow">Grading and Participation</div>
<h1 id="groupName">Loading gradebook…</h1>

<div class="card">
    <table id="gradebookTable">
        <thead>
            <tr>
                <th>Student</th>
                <th>Participation</th>
                <th>Quiz score</th>
                <th># quizzes taken</th>
                <th>Overall total</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
@endsection

@section('scripts')
<script>
const groupId = {{ $group }};

async function loadGradebook() {
    const data = await api(`/groups/${groupId}/gradebook`);
    if (!data || data.message) {
        document.getElementById('groupName').textContent = 'Gradebook';
        document.querySelector('#gradebookTable tbody').innerHTML =
            `<tr><td colspan="5" class="muted">${data?.message ?? 'Could not load the gradebook (are you the lecturer for this group?).'}</td></tr>`;
        return;
    }

    document.getElementById('groupName').textContent = `${data.group} — Gradebook`;

    const tbody = document.querySelector('#gradebookTable tbody');
    tbody.innerHTML = (data.rows || []).map(r => `
        <tr>
            <td>${r.full_name}</td>
            <td>${Number(r.participation_total).toFixed(2)}</td>
            <td>${Number(r.quiz_total).toFixed(2)}</td>
            <td>${r.quiz_attempts_count}</td>
            <td><strong>${Number(r.overall_total).toFixed(2)}</strong></td>
        </tr>
    `).join('') || '<tr><td colspan="5" class="muted">No members with grades yet.</td></tr>';
}

loadGradebook();
</script>
@endsection
