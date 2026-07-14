@extends('layouts.app')

@section('title', 'Manage Users')

@section('content')
<div class="eyebrow"><a href="/dashboard/admin" style="color: inherit;">← Administrator Dashboard</a></div>
<h1>Manage Users</h1>
<p class="muted">Grant or change the Lecturer/Administrator role for any user. Every new signup starts as a Student — this is the only way anyone becomes a Lecturer or Administrator.</p>

<div class="card">
    <input type="text" id="userSearch" placeholder="Search by name or email…" style="width: 100%; padding: 8px; margin-bottom: 12px;">
    <table>
        <thead>
            <tr><th>Name</th><th>Email</th><th>Current role(s)</th><th>Assign role</th></tr>
        </thead>
        <tbody id="usersBody">
            <tr><td colspan="4" class="muted" style="text-align:center; padding: 14px;">Loading users…</td></tr>
        </tbody>
    </table>
</div>
@endsection

@section('scripts')
<script>
    if (!localStorage.getItem('sdf_token')) { window.location.href = '/'; }

    let allUsers = [];

    async function guardAdmin() {
        const me = await loadCurrentUser();
        if (!me) return false;
        if (window.CURRENT_ROLE !== 'administrator') {
            window.location.replace(window.CURRENT_ROLE === 'lecturer' ? '/dashboard/lecturer' : '/dashboard/student');
            return false;
        }
        return true;
    }

    function renderUsers(users) {
        const tbody = document.getElementById('usersBody');
        tbody.innerHTML = users.map(u => {
            const roleNames = (u.roles || []).map(r => r.role_name);
            const badges = roleNames.map(r => `<span class="badge role-${r.toLowerCase()}" style="margin-right:4px;">${r}</span>`).join('') || '<span class="muted">None</span>';

            return `
                <tr>
                    <td>${u.full_name}</td>
                    <td>${u.email}</td>
                    <td>${badges}</td>
                    <td>
                        <select class="roleSelect" data-user-id="${u.user_id}" style="padding: 5px;">
                            <option value="Student" ${roleNames.includes('Student') ? 'selected' : ''}>Student</option>
                            <option value="Lecturer" ${roleNames.includes('Lecturer') ? 'selected' : ''}>Lecturer</option>
                            <option value="Administrator" ${roleNames.includes('Administrator') ? 'selected' : ''}>Administrator</option>
                        </select>
                        <button class="btn assign-role-btn" data-user-id="${u.user_id}" style="padding: 5px 10px; font-size: 13px; margin-left: 6px;">Assign</button>
                    </td>
                </tr>
            `;
        }).join('') || '<tr><td colspan="4" class="muted" style="text-align:center; padding:14px;">No users found.</td></tr>';

        tbody.querySelectorAll('.assign-role-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const userId = btn.dataset.userId;
                const select = tbody.querySelector(`.roleSelect[data-user-id="${userId}"]`);
                const role = select.value;

                btn.disabled = true;
                btn.textContent = 'Saving…';
                const res = await api(`/users/${userId}/role`, { method: 'PATCH', body: { role } });
                btn.disabled = false;
                btn.textContent = 'Assign';

                if (res) {
                    alert(`Role updated to ${role}.`);
                    loadUsers();
                } else {
                    alert('Failed to update role.');
                }
            });
        });
    }

    async function loadUsers() {
        const data = await api('/users');
        allUsers = (data && (data.data || data)) || [];
        renderUsers(allUsers);
    }

    document.getElementById('userSearch').addEventListener('input', (e) => {
        const term = e.target.value.toLowerCase();
        renderUsers(allUsers.filter(u =>
            u.full_name.toLowerCase().includes(term) || u.email.toLowerCase().includes(term)
        ));
    });

    (async () => {
        if (await guardAdmin()) {
            loadUsers();
        }
    })();
</script>
@endsection
