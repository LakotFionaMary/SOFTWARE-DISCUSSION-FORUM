@extends('layouts.app')

@section('title', 'Topic')

@section('content')
<style>
    /*----for chat-------*/
    .topic-header {
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
        flex-wrap: wrap; padding-bottom: 12px; border-bottom: 1px solid var(--line); margin-bottom: 14px;
    }

    /* ---------- Chat thread (WhatsApp-style) ---------- */
    .chat-thread {
        display: flex; flex-direction: column; gap: 4px;
        background: var(--paper); border: 1px solid var(--line); border-radius: var(--radius);
        padding: 16px; min-height: 200px; max-height: 65vh; overflow-y: auto;
    }
    .msg-group { display: flex; flex-direction: column; margin: 8px 0; max-width: 78%; }
    .msg-group.mine { align-self: flex-end; align-items: flex-end; }
    .msg-group.theirs { align-self: flex-start; align-items: flex-start; }

    .bubble {
        position: relative; padding: 8px 12px 6px; border-radius: 12px;
        font-size: 14px; line-height: 1.4; word-wrap: break-word;
    }
    .msg-group.mine .bubble { background: #d9fdd3; border-bottom-right-radius: 3px; }
    .msg-group.theirs .bubble { background: #fff; border: 1px solid var(--line); border-bottom-left-radius: 3px; }
    .bubble.is-flagged { outline: 2px solid var(--warn); }

    .bubble-author { font-size: 12px; font-weight: 600; color: var(--accent); margin-bottom: 2px; }
    .msg-group.mine .bubble-author { display: none; }
    .bubble-content { white-space: pre-wrap; }
    .bubble-meta { font-size: 10.5px; color: var(--slate); margin-top: 3px; text-align: right; }
    .bubble-flag-tag { color: var(--warn); font-weight: 600; }

    .bubble-actions {
        display: flex; gap: 2px; margin-top: 3px; opacity: 0; transition: opacity .12s;
    }
    .msg-group:hover .bubble-actions, .bubble-actions.force-visible { opacity: 1; }
    .msg-group.mine .bubble-actions { justify-content: flex-end; }

    .icon-btn {
        display: inline-flex; align-items: center; justify-content: center;
        background: transparent; border: none; color: var(--slate);
        border-radius: 50%; width: 26px; height: 26px; cursor: pointer; padding: 0;
    }
    .icon-btn svg { width: 15px; height: 15px; flex-shrink: 0; }
    .icon-btn:hover { background: rgba(0,0,0,.06); color: var(--accent); }
    .icon-btn.flag-btn:hover { color: var(--warn); }
    .icon-btn.is-flagged { color: var(--warn); }

    .share-wrap { position: relative; display: inline-block; }
    .share-menu {
        display: none; position: absolute; bottom: calc(100% + 4px); z-index: 20;
        background: #fff; border: 1px solid var(--line); border-radius: var(--radius);
        box-shadow: 0 6px 18px rgba(28,43,51,.15); padding: 6px; min-width: 190px;
    }
    .msg-group.theirs .share-menu { left: 0; }
    .msg-group.mine .share-menu { right: 0; }
    .share-menu.open { display: block; }
    .share-menu button {
        display: flex; align-items: center; gap: 10px; width: 100%; text-align: left;
        background: none; border: none; padding: 8px 10px; border-radius: 4px; cursor: pointer;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; font-size: 13px; color: var(--ink);
    }
    .share-menu button:hover { background: var(--paper); }
    .share-menu svg { width: 16px; height: 16px; flex-shrink: 0; }

    /* Replies rendered as their own chat rows, indented slightly to show they belong to the post above */
    .reply-row { margin-left: 22px; margin-top: 2px; }
    .reply-row .bubble { font-size: 13.5px; }

    /* ---------- Composer (bottom bar, WhatsApp-style) ---------- */
    .composer {
        display: flex; align-items: flex-end; gap: 8px; margin-top: 14px;
        background: #fff; border: 1px solid var(--line); border-radius: 24px; padding: 8px 8px 8px 16px;
    }
    .composer textarea {
        flex: 1; border: none; resize: none; outline: none; font-size: 14px; padding: 6px 0;
        max-height: 120px; font-family: inherit;
    }
    .composer-send {
        background: var(--accent); color: #fff; border: none; border-radius: 50%;
        width: 38px; height: 38px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; cursor: pointer;
    }
    .composer-send svg { width: 18px; height: 18px; }
    .composer-extra { font-size: 12px; color: var(--slate); margin-top: 6px; }
    .composer-extra input { font-size: 12px; }

    .reply-composer { display: flex; align-items: center; gap: 6px; margin: 6px 0 4px 22px; }
    .reply-composer input {
        flex: 1; border: 1px solid var(--line); border-radius: 16px; padding: 6px 12px; font-size: 13px; outline: none;
    }
    .reply-composer button {
        background: var(--accent); color: #fff; border: none; border-radius: 50%;
        width: 28px; height: 28px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; cursor: pointer;
    }
    .reply-composer button svg { width: 14px; height: 14px; }
</style>

<div class="eyebrow" id="topicCategory">Topic</div>
<h1 id="topicTitle">Loading Topic...</h1>
<button class="btn secondary" onclick="downloadPDF()">Export to PDF</button>

<div class="card">
    <h3>Write a post</h3>
    <form id="postFormCard">
        <textarea id="postContentCard" rows="3" placeholder="Share your thoughts…" required></textarea>

        <label for="excludeGroup" class="muted" style="display:block; margin-top:8px;">Exclude members of group (optional)</label>
        <select id="excludeGroup" style="margin-bottom:6px;">
            <option value="">— Select a group —</option>
        </select>

        <label for="excludeUsers" class="muted" style="display:block;">Exclude specific users (optional)</label>
        <select id="excludeUsers" multiple style="min-height:100px; margin-bottom:10px;">
            <option disabled>Select a group above to load its members…</option>
        </select>

        <button class="btn" type="submit">Post</button>
    </form>
</div>

<div class="chat-thread" id="posts"></div>

<form class="composer" id="postFormComposer">
    <textarea id="postContentComposer" rows="1" placeholder="Type a message…" required
        oninput="this.style.height='auto'; this.style.height=(this.scrollHeight)+'px';"></textarea>
    <button class="composer-send" type="submit" title="Send">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
    </button>
</form>

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
@endsection

@section('scripts')
<script>
// Use standard Laravel blade echo if global window var isn't used elsewhere
const topicId = parseInt("{{ $topic }}") || null;

let currentUserRole = 'Student';
let currentUserId = null;

/* ---------------- Inline icon library ---------------- */
const ICONS = {
    flag: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V4s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="2"/></svg>',
    share: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.6" y1="10.6" x2="15.4" y2="6.4"/><line x1="8.6" y1="13.4" x2="15.4" y2="17.6"/></svg>',
    send: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>',
    whatsapp: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.5 14.4c-.3-.1-1.6-.8-1.9-.9-.3-.1-.4-.1-.6.1-.2.3-.7.9-.8 1-.2.2-.3.2-.5.1-.3-.1-1.2-.4-2.2-1.4-.8-.7-1.4-1.6-1.6-1.9-.2-.3 0-.5.1-.6.1-.1.3-.3.4-.5.1-.1.2-.3.2-.4.1-.2 0-.3 0-.5s-.6-1.5-.9-2c-.2-.5-.5-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.3.3-1 1-1 2.4s1 2.8 1.2 3c.1.2 2 3 4.8 4.3.7.3 1.2.5 1.6.6.7.2 1.3.2 1.8.1.5-.1 1.6-.7 1.9-1.3.2-.6.2-1.1.2-1.2-.1-.2-.3-.3-.6-.4z"/><path d="M12 2a10 10 0 0 0-8.6 15L2 22l5.2-1.4A10 10 0 1 0 12 2zm0 18.2c-1.6 0-3.2-.4-4.5-1.2l-.3-.2-3.1.8.8-3-.2-.3A8.2 8.2 0 1 1 20.2 12 8.2 8.2 0 0 1 12 20.2z"/></svg>',
    twitter: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.9 3H21l-6.6 7.5L22.3 21h-6.7l-4.6-6-5.2 6H3.8l7-8-7.4-9h6.9l4.2 5.5L18.9 3zm-1.2 16.2h1.9L7.4 4.7H5.4l12.3 14.5z"/></svg>',
    facebook: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M13.5 21v-8.1h2.7l.4-3.2h-3.1V7.6c0-.9.3-1.5 1.6-1.5h1.7V3.2C16.5 3.1 15.5 3 14.4 3c-2.4 0-4 1.4-4 4.1v2.6H7.7v3.2h2.7V21h3.1z"/></svg>',
    linkedin: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M6.9 8.6H3.6V20h3.3V8.6zM5.3 3.5a1.9 1.9 0 1 0 0 3.8 1.9 1.9 0 0 0 0-3.8zM20.4 20h-3.3v-6c0-1.4 0-3.3-2-3.3s-2.4 1.6-2.4 3.2V20H9.4V8.6h3.2v1.6h.1a3.5 3.5 0 0 1 3.1-1.7c3.4 0 4 2.2 4 5.1V20z"/></svg>',
    link: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.5.5l2-2a5 5 0 0 0-7-7l-1.2 1.1"/><path d="M14 11a5 5 0 0 0-7.5-.5l-2 2a5 5 0 0 0 7 7l1.1-1.1"/></svg>',
};

// Safe API wrapper check
async function executeApiCall(url, config = {}) {
    if (typeof api === 'function') {
        return await api(url, config);
    } else {
        console.error("The custom global wrapper function 'api()' was not found in your layout.");
        return null;
    }
}

function timeOnly(dt) {
    if(!dt) return "";
    return new Date(dt).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

async function loadCurrentUser() {
    const me = await executeApiCall('/me');
    if (me) {
        currentUserRole = (me.roles && me.roles.length > 0) ? me.roles[0].role_name : 'Student';
        currentUserId = me.user_id;
    }
}

async function loadTopic() {
    if(!topicId) return;
    const t = await executeApiCall(`/topics/${topicId}`);
    
    const titleEl = document.getElementById('topicTitle');
    const categoryEl = document.getElementById('topicCategory');
    const postsContainer = document.getElementById('posts');

    if (!t || t.message) {
        if(titleEl) titleEl.textContent = 'Unavailable';
        if(postsContainer) postsContainer.innerHTML = `<div class="muted">${(t && t.message) || 'This topic could not be loaded.'}</div>`;
        return;
    }

    if(titleEl) titleEl.textContent = t.title || "No Title";
    if(categoryEl) categoryEl.textContent = t.category ?? 'General';
    
    renderPosts(t.posts || []);
    
    if(postsContainer) {
        postsContainer.scrollTop = postsContainer.scrollHeight;
    }
}

async function loadGroupsForExclusion() {
    const select = document.getElementById('excludeGroup');
    if(!select) return;
    try {
        const res = await executeApiCall('/groups');
        if(!res) return;
        const groups = res.data || res;
        select.innerHTML = '<option value="">— Select a group —</option>' +
            groups.map(g => `<option value="${g.group_id}">${g.name}</option>`).join('');
    } catch (err) {
        console.error('Could not load groups', err);
    }
}

async function loadMembersForExclusion(groupId) {
    const select = document.getElementById('excludeUsers');
    if(!select) return;
    if (!groupId) {
        select.innerHTML = '<option disabled>Select a group above to load its members…</option>';
        return;
    }
    select.innerHTML = '<option disabled>Loading members…</option>';
    try {
        const res = await executeApiCall(`/groups/${groupId}/members`);
        if(!res) return;
        const members = res.data || res;
        select.innerHTML = members.length
            ? members.map(m => `<option value="${m.user_id}">${m.full_name || m.name}</option>`).join('')
            : '<option disabled>No members in this group.</option>';
    } catch (err) {
        console.error('Could not load group members', err);
        select.innerHTML = '<option disabled>Failed to load members.</option>';
    }
}

const excludeGroupEl = document.getElementById('excludeGroup');
if(excludeGroupEl) {
    excludeGroupEl.addEventListener('change', (e) => {
        loadMembersForExclusion(e.target.value);
    });
}

function actionsHtml(kind, id, isFlagged, canFlag) {
    const flagEndpoint = kind === 'post' ? `/posts/${id}/flag` : `/replies/${id}/flag`;
    return `
        <div class="bubble-actions">
            <div class="share-wrap">
                <button class="icon-btn" type="button" title="Forward to social media" onclick="toggleShareMenu('${kind}-${id}')">
                    ${ICONS.share}
                </button>
                <div class="share-menu" id="share-menu-${kind}-${id}">
                    <button type="button" onclick="shareToSocial(${id}, 'WhatsApp')">${ICONS.whatsapp} WhatsApp</button>
                    <button type="button" onclick="shareToSocial(${id}, 'Twitter')">${ICONS.twitter} X / Twitter</button>
                    <button type="button" onclick="shareToSocial(${id}, 'Facebook')">${ICONS.facebook} Facebook</button>
                    <button type="button" onclick="shareToSocial(${id}, 'LinkedIn')">${ICONS.linkedin} LinkedIn</button>
                    <button type="button" onclick="shareToSocial(${id}, 'Clipboard')">${ICONS.link} Copy link</button>
                </div>
            </div>
            ${canFlag ? `
                <button class="icon-btn flag-btn ${isFlagged ? 'is-flagged' : ''}" type="button"
                    title="Flag for moderation" onclick="flagItem('${flagEndpoint}')">
                    ${ICONS.flag}
                </button>
            ` : ''}
        </div>
    `;
}

function authorLink(author) {
    if (!author) return 'Unknown';
    const name = author.full_name || author.name || "Unknown User";
    return `<span class="author-link" style="cursor:pointer; text-decoration:underline;" onclick="viewProfile(${author.user_id})">${name}</span>`;
}

function renderPosts(posts) {
    const container = document.getElementById('posts');
    if(!container) return;
    
    const canFlag = currentUserRole === 'Administrator' || currentUserRole === 'Lecturer';

    container.innerHTML = posts.map(p => {
        const mine = p.author_id === currentUserId;
        const side = mine ? 'mine' : 'theirs';
        const authorName = p.author ? (p.author.full_name || p.author.name) : "User";

        const repliesHtml = (p.replies || []).map(r => {
            const replyMine = r.author_id === currentUserId;
            const replySide = replyMine ? 'mine' : 'theirs';
            const replyAuthorName = r.author ? (r.author.full_name || r.author.name) : "User";
            return `
                <div class="msg-group ${replySide} reply-row" id="reply-${r.reply_id}">
                    <div class="bubble ${r.is_flagged ? 'is-flagged' : ''}">
                        <div class="bubble-author">${replyAuthorName}</div>
                        <div class="bubble-content">${r.content}</div>
                        <div class="bubble-meta">${r.is_flagged ? '<span class="bubble-flag-tag">flagged · </span>' : ''}${timeOnly(r.replied_at || r.created_at)}</div>
                    </div>
                    ${actionsHtml('reply', r.reply_id, r.is_flagged, canFlag)}
                </div>
            `;
        }).join('');

        return `
            <div class="msg-group ${side}" id="post-${p.post_id}">
                <div class="bubble ${p.is_flagged ? 'is-flagged' : ''}">
                    <div class="bubble-author">${authorName}</div>
                    <div class="bubble-content">${p.content}</div>
                    <div class="bubble-meta">${p.is_flagged ? '<span class="bubble-flag-tag">flagged · </span>' : ''}${timeOnly(p.posted_at || p.created_at)}</div>
                </div>
                ${actionsHtml('post', p.post_id, p.is_flagged, canFlag)}
            </div>

            ${repliesHtml}

            <div class="reply-composer">
                <input type="text" id="reply-input-${p.post_id}" placeholder="Reply…"
                    onkeydown="if(event.key==='Enter'){ submitReply(${p.post_id}); }">
                <button type="button" title="Send reply" onclick="submitReply(${p.post_id})">${ICONS.send}</button>
            </div>
        `;
    }).join('') || '<div class="muted">No messages yet in this topic — start the discussion below.</div>';
}

async function submitReply(postId) {
    const input = document.getElementById(`reply-input-${postId}`);
    if (!input || !input.value.trim()) return;
    const res = await executeApiCall(`/posts/${postId}/replies`, { method: 'POST', body: { content: input.value } });
    if (res && res.message && !res.reply_id && !res.author) {
        alert(res.message);
    }
    input.value = '';
    loadTopic();
}

function toggleShareMenu(key) {
    document.querySelectorAll('.share-menu.open').forEach(m => {
        if (m.id !== `share-menu-${key}`) m.classList.remove('open');
    });
    const targetMenu = document.getElementById(`share-menu-${key}`);
    if(targetMenu) targetMenu.classList.toggle('open');
}

document.addEventListener('click', (e) => {
    if (!e.target.closest('.share-wrap')) {
        document.querySelectorAll('.share-menu.open').forEach(m => m.classList.remove('open'));
    }
});

async function shareToSocial(postId, platform) {
    const res = await executeApiCall(`/posts/${postId}/share`, { method: 'POST', body: { platform } });
    document.querySelectorAll('.share-menu.open').forEach(m => m.classList.remove('open'));

    if (!res || res.message) {
        alert((res && res.message) || 'This content could not be shared.');
        return;
    }

    const shareUrl = res.shared_url || `${window.location.origin}/topics/${topicId}#post-${postId}`;

    switch (platform) {
        case 'WhatsApp':
            window.open(`https://wa.me/?text=${encodeURIComponent(shareUrl)}`, '_blank');
            break;
        case 'Twitter':
            window.open(`https://twitter.com/intent/tweet?url=${encodeURIComponent(shareUrl)}`, '_blank');
            break;
        case 'Facebook':
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`, '_blank');
            break;
        case 'LinkedIn':
            window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(shareUrl)}`, '_blank');
            break;
        case 'Clipboard':
        default:
            try {
                await navigator.clipboard.writeText(shareUrl);
                alert('Link copied to clipboard.');
            } catch (err) {
                alert(`Copy this link: ${shareUrl}`);
            }
            break;
    }
}

async function flagItem(endpoint) {
    const res = await executeApiCall(endpoint, { method: 'POST' });
    if (res && res.message && !res.post && !res.reply) {
        alert(res.message);
    }
    loadTopic();
}

/* Form submission hooks safely mounted */
const cardForm = document.getElementById('postFormCard');
if(cardForm) {
    cardForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const excludeSelect = document.getElementById('excludeUsers');
        const exclude_user_ids = excludeSelect ? Array.from(excludeSelect.selectedOptions)
            .map(opt => parseInt(opt.value))
            .filter(id => !isNaN(id)) : [];
            
        await executeApiCall(`/topics/${topicId}/posts`, {
            method: 'POST',
            body: { content: document.getElementById('postContentCard').value, exclude_user_ids },
        });
        e.target.reset();
        const exGroup = document.getElementById('excludeGroup');
        if(exGroup) exGroup.value = '';
        loadMembersForExclusion(null);
        loadTopic();
    });
}

const composerForm = document.getElementById('postFormComposer');
if(composerForm) {
    composerForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const textarea = document.getElementById('postContentComposer');
        const res = await executeApiCall(`/topics/${topicId}/posts`, {
            method: 'POST',
            body: { content: textarea.value, exclude_user_ids: [] },
        });
        if (res && res.message && !res.author) {
            alert(res.message);
        }
        e.target.reset();
        if(textarea) textarea.style.height = 'auto';
        loadTopic();
    });
}

async function viewProfile(userId) {
    const profile = await executeApiCall(`/users/${userId}/profile`);
    if (!profile) return;

    document.getElementById('modalName').textContent = profile.full_name || profile.name;
    document.getElementById('modalRole').textContent = profile.role || '';
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
        fallback.textContent = (profile.full_name || profile.name || '?').substring(0, 2).toUpperCase();
    }

    document.getElementById('profileModal').style.display = 'flex';
}

function closeProfileModal() {
    document.getElementById('profileModal').style.display = 'none';
}

window.downloadPDF = async function() {
    try {
        // Find the token wherever your application stores it (localStorage, cookies, or meta tag)
        const token = localStorage.getItem('token') || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        const headers = {};
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
            headers['X-CSRF-TOKEN'] = token;
        }

        // Hit the endpoint. Note: adjust path to matching '/api/topics/...' or '/topics/.../export' based on backend setup
        const response = await fetch(`/api/topics/${topicId}/export`, {
            method: 'GET',
            headers: headers
        });

        if (!response.ok) {
            // Read backend error message if there is one
            const errorText = await response.text();
            throw new Error(`PDF generation failed: ${response.status} ${response.statusText}. ${errorText}`);
        }

        const blob = await response.blob();
        if (blob.size === 0) {
            throw new Error("Received an empty file from the server.");
        }
        
        // Force the browser to trigger a genuine system-level download layout prompt
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = `topic-${topicId}.pdf`;
        
        document.body.appendChild(a);
        a.click();
        
        // Clean up immediately after execution thread terminates
        setTimeout(() => {
            a.remove();
            window.URL.revokeObjectURL(url);
        }, 100);

    } catch (err) {
        console.error('Could not export PDF:', err);
        alert(`Failed to export PDF: ${err.message}`);
    }
}
</script>
@endsection