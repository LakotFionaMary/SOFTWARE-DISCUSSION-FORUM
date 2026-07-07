<?php $__env->startSection('title', 'My Profile'); ?>

<?php $__env->startSection('content'); ?>
<div class="eyebrow">My Account</div>
<h1>Profile</h1>

<div class="card" id="profileCard">
    <div style="display:flex;align-items:center;gap:16px;margin-bottom:16px;">
        <img id="avatarPreview" src="" style="width:64px;height:64px;border-radius:50%;object-fit:cover;display:none;border:1px solid var(--line);">
        <div id="avatarFallback" style="width:64px;height:64px;border-radius:50%;background:var(--accent);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:20px;"></div>
        <div>
            <div id="profileName" style="font-weight:700;"></div>
            <div id="profileRole" class="muted"></div>
        </div>
    </div>

    <label>Profile picture</label>
    <input type="file" id="pictureInput" accept="image/png,image/jpeg">
    <div id="pictureMsg" class="error" style="display:none;"></div>

    <form id="profileForm" style="margin-top:16px;">
        <label>Full name</label>
        <input type="text" id="fullName" required>

        <label>Bio</label>
        <textarea id="bio" rows="3" placeholder="Tell others a bit about yourself…"></textarea>

        <label>Phone number</label>
        <input type="text" id="phone" placeholder="e.g. 0700 000 000">

        <label style="display:flex; align-items:center; gap:8px; font-size:14px;">
            <input type="checkbox" id="phonePublic" style="width:auto; margin:0;">
            Make my phone number visible to others
        </label>

        <div id="departmentField" style="display:none;">
            <label>Department</label>
            <input type="text" id="department" placeholder="e.g. Computer Science">
        </div>

        <div id="formMsg" class="error" style="display:none;"></div>
        <button class="btn" type="submit">Save changes</button>
        <a class="btn secondary" href="<?php echo e(route('dashboard')); ?>">Cancel</a>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    let me = null;

    function renderProfile() {
        document.getElementById('profileName').textContent = me.full_name;
        const role = (me.roles && me.roles.length) ? me.roles[0].role_name : 'Student';
        document.getElementById('profileRole').textContent = role;

        document.getElementById('fullName').value = me.full_name || '';
        document.getElementById('bio').value = me.bio || '';
        document.getElementById('phone').value = me.phone || '';
        document.getElementById('phonePublic').checked = !!me.phone_public;
        document.getElementById('department').value = me.department || '';

        if (role === 'Lecturer') {
            document.getElementById('departmentField').style.display = 'block';
        }

        if (me.profile_picture) {
            const img = document.getElementById('avatarPreview');
            img.src = '/storage/' + me.profile_picture;
            img.style.display = 'block';
            document.getElementById('avatarFallback').style.display = 'none';
        } else {
            document.getElementById('avatarFallback').textContent =
                (me.full_name || '?').substring(0, 2).toUpperCase();
        }
    }

    async function loadProfile() {
        me = await api('/me');
        if (me) renderProfile();
    }

    document.getElementById('profileForm').addEventListener('submit', async (e) => {
        e.preventDefault();
      
        const msg = document.getElementById('formMsg');
        msg.style.display = 'none';

        const res = await api('/me', {
            method: 'PATCH',
            body: {
                full_name: document.getElementById('fullName').value,
                bio: document.getElementById('bio').value,
                phone: document.getElementById('phone').value,
                phone_public: document.getElementById('phonePublic').checked,
                department: document.getElementById('department').value,
            },
        });

    

        if (res && res.user) {
            me = res.user;
            renderProfile();
            msg.style.color = 'var(--accent)';
            msg.textContent = 'Saved!';
            msg.style.display = 'block';
        } else {
            msg.style.color = 'var(--warn)';
            msg.textContent = 'Could not save changes.';
            msg.style.display = 'block';
        }
    });

    document.getElementById('pictureInput').addEventListener('change', async (e) => {
        const file = e.target.files[0];
        if (!file) return;
        const msgEl = document.getElementById('pictureMsg');
        msgEl.style.display = 'none';

        const formData = new FormData();
        formData.append('profile_picture', file);

        const token = localStorage.getItem('sdf_token');
        try {
            const response = await fetch('/api/me/profile-picture', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`,
                },
                body: formData,
            });
            const data = await response.json();
            if (response.ok) {
                document.getElementById('avatarPreview').src = data.profile_picture_url;
                document.getElementById('avatarPreview').style.display = 'block';
                document.getElementById('avatarFallback').style.display = 'none';
            } else {
                msgEl.textContent = data.message || 'Upload failed.';
                msgEl.style.display = 'block';
            }
        } catch (err) {
            msgEl.textContent = 'Upload failed.';
            msgEl.style.display = 'block';
        }
    });

    loadProfile();
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /data/data/com.termux/files/home/forumG/resources/views/profile/edit.blade.php ENDPATH**/ ?>