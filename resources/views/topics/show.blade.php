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
@endsection

@section('scripts')
@vite(['resources/js/app.js'])
<script>
const topicId = {{ $topic }};
    if (window.Pusher) {
    window.Pusher = Pusher;
}

if (typeof window.Echo === 'undefined' || !window.Echo) {
    console.log("Forcing manual, inline configuration for Laravel Echo...");
    window.Echo = new window.Echo({
        broadcaster: 'reverb',
        key: '{{ env("REVERB_APP_KEY") }}',
        wsHost: '127.0.0.1',
        wsPort: 8080,
        forceTLS: false,
        enabledTransports: ['ws', 'wss'],
        // This forces Echo to immediately start the connection handshakes!
        disableStats: true,
    });
}
// Use standard Laravel blade echo if global window var isn't used elsewhere
//const topicId = parseInt("{{ $topic }}") || null;
// Change 'const' to 'let' at the top of your script
let topicId = parseInt("{{ $topic }}") || null; 
let activeChannelId = topicId; // Track the currently subscribed channel

let activeChannelId = topicId; 
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

async function executeApiCall(url, config = {}) {
    if (typeof api === 'function') {
        return await api(url, config);
    } else {
        console.error("The custom global wrapper function 'api()' was not found in your layout.");
        return null;
    }
}

async function loadCurrentUser() {
    const me = await executeApiCall('/me');
    if (me) {
        currentUserRole = (me.roles && me.roles.length > 0) ? me.roles[0].role_name : 'Student';
        currentUserId = me.user_id;
    }
}

async function loadTopic() {
    if (!topicId) return;

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
    if (postsContainer) {
        setTimeout(() => {
            postsContainer.scrollTop = postsContainer.scrollHeight;
        }, 50);
    }
    // --- ADD THIS HERE ---
    // Every single time a topic successfully finishes loading,
    // we must manually trigger the WebSocket subscription!
    subscribeToTopic();
}

console.log("TOPIC SCRIPT LOADED");   


console.log("TOPIC SCRIPT LOADED");   

    if (typeof window.Echo === 'undefined') {
    if (!topicId) return;
        return;
    }

    console.log("Successfully calling Echo.join for topic:", topicId);
    
    try {
        
        
        window.Echo.join(`topic.${topicId}`)
        
        
        window.Echo.join(`topic.${topicId}`)
            .here((users) => { console.log('Connected to topic! Online users:', users); })
            .joining((user) => { console.log(user.full_name + ' joined'); })
            .leaving((user) => { console.log(user.full_name + ' left'); })

                console.log("WebSocket event payload received:", e);
                if (Array.isArray(e.excluded_user_ids) && e.excluded_user_ids.includes(currentUserId)) {

                    return;
                }

                    return;
                }

                // DETECT: Identify if incoming model target is a sub-reply or parent post
                const isReply = e.reply.hasOwnProperty('post_id') && e.reply.post_id !== null;

                if (isReply) {
                    // --- CASE A: IT IS A NESTED SUB-REPLY ---
                    const replyStack = document.getElementById(`reply-stack-${e.reply.post_id}`);
                    if (replyStack) {
                        const newReplyHtml = `
                            <div class="reply-row" id="reply-${e.reply.reply_id}" style="margin-bottom:8px; padding: 6px; background: rgba(0,0,0,0.02); border-radius: 4px;">
                                <strong>${authorLink(e.reply.author)}</strong>
                                <span class="muted">${new Date(e.reply.replied_at || e.reply.created_at).toLocaleString()}</span>
                                ${e.reply.is_flagged ? '<span class="flag" style="color: #dc2626; font-weight: bold;"> · flagged</span>' : ''}
                                <div>${e.reply.content}</div>
                            </div>
                        `;
                    }
                } else {
                        loadTopic();
                    const postsContainer = document.getElementById('posts');
                    if (postsContainer) {
                    // --- CASE B: IT IS A NEW PARENT POST ---
                    const postsContainer = document.getElementById('posts');
                    if (postsContainer) {
                        if (postsContainer.querySelector('.muted') && postsContainer.children.length === 1) {
                            postsContainer.innerHTML = '';
                        }
                        
                        const targetPostId = e.reply.post_id || e.reply.id;
                        const newPostHtml = `
                            <div class="card" id="post-card-${targetPostId}" style="margin-bottom: 24px; padding: 16px;">
                                <strong>${authorLink(e.reply.author)}</strong>
                                <span class="muted">${new Date(e.reply.posted_at || e.reply.created_at).toLocaleString()}</span>
                                ${e.reply.is_flagged ? '<span class="flag" style="color: #dc2626; font-weight: bold;"> · flagged</span>' : ''}
                                <p style="margin: 12px 0;">${e.reply.content}</p>
                                <div style="margin-bottom: 12px;">
                                    <button class="btn secondary" onclick="shareToSocial(${targetPostId}, 'Clipboard')">Forward</button>
                                    <button class="btn secondary" onclick="flagPost(${targetPostId})">Flag</button>
                                </div>
                                <div style="margin-top:10px; padding-left:16px; border-left: 2px solid #d8d2c4;">
                                    <div id="reply-stack-${targetPostId}"></div>
                                    <form onsubmit="return submitReply(event, ${targetPostId})" style="margin-top: 12px; display: flex; gap: 8px;">
                                        <input type="text" id="reply-input-${targetPostId}" placeholder="Reply…" style="flex: 1; padding: 6px;" required>
                                        <button class="btn secondary" type="submit">Reply</button>
                                    </form>
                                </div>
                            </div>
                        `;
                        postsContainer.insertAdjacentHTML('beforeend', newPostHtml);
                const postsContainer = document.getElementById('posts');
                if (postsContainer) {
                    postsContainer.scrollTop = postsContainer.scrollHeight;
                }
            });
            
    } catch (error) {
        console.error("Echo join operation failed with error:", error);
    }
}
        console.error("Echo join operation failed with error:", error);
    }
}

document.addEventListener('DOMContentLoaded', async () => {

function authorLink(author) {
    if (!author) return 'Unknown';

}

function renderPosts(posts) {
    document.getElementById('posts').innerHTML = posts.map(p => `

            <strong>${authorLink(p.author)}</strong>
            <span class="muted">${new Date(p.posted_at).toLocaleString()}</span>
        <div class="card" id="post-card-${p.post_id}" style="margin-bottom: 24px; padding: 16px;">
            <strong>${authorLink(p.author)}</strong>
            <span class="muted">${new Date(p.posted_at).toLocaleString()}</span>
            ${p.is_flagged ? '<span class="flag" style="color: #dc2626; font-weight: bold;"> · flagged</span>' : ''}
            <p style="margin: 12px 0;">${p.content}</p>
            <div style="margin-bottom: 12px;">
            <div style="margin-top:10px; padding-left:16px; border-left: 2px solid #d8d2c4;">
                <button class="btn secondary" onclick="flagPost(${p.post_id})">Flag</button>
            </div>
            <div style="margin-top:10px; padding-left:16px; border-left: 2px solid #d8d2c4;">
                <div id="reply-stack-${p.post_id}">
                    ${(p.replies || []).map(r => `
                        <div class="reply-row" id="reply-${r.reply_id}" style="margin-bottom:8px; padding: 6px; background: rgba(0,0,0,0.02); border-radius: 4px;">
                            <strong>${authorLink(r.author)}</strong>
                            <span class="muted">${new Date(r.replied_at).toLocaleString()}</span>
                            ${r.is_flagged ? '<span class="flag" style="color: #dc2626; font-weight: bold;"> · flagged</span>' : ''}
                            <div>${r.content}</div>
                        </div>
                    `).join('')}
                    <button class="btn secondary" type="submit">Reply</button>
                </form>
            </div>
        </div>
    `).join('') || '<div class="muted">No posts yet in this topic.</div>';
}

async function submitReply(e, postId) {
    e.preventDefault();

async function submitReply(e, postId) {
    e.preventDefault();
    const input = document.getElementById(`reply-input-${postId}`);
    if (!input || !input.value.trim()) return false;

    await executeApiCall(`/posts/${postId}/replies`, { 
        method: 'POST', 
    loadTopic();
    return false;
}

async function shareToSocial(postId) {
    await api(`/posts/${postId}/share`, { method: 'POST', body: { platform: 'Clipboard' } });
    alert('Link copied and share logged.');
}

async function flagPost(postId) {
}
    loadTopic();
}

document.getElementById('postForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const excludeRaw = document.getElementById('excludeIds').value.trim();
    const exclude_user_ids = excludeRaw ? excludeRaw.split(',').map(s => parseInt(s.trim())) : [];

    const excludeRaw = document.getElementById('excludeIds').value.trim();
        method: 'POST',
        body: { content: document.getElementById('postContent').value, exclude_user_ids },
    });
    e.target.reset();
    loadTopic();
});

/* ---------------- Profile popup ---------------- */
async function shareToSocial(postId, platform) {
    const res = await executeApiCall(`/posts/${postId}/share`, { method: 'POST', body: { platform } });
    document.querySelectorAll('.share-menu.open').forEach(m => m.classList.remove('open'));

    if (!res || res.message) {
        alert((res && res.message) || 'This content could not be shared.');
        return;
    }

    const shareUrl = res.shared_url || `${window.location.origin}/topics/${topicId}#post-${postId}`;
    }

    const shareUrl = res.shared_url || `${window.location.origin}/topics/${topicId}#post-${postId}`;
    try {
        await navigator.clipboard.writeText(shareUrl);
        alert('Link copied to clipboard.');
    } catch (err) {
        alert(`Copy this link: ${shareUrl}`);
async function viewProfile(userId) {
}
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

window.downloadPDF = async function() {
    try {
        const targetUrl = window.location.origin + `/api/topics/${topicId}/export`;
        
        // Grab the correct token name used by your Smart Discussion Forum login system
        const token = localStorage.getItem('sdf_token');

        const headers = {
            'Accept': 'application/pdf'
        };

        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        const response = await fetch(targetUrl, {
            method: 'GET',
            headers: headers
        });

        if (!response.ok) {
            const errBody = await response.text();
            throw new Error(`Server returned status ${response.status}`);
        }

        const pdfBlob = await response.blob();
        
        if (pdfBlob.size === 0) {
            throw new Error("The server generated an empty stream.");
        }

        const blobUrl = window.URL.createObjectURL(pdfBlob);
        const downloadLink = document.createElement('a');
        downloadLink.style.display = 'none';
        downloadLink.href = blobUrl;
        downloadLink.download = `topic-${topicId}.pdf`;
        
        document.body.appendChild(downloadLink);
        downloadLink.click();
        
        setTimeout(() => {
            downloadLink.remove();
            window.URL.revokeObjectURL(blobUrl);
        }, 150);

    } catch (err) {
        console.error('PDF Export Breakdown:', err);
        alert(`Failed to export PDF: ${err.message}`);
    }
}
  
</script>
@endsection
