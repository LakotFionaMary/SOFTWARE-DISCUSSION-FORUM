@extends('layouts.app')

@section('title', 'Topic')

@section('content')
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
@endsection

@section('scripts')
<script>
const topicId = {{ $topic }};

async function loadTopic() {
    const t = await api(`/topics/${topicId}`);
    document.getElementById('topicTitle').textContent = t.title;
    document.getElementById('topicCategory').textContent = t.category ?? 'General';
    document.getElementById('exportLink').href = `/api/topics/${topicId}/export`;
    renderPosts(t.posts || []);
}

function renderPosts(posts) {
    document.getElementById('posts').innerHTML = posts.map(p => `
        <div class="card">
            <strong>${p.author.full_name}</strong>
            <span class="muted">${new Date(p.posted_at).toLocaleString()}</span>
            ${p.is_flagged ? '<span class="flag"> · flagged</span>' : ''}
            <p>${p.content}</p>
            <button class="btn secondary" onclick="shareToSocial(${p.post_id})">Forward</button>
            <button class="btn secondary" onclick="flagPost(${p.post_id})">Flag</button>
            <div style="margin-top:10px; padding-left:16px; border-left: 2px solid #d8d2c4;">
                ${(p.replies || []).map(r => `
                    <div style="margin-bottom:8px;">
                        <strong>${r.author.full_name}</strong>
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

loadTopic();
</script>
@endsection
