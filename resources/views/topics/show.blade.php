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

// 2. Manually boot Echo to bypass compiler bugs
if (typeof window.Echo === 'undefined' || !window.Echo) {
    console.log("Forcing manual, inline configuration for Laravel Echo...");
    
    // Explicitly import Echo if you have it compiled, otherwise build it on top of the global Pusher
    window.Echo = new window.Echo({
        broadcaster: 'reverb',
        key: '{{ env("REVERB_APP_KEY") }}', // Injects your key straight from the .env file
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

console.log("Current topic:", topicId);

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

let subscribed = false;

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
        }, 50); // A tiny 50ms delay makes sure the scroll is 100% accurate!
    }
    // --- ADD THIS HERE ---
    // Every single time a topic successfully finishes loading,
    // we must manually trigger the WebSocket subscription!
    subscribeToTopic();
}

console.log("TOPIC SCRIPT LOADED");   

  window.subscribeToTopic = function () {
//function subscribeToTopic() {
    console.log("subscribeToTopic called");
    console.log("topicId =", topicId);

    if (!topicId) {
        console.log("No topicId!");
        return;
    }


    // Check if Laravel Echo is loaded
    if (typeof window.Echo === 'undefined') {
        console.error("CRITICAL: Laravel Echo is not defined on the window object! Check if your app.js layout file is compiling and importing Echo correctly.");
        return;
    }

    console.log("Successfully calling Echo.join for topic:", topicId);
    
    try {
        
        
        window.Echo.join(`topic.${topicId}`)
            .here((users) => {
                console.log('Connected to topic! Online users:', users);
            })
            .joining((user) => {
                console.log(user.full_name + ' joined');
            })
            .leaving((user) => {
                console.log(user.full_name + ' left');
            })
            /*.listen('.message.sent', (event) => {
                console.log('WebSocket Message Received (.message.sent):', event);
                loadTopic();
            })*/
           .listen('.MessageBroadcast', (e) => { // Note the '.' prefix if using broadcastAs()
                console.log("New message received:", e.reply);
                console.log("!!! WEBSOCKET EVENT RECEIVED !!!");
                console.log("Raw event payload:", e);

                // Safe guard: check if e or e.reply exists
                if (!e || !e.reply) {
                    console.error("Payload structure mismatch! Received:", e);
                    return;
                }
               
                // 1. Identify if the message belongs to the current logged-in user
                const isMine = e.reply.author_id === currentUserId;
                const sideClass = isMine ? 'mine' : 'theirs';
                const authorName = e.reply.author ? (e.reply.author.full_name || e.reply.author.name) : "User";
                
                const formattedTime = new Date(e.reply.replied_at || e.reply.created_at).toLocaleTimeString([], { 
                    hour: '2-digit', 
                    minute: '2-digit' 
                });

                // Determine if flagging action is allowed for the UI (Matches renderPosts logic)
                const canFlag = currentUserRole === 'Administrator' || currentUserRole === 'Lecturer';

                // 2. Build the exact HTML layout used in your renderPosts() function
                const newReplyHtml = `
                    <div class="msg-group ${sideClass} reply-row" id="reply-${e.reply.reply_id}">
                        <div class="bubble ${e.reply.is_flagged ? 'is-flagged' : ''}">
                            <div class="bubble-author">${authorName}</div>
                            <div class="bubble-content">${e.reply.content}</div>
                            <div class="bubble-meta">
                                ${e.reply.is_flagged ? '<span class="bubble-flag-tag">flagged · </span>' : ''}
                                ${formattedTime}
                            </div>
                        </div>
                        ${actionsHtml('reply', e.reply.reply_id, e.reply.is_flagged, canFlag)}
                    </div>
                `;

                // 3. Find the reply composer for this specific post
                const replyInput = document.getElementById(`reply-input-${e.reply.post_id}`);
                
                if (replyInput) {
                    // Get the composer container wrapper (<div class="reply-composer">)
                    const replyComposer = replyInput.closest('.reply-composer');
                    
                    if (replyComposer) {
                        // Insert the new reply directly BEFORE the reply input composer!
                        // This guarantees it goes to the bottom of the reply stack, right above the input field.
                        replyComposer.insertAdjacentHTML('beforebegin', newReplyHtml);
                    }
                } else {
                    // Fallback: If we can't find the composer, append to the bottom of the thread
                    const postsContainer = document.getElementById('posts');
                    if (postsContainer) {
                        postsContainer.insertAdjacentHTML('beforeend', newReplyHtml);
                    }
                }

                // 4. Auto-scroll container so users see the incoming text bubble
                const postsContainer = document.getElementById('posts');
                if (postsContainer) {
                    postsContainer.scrollTop = postsContainer.scrollHeight;
                }
            });
            
    } catch (error) {
        console.error("Echo join operation failed with error:", error);
    }
}
       document.addEventListener('DOMContentLoaded', async () => {
    console.log("Page loaded. Initializing setup...");

    // 1. Try to load user
    try {
        await loadCurrentUser();
        console.log("User loaded. Role:", currentUserRole, "ID:", currentUserId);
    } catch (err) {
        console.error("Failed to load current user:", err);
    }

    // 2. Try to load topic messages
    try {
        await loadTopic();
        console.log("Topic messages loaded successfully.");
    } catch (err) {
        console.error("Failed to load topic:", err);
    }

    // 3. Try to load exclusions
    try {
        await loadGroupsForExclusion();
    } catch (err) {
        console.error("Failed to load exclusions:", err);
    }

    // 4. Try to subscribe to WebSockets (We don't await this)
    try {
        subscribeToTopic();
    } catch (err) {
        console.error("Failed to subscribe to topic channel:", err);
    }
});

/*async function loadTopic() {
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
}*/

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
        const contentVal = textarea.value.trim();
        if(!contentVal) return;

        // Reset the input immediately to make it feel fast (WhatsApp style!)
        textarea.value = '';
        if(textarea) textarea.style.height = 'auto';

        const res = await executeApiCall(`/topics/${topicId}/posts`, {
            method: 'POST',
            body: { content: contentVal, exclude_user_ids: [] },
        });

        if (res && res.message && !res.author) {
            alert(res.message);
        }

        // Refresh the thread locally to show your newly sent message instantly
        await loadTopic();
    });
}
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