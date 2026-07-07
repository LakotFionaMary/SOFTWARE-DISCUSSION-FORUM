<?php $__env->startSection('title', 'Topic'); ?>

<?php $__env->startSection('content'); ?>
<div class="eyebrow" id="topicCategory">Topic</div>
<h1 id="topicTitle">Loading…</h1>
<a class="btn secondary" id="exportLink" href="#" target="_blank">Export to PDF</a>

<div class="card">
    <h3>Write a post</h3>
    <form id="postForm">
        <textarea id="postContent" rows="3" placeholder="Share your thoughts…" required></textarea>
        <input type="text" id="excludeIds" placeholder="Exclude user IDs (comma-separated, optional)">
        <button class="btn" type="submit">Post</button>
    </form>
</div>

<div id="posts"></div>

<!-- Profile popup modal -->
<div id="profileModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:999; align-items:center; justify-content:center;">
    <div class="card" style="max-width:360px; width:90%; text-align:center;">
        <img id="modalAvatar" src="" style="width:96px;height:96px;border-radius:50%;object-fit:cover;display:none;margin:0 auto 12px;border:1px solid var(--line);">
        <div id="modalAvatarFallback" style="width:96px;height:96px;border-radius:50%;background:var(--accent);color:#fff;display:none;align-items:center;justify-content:center;font-weight:700;font-size:28px;margin:0 auto 12px;"></div>
        <h3 id="modalName" style="margin-bottom:4px;"></h3>
        <div class="muted" id="modalRole" style="margin-bottom:12px;"></div>
        <p id="modalBio" style="text-align:left;"></p>
        <p id="modalPhone" style="text-align:left; display:none;"></p>
        <button class="btn secondary" onclick="closeProfileModal()" style="margin-top:12px;">Close</button>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
const topicId = <?php echo e($topic); ?>;

async function loadTopic() {
    const t = await api(`/topics/${topicId}`);
    document.getElementById('topicTitle').textContent = t.title;
    document.getElementById('topicCategory').textContent = t.category ?? 'General';
    document.getElementById('exportLink').href = `/api/topics/${topicId}/export`;
    renderPosts(t.posts || []);
}

function authorLink(author) {
    if (!author) return 'Unknown';
    return `<span class="author-link" style="cursor:pointer; text-decoration:underline;" onclick="viewProfile(${author.user_id})">${author.full_name}</span>`;
}

function renderPosts(posts) {
    document.getElementById('posts').innerHTML = posts.map(p => `
        <div class="card">
            <strong>${authorLink(p.author)}</strong>
            <span class="muted">${new Date(p.posted_at).toLocaleString()}</span>
            ${p.is_flagged ? '<span class="flag"> · flagged</span>' : ''}
            <p>${p.content}</p>
            <button class="btn secondary" onclick="shareToSocial(${p.post_id})">Forward</button>
            <button class="btn secondary" onclick="flagPost(${p.post_id})">Flag</button>
            <div style="margin-top:10px; padding-left:16px; border-left: 2px solid #d8d2c4;">
                ${(p.replies || []).map(r => `
                    <div style="margin-bottom:8px;">
                        <strong>${authorLink(r.author)}</strong>
                        <span class="muted">${new Date(r.replied_at).toLocaleString()}</span>
                        <div>${r.content}</div>
                    </div>
                `).join('')}
                <form onsubmit="return submitReply(event, ${p.post_id})">
                    <input type="text" placeholder="Reply…" required>
                    <button class="btn secondary" type="submit">Reply</button>
                </form>
            </div>
        </div>
    `).join('') || '<div class="muted">No posts yet in this topic.</div>';
}

async function submitReply(e, postId) {
    e.preventDefault();
    const input = e.target.querySelector('input');
    await api(`/posts/${postId}/replies`, { method: 'POST', body: { content: input.value } });
    loadTopic();
    return false;
}

async function shareToSocial(postId) {
    await api(`/posts/${postId}/share`, { method: 'POST', body: { platform: 'Clipboard' } });
    alert('Link copied and share logged.');
}

async function flagPost(postId) {
    await api(`/posts/${postId}/flag`, { method: 'POST' });
    loadTopic();
}

document.getElementById('postForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const excludeRaw = document.getElementById('excludeIds').value.trim();
    const exclude_user_ids = excludeRaw ? excludeRaw.split(',').map(s => parseInt(s.trim())) : [];

    await api(`/topics/${topicId}/posts`, {
        method: 'POST',
        body: { content: document.getElementById('postContent').value, exclude_user_ids },
    });
    e.target.reset();
    loadTopic();
});

/* ---------------- Profile popup ---------------- */
async function viewProfile(userId) {
    const profile = await api(`/users/${userId}/profile`);
    if (!profile) return;

    document.getElementById('modalName').textContent = profile.full_name;
    document.getElementById('modalRole').textContent = profile.role;
    document.getElementById('modalBio').textContent = profile.bio || 'No bio provided.';

    const phoneEl = document.getElementById('modalPhone');
    if (profile.phone_public && profile.phone) {
        phoneEl.textContent = '📞 ' + profile.phone;
        phoneEl.style.display = 'block';
    } else {
        phoneEl.style.display = 'none';
    }

    const img = document.getElementById('modalAvatar');
    const fallback = document.getElementById('modalAvatarFallback');
    if (profile.profile_picture) {
        img.src = '/storage/' + profile.profile_picture;
        img.style.display = 'block';
        fallback.style.display = 'none';
    } else {
        img.style.display = 'none';
        fallback.style.display = 'flex';
        fallback.textContent = (profile.full_name || '?').substring(0, 2).toUpperCase();
    }

    document.getElementById('profileModal').style.display = 'flex';
}

function closeProfileModal() {
    document.getElementById('profileModal').style.display = 'none';
}

loadTopic();
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /data/data/com.termux/files/home/forumG/resources/views/topics/show.blade.php ENDPATH**/ ?>