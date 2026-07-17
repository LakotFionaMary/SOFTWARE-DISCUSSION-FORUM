@extends('layouts.app')

@section('title', 'Student Dashboard')

@section('content')
<style>
    /* ---------- Groups panel: single drill-down view (groups -> topics -> posts) ---------- */
    #groupsBrowserContent { margin-top: 12px; }

    .back-link { display: inline-flex; 
        align-items: center; 
        gap: 6px;
        background: #f3f4f6; /* Soft gray background */
        color: #374151; /* Dark gray text */
        font-weight: 600; 
        font-size: 13.5px;
        padding: 6px 12px;
        border-radius: 20px; /* Fully rounded pill-style button */
        border: 1px solid var(--line);
        cursor: pointer; 
        text-decoration: none;
        transition: all 0.2s ease;
        margin-bottom: 8px; }
    .back-link:hover { background: #e5e7eb; /* Slightly darker gray on hover */
        color: #111827;
        text-decoration: none; /* Prevents underline */ }

    .group-item, .topic-item {
        display: flex; align-items: center; justify-content: space-between; gap: 10px;
        padding: 12px 4px; border-bottom: 1px solid var(--line); cursor: pointer;
    }
    .group-item:last-child, .topic-item:last-child { border-bottom: none; }
    .group-item:hover, .topic-item:hover { background: #eef2f1; }
    .group-item .group-info, .topic-item .group-info { min-width: 0; }
    .group-item .group-info strong, .topic-item strong { display: block; font-size: 15px; }
    .group-item .group-info .muted, .topic-item .muted { font-size: 12.5px; }
    .group-item .join-btn {
        flex-shrink: 0; padding: 5px 12px; font-size: 12.5px; border-radius: 14px;
        background: var(--accent); color: #fff; border: none; cursor: pointer;
    }
    .group-item .join-btn:hover { background: var(--accent-dark); }

    /* Chat thread + composer, reused from the standalone topic page so the
       inline preview here looks/feels the same. No fixed height/scrolling —
       it simply grows with the conversation. */
    .chat-thread {
        display: flex; flex-direction: column; gap: 4px;
        background: var(--paper); border: 1px solid var(--line); border-radius: var(--radius);
        padding: 16px; min-height: 260px; flex: 1;
    }
    .msg-group { display: flex; flex-direction: column; margin: 10px 0; max-width: 78%; }
    .msg-group.mine { align-self: flex-end; align-items: flex-end; }
    .msg-group.theirs { align-self: flex-start; align-items: flex-start; }
    /* Reply thread: a connecting guide line + indent applied straight to the
       message itself (one modifier class) instead of an extra wrapper div. */
    .msg-group.is-reply { margin-left: 26px; max-width: calc(78% - 26px); padding-left: 14px; border-left: 2px solid var(--line); }

    /* Flagged post highlight */
    .msg-group.is-flagged .bubble { outline: 2px solid #dc2626; outline-offset: 2px; }
 
    .bubble {
        padding: 8px 12px; border-radius: 12px;
        font-size: 14px; line-height: 1.4; word-wrap: break-word;
    }
    .msg-group.mine .bubble { background: #d9fdd3; border-bottom-right-radius: 3px; }
    .msg-group.theirs .bubble { background: #fff; border: 1px solid var(--line); border-bottom-left-radius: 3px; }
    .bubble-author { display: block; font-size: 12px; font-weight: 600; color: var(--accent); margin-bottom: 2px; }
    .msg-group.mine .bubble-author { display: none; }
    .bubble-text { margin: 0; white-space: pre-wrap; }

    .msg-actions { display: flex; align-items: center; gap: 8px; margin: 4px 2px 0; font-size: 11.5px; }
    .msg-group.mine .msg-actions { flex-direction: row-reverse; }
    .msg-actions .reply-link,
    .msg-actions .forward-link { color: var(--accent); cursor: pointer; }
    .msg-actions .reply-link:hover,
    .msg-actions .forward-link:hover { text-decoration: underline; }
    .msg-actions .msg-time { color: var(--slate); }
    /* Flag action (group admins only) */
    .msg-actions .flag-link { color: #dc2626; cursor: pointer; }
    .msg-actions .flag-link:hover { text-decoration: underline; }
    .msg-actions .flag-link.flagged { font-weight: 700; }
 

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

    /* Inline "View statistics" shortcut shown to group admins in the topics/posts drill-down */
    .stats-shortcut {
        padding: 5px 12px; font-size: 12.5px;
    }
 
    /* ---------- Forward message modal ---------- */
    .modal-overlay {
        position: fixed; inset: 0; background: rgba(15, 23, 20, 0.45);
        display: none; align-items: center; justify-content: center; z-index: 1000; padding: 16px;
    }
    .modal-overlay.open { display: flex; }
    .modal-box {
        background: #fff; border-radius: var(--radius); padding: 20px;
        width: 100%; max-width: 420px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    .modal-preview {
        background: #f3f8f5; border-left: 3px solid var(--accent); padding: 8px 10px;
        border-radius: 0 6px 6px 0; font-size: 13px; max-height: 90px; overflow-y: auto; white-space: pre-wrap;
    }
    .modal-box select {
        width: 100%; padding: 7px; border: 1px solid var(--line); border-radius: 6px; font-family: inherit;
    }
</style>

<div class="eyebrow">Student Dashboard</div>
<h1 id="welcome">Loading your dashboard…</h1>

<div class="dash-shell">
    <div class="dash-main">
        <!-- ================= GROUP ADMIN PANEL (students who admin a group) ================= -->
        <!-- Only reachable at all when the student is an active group admin for
             at least one group - see renderGroupAdminPanel(), which is what
             actually decides whether the sidebar item/panel exist (not just CSS). -->
        <div class="dash-panel" id="panel-group-admin">
            <div class="section-title"><h2 style="margin:0;">Group Admin</h2></div>
            <p class="muted">Groups you administer. As a group admin you can view full group statistics, the same view a lecturer sees for their own groups, and flag posts for review.</p>
            <div id="groupAdminList"></div>
        </div>

        <!-- ================= MY GROUPS ================= -->
        <div class="dash-panel" id="panel-groups">
            <!--removed dash panel-->            

            <!-- Single drill-down view: groupsBrowserContent's innerHTML is
                 fully swapped by JS between the groups list, a group's
                 topics, and a topic's posts (with a "Back" link), the same
                 way the reference page navigates - one content area, not a
                 separate div per state. -->
            <div class="card" id="groupsBrowserContent">Loading groups…</div>
        </div>

        <!-- ================= MY GRADES ================= -->
        <div class="dash-panel" id="panel-grades">
            <div class="section-title"><h2 style="margin:0;">My Grades</h2></div>
            <div id="studentGrades" class="card muted">Loading your grades…</div>
        </div>

        <!-- ================= QUIZZES ================= -->
        <div class="dash-panel" id="panel-quizzes">
            <div class="section-title"><h2 style="margin:0;">Published Quizzes</h2></div>
            <div id="studentQuizzes" class="card muted">Loading published quizzes…</div>
        </div>

        <!-- ================= RECOMMENDATIONS ================= -->
        <div class="dash-panel" id="panel-recommendations">
            <div class="section-title"><h2 style="margin:0;">Recommended Topics</h2></div>
            <div id="recommendations" class="card muted">Loading recommendations…</div>
        </div>

        <!-- ================= NOTIFICATIONS ================= -->
        <div class="dash-panel" id="panel-notifications">
            <div class="section-title"><h2 style="margin:0;">Notifications</h2></div>
            <div id="notifications" class="card muted">Loading notifications…</div>
        </div>
    </div>
</div>

<!-- One shared modal, reused for forwarding any post/reply, rather than
     building a separate picker per message. -->
<div class="modal-overlay" id="forwardModalOverlay">
    <div class="modal-box">
        <h3 style="margin-top:0; display: flex; justify-content: space-between; align-items: center;">
            <span>Forward Message</span>
            <span id="modalModeBadge" class="badge" style="font-size: 11px; background: var(--accent); color: #fff;">Internal</span>
        </h3>
        <div class="modal-preview" id="forwardPreview"></div>

        <div style="display: flex; gap: 4px; margin: 14px 0; background: #f3f4f6; padding: 4px; border-radius: 6px;">
            <button type="button" id="tabInternal" class="btn" style="flex: 1; padding: 6px; font-size: 12.5px; background: #fff; color: #000; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" onclick="setForwardMode('internal')">Inside Forum</button>
            <button type="button" id="tabExternal" class="btn secondary" style="flex: 1; padding: 6px; font-size: 12.5px;" onclick="setForwardMode('external')">Social Media</button>
        </div>

        <div id="internalForwardFields">
            <label class="muted" style="display:block; margin:14px 0 4px;">Group</label>
            <select id="forwardGroupSelect"></select>

            <label class="muted" style="display:block; margin:12px 0 4px;">Topic</label>
            <select id="forwardTopicSelect"></select>
            
            <div style="display:flex; gap:8px; margin-top:18px; justify-content:flex-end;">
                <button class="btn secondary" type="button" onclick="closeForwardModal()">Cancel</button>
                <button class="btn" type="button" onclick="confirmForward()">Forward</button>
            </div>
        </div>

        <div id="externalForwardFields" style="display: none;">
            <p class="muted" style="font-size: 13px; margin-bottom: 12px;">Choose a platform below to securely share a public reference link to this discussion thread.</p>
            
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <button class="btn" type="button" onclick="shareToPlatform('WhatsApp')" style="background: #25D366; color: #fff; text-align: left; display: flex; align-items: center; gap: 10px;">
                   <span style="font-weight: bold;">💬</span> Share on WhatsApp
              </button>
                <button class="btn" type="button" onclick="shareToPlatform('Twitter')" style="background: #111; color: #fff; text-align: left; display: flex; align-items: center; gap: 10px;">
                    <span>𝕏</span> Share on Twitter / X
                </button>
                <button class="btn" type="button" onclick="shareToPlatform('Facebook')" style="background: #1877f2; color: #fff; text-align: left; display: flex; align-items: center; gap: 10px;">
                    <span>f</span> Share on Facebook
                </button>
                <button class="btn" type="button" onclick="shareToPlatform('LinkedIn')" style="background: #0077b5; color: #fff; text-align: left; display: flex; align-items: center; gap: 10px;">
                    <span>in</span> Share on LinkedIn
                </button>
                <button class="btn secondary" type="button" onclick="shareToPlatform('Clipboard')" style="text-align: left; display: flex; align-items: center; gap: 10px;">
                    <span>🔗</span> Copy Link to Clipboard
                </button>
            </div>

            <div style="display:flex; gap:8px; margin-top:18px; justify-content:flex-end;">
                <button class="btn secondary" type="button" onclick="closeForwardModal()">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    if (!localStorage.getItem('sdf_token')) { window.location.href = '/'; }

    let myGroups = [];

    async function loadWelcome() {
        const me = await loadCurrentUser();
        if (!me) return;
        // A lecturer/admin landing here directly (e.g. old bookmark) gets
        // bounced to the dashboard that actually matches their role.
        if (window.CURRENT_ROLE !== 'student') {
            window.location.replace((window.CURRENT_ROLE === 'administrator' ? '/dashboard/admin' : '/dashboard/lecturer') + window.location.search);
            return;
        }
        document.getElementById('welcome').textContent = `Welcome, ${me.full_name}`;
    }

    /* ---------- Groups panel: single drill-down view ---------- */
    let browseView = 'groups'; // 'groups' | 'topics' | 'posts'
    let activeBrowseGroupId = null;
    let activeBrowseGroupName = '';
    let activeBrowseTopicId = null;
    let activeBrowseTopicTitle = '';
    let currentTopicMessages = []; // index -> {author, content, postId, flagged}, used by Forward + Flag
    
    //// added------------------
    let groupMembersExpanded = false;
    let allGroupMembers = []; // cache so "show more" doesn't need another API call

    // ---- Topics-list search / filter / pagination state (borrowed from index.blade.php) ----
    let browseTopicsPage = 1;
    let browseTopicsSearch = '';
    let browseTopicsCategory = '';

    function timeOnly(dt) {
        if (!dt) return '';
        return new Date(dt).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    // Group-admin check: is the current user the admin of the given group?
    // Used to gate both the inline "View statistics" shortcut and the
    // "Flag" action on posts/replies within that group's topics.
    function isGroupAdmin(groupId) {
        if (!groupId || !window.CURRENT_USER) return false;
        const g = myGroups.find(x => x.group_id === groupId);
        return !!(g && g.admin_id === window.CURRENT_USER.user_id);
    }
    window.isGroupAdmin = isGroupAdmin;

    /* ---------- Live WebSockets Subscription (Laravel Echo) ---------- */
    window.currentSubscriptionId = null;

    window.subscribeToTopic = function (topicId) {
        if (!topicId) return;

        // 1. Wait for Laravel Echo to finish loading if it's slow
        if (typeof window.Echo === 'undefined') {
            console.warn("Laravel Echo is not loaded yet! Retrying in 500ms...");
            setTimeout(() => window.subscribeToTopic(topicId), 500);
            return;
        }

        // 2. Prevent duplicate subscriptions to the same topic
        if (window.currentSubscriptionId === topicId) {
            return;
        }

        // 3. Clean up the old channel subscription if switching topics
        if (window.currentSubscriptionId && window.currentSubscriptionId !== topicId) {
            console.log("Leaving old channel: topic." + window.currentSubscriptionId);
            window.Echo.leave(`topic.${window.currentSubscriptionId}`);
        }

        window.currentSubscriptionId = topicId;
        console.log("Joining Presence Channel: topic." + topicId);

        // 4. Join the Reverb Presence Channel
        window.Echo.join(`topic.${topicId}`)
            .here((users) => {
                console.log("!!! Connected to Presence Channel! Users online:", users);
            })
            .joining((user) => {
                // Fix: Check for full_name first, then fallback to name
                const joinerName = user.full_name || user.name || "A user";
                console.log(joinerName + " joined the chat");
            })
            .leaving((user) => {
                // Fix: Check for full_name first, then fallback to name
                const leaverName = user.full_name || user.name || "A user";
                console.log(leaverName + " left the chat");
            })
            .listen('.MessageBroadcast', (e) => {
                console.log("!!! LIVE EVENT ARRIVED !!!", e);
                
                // Only append the post if we are actively looking at the correct topic screen
                if (activeBrowseTopicId !== e.topicId) return;

                const container = document.getElementById('dashPosts');
                if (!container) return;

                // If "No messages yet" is showing, clear it out
                const emptyMsg = container.querySelector('.muted');
                if (emptyMsg && emptyMsg.textContent.includes('No messages yet')) {
                    container.innerHTML = '';
                }

                const myId = window.CURRENT_USER ? window.CURRENT_USER.user_id : null;
                const mine = e.reply.author_id === myId;
                const side = mine ? 'mine' : 'theirs';
                const authorName = e.reply.author ? (e.reply.author.full_name || e.reply.author.name) : 'User';
                const timeStr = timeOnly(e.reply.posted_at || e.reply.created_at);

                // Build the post HTML markup matching your structure exactly
                const newPostHtml = renderMsgGroup(side, authorName, e.reply.content, timeStr, false, e.reply.post_id ?? e.reply.reply_id, e.reply.is_flagged);

                // Append the live message to the chat view container
                container.insertAdjacentHTML('beforeend', newPostHtml);
                container.scrollTop = container.scrollHeight;
            })
            .error((error) => {
                console.error("Presence channel subscription error:", error);
            });
            
    };//////////////added
    document.addEventListener('DOMContentLoaded', () => {
    console.log("DOM loaded. Checking for Laravel Echo...");
    
    // Check if we already have a default topic to subscribe to on load
    // (For example, if your application has a default activeBrowseTopicId variable)
    if (typeof activeBrowseTopicId !== 'undefined' && activeBrowseTopicId) {
        window.subscribeToTopic(activeBrowseTopicId);
    } else {
        console.log("No active topic selected yet. Waiting for user interaction.");
    }
});
 
    function escAttr(str) {
        return (str || '').replace(/'/g, "\\'");
    }

    async function loadGroups() {
        const data = await api('/groups');
        const groups = (data && (data.data || data)) || [];
        myGroups = groups;
        renderGroupAdminPanel(groups);
        renderGroupsBrowser();
    }

    // Swaps the ONE content div's innerHTML based on browseView, instead of
    // keeping separate group/topic/post divs all in the DOM at once.
    function renderGroupsBrowser() {
        const el = document.getElementById('groupsBrowserContent');
        if (browseView === 'topics') {
            el.innerHTML = topicsViewHtml();
            loadBrowseTopics();
            loadBrowseCategories();
        } else if (browseView === 'posts') {
            el.innerHTML = postsViewHtml();
            loadBrowsePosts();
        } else {
            el.innerHTML = groupsViewHtml();
        }
    }
    
    ///////////replaced-------------------
    function groupsViewHtml() {
        const rows = myGroups.map(g => {
            const joined = g.is_member || g.is_group_admin;
            const isBanned = g.is_banned || g.banned;
            return `
                <div class="group-item" data-group-id="${g.group_id}" onclick="${isBanned ? `alert('You are blacklisted/banned from this group.')` : `openGroupTopics(${g.group_id}, '${escAttr(g.name)}')`}">
                onclick="${joined ? `openGroupTopics(${g.group_id}, '${escAttr(g.name)}')` : `showNotMemberNotice(${g.group_id})`}">
                    <div class="group-info">
                        <strong>${g.name}${isBanned ? ' <span class="badge" style="background:#dc2626; color:#fff; margin-left:6px; font-size:11px;">Banned</span>' : ''}</strong>
                        <div class="muted">${g.members_count ?? 0} members · ${g.topics_count ?? 0} topics</div>
                        <div class="muted" id="notMemberNotice-${g.group_id}" style="display:none; color:#b45309; font-weight:600; margin-top:2px;">
                        You're not a member of this group yet — join to view topics.
                    </div>
                    ${isBanned 
                        ? '<span class="badge" style="background:#dc2626; color:#fff;">Banned</span>'
                        : (joined
                            ? '<span class="badge role-student">Joined</span>'
                            : `<button type="button" class="join-btn" onclick="joinGroupInline(event, ${g.group_id})">Join</button>`
                        )
                    }
                </div>
                ${joined
                    ? '<span class="badge role-student">Joined</span>'
                    : `<button type="button" class="join-btn" onclick="joinGroupInline(event, ${g.group_id})">Join</button>`
                }
            </div>
        `;
    }).join('') || '<div class="empty-state">No groups exist yet.</div>';
    
    // 2. Create the Top Header with the trigger button
    const headerHtml = `
        <div class="groups-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h2 style="margin: 0;">Groups</h2>
            <button class="btn" type="button" onclick="openCreateGroupModal(event)">+ Create Group</button>
        </div>
    `;

    // 3. Create the Hidden Popup Modal
    const modalHtml = `
        <div id="createGroupModal" class="modal-overlay" onclick="closeCreateGroupModalOnOuterClick(event)" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
            <div class="modal-content" style="background: white; padding: 24px; border-radius: 8px; width: 90%; max-width: 450px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); position: relative;" onclick="event.stopPropagation()">
                <span class="close-modal-btn" onclick="closeCreateGroupModal()" style="position: absolute; top: 12px; right: 16px; font-size: 24px; cursor: pointer; color: #666; line-height: 1;">&times;</span>
                <h3 style="margin-top: 0; margin-bottom: 16px;">Create a new group</h3>
                
                <form id="createGroupForm">
                    <div style="margin-bottom: 12px;">
                        <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 14px;">Group Name</label>
                        <input type="text" id="groupName" name="name" placeholder="e.g. CS301 Databases" required style="width:100%; padding:8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    
                    <div style="margin-bottom: 16px;">
                        <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 14px;">Description</label>
                        <textarea id="groupDescription" name="description" placeholder="What is this group for?" rows="3" style="width:100%; padding:8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; resize: vertical;"></textarea>
                    </div>
                    
                    <div style="display: flex; justify-content: flex-end; gap: 8px;">
                        <button type="button" class="btn btn-secondary" onclick="closeCreateGroupModal()" style="background: #e5e7eb; color: #374151; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">Cancel</button>
                        <button type="submit" class="btn" style="padding: 8px 16px;">Create group</button>
                    </div>
                </form>
            </div>
        </div>
    `;

    // 4. Assemble and return
    return `
        <div class="groups-view-container">
            ${headerHtml}
            <div class="groups-list">
                ${rows}
            </div>
            ${modalHtml}
        </div>
    `;
}
     
// Opens the modal popup
function openCreateGroupModal(event) {
    if (event) event.stopPropagation();
    const modal = document.getElementById('createGroupModal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

// Closes the modal popup and resets the form fields
function closeCreateGroupModal() {
    const modal = document.getElementById('createGroupModal');
    const form = document.getElementById('createGroupForm');
    if (modal) {
        modal.style.display = 'none';
    }
    if (form) {
        form.reset();
    }
}

// Closes the modal if the user clicks outside the modal box
function closeCreateGroupModalOnOuterClick(event) {
    const modal = document.getElementById('createGroupModal');
    if (event.target === modal) {
        closeCreateGroupModal();
    }
}

/*-----------------------------------------------*/
    // ---- Topics view now includes search box + category filter + load-more,
    // borrowed from the standalone group-topics page (index.blade.php). ----
    function topicsViewHtml() {
        // Group admins get a quick shortcut straight to their group statistics
        // page without having to leave the drill-down view (the full stats
        // link still also lives in the "Group Admin" sidebar panel).
        const statsShortcut = isGroupAdmin(activeBrowseGroupId)
            ? `<a class="btn secondary stats-shortcut" style="float:right;" href="/groups/${activeBrowseGroupId}/statistics">View statistics</a>`
            : '';
            /* removed back to groups and added create topic, searching , filtering */
        return `
            ${statsShortcut}
            <h3 style="margin: 12px 0 2px;">${activeBrowseGroupName}</h3>
            <p class="muted" style="margin: 0 0 14px;">Topics in this group</p>
        
        <div style="display:flex; align-items:center; justify-content:space-between; margin: 12px 0 14px;">
            <div>
                <h3 style="margin:0;">${activeBrowseGroupName}</h3>
                <p class="muted" style="margin:2px 0 0;">Topics in this group</p>
            </div>
            <div style="display:flex; gap:8px;">
                <button class="btn secondary" type="button" onclick="openGroupMembersModal()" title="View members" style="display:flex; align-items:center; gap:6px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Members
                </button>
                <button class="btn" type="button" onclick="openCreateTopicModal()">+ New Topic</button>
            </div>
        </div>

        <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:12px;">
            <div style="position:relative; flex:1; min-width:180px;">
                <input type="text" id="browseTopicSearch" placeholder="Search topics…" value="${escAttr(browseTopicsSearch)}"
                    style="width:100%; padding:8px 40px 8px 8px; box-sizing:border-box;">
                <button type="button" id="browseSearchBtn" aria-label="Search"
                    style="position:absolute; right:4px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; font-size:18px; padding:6px;">🔍</button>
            </div>
            <select id="browseCategoryFilter" style="min-width:170px; padding:8px;">
                <option value="">All categories</option>
            </select>
        </div>

        <div id="groupTopicsList" class="muted">Loading topics…</div>

        <div style="text-align:center; margin: 14px 0;">
            <button class="btn secondary" id="browseLoadMoreBtn" type="button" style="display:none;">Load more</button>
        </div>

        ${createTopicModalHtml()}
        ${groupMembersModalHtml()}
    `;
}
 
    function postsViewHtml() {
        const statsShortcut = isGroupAdmin(activeBrowseGroupId)
            ? `<a class="btn secondary stats-shortcut" href="/groups/${activeBrowseGroupId}/statistics">Statistics</a>`
            : '';
        return `
            <a class="back-link" onclick="browseGoBack()"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Back to topics
            </a>
            <div style="display:flex; align-items:center; justify-content:space-between; margin: 12px 0 14px;">
                <h3 style="margin:0;">${activeBrowseTopicTitle}</h3>
                <div style="display:flex; gap:8px;">
                    ${statsShortcut}
                    <button class="btn secondary" type="button" onclick="exportDashTopicPdf()">PDF</button>
                </div>
            </div>
            <div class="chat-thread" id="dashPosts"><div class="muted">Loading messages…</div></div>
            <form class="composer" id="dashComposerForm">
                <textarea id="dashComposerInput" rows="1" placeholder="Type a message…" required
                    oninput="this.style.height='auto'; this.style.height=(this.scrollHeight)+'px';"></textarea>
                <button class="composer-send" type="submit" title="Send">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                </button>
            </form>
        `;
    }

    function createTopicModalHtml() {
    return `
        <div id="createTopicModal" class="modal-overlay" onclick="closeCreateTopicModalOnOuterClick(event)" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
            <div class="modal-content" style="background:white; padding:24px; border-radius:8px; width:90%; max-width:420px; box-shadow:0 4px 15px rgba(0,0,0,0.2); position:relative;" onclick="event.stopPropagation()">
                <span class="close-modal-btn" onclick="closeCreateTopicModal()" style="position:absolute; top:12px; right:16px; font-size:24px; cursor:pointer; color:#666; line-height:1;">&times;</span>
                <h3 style="margin-top:0; margin-bottom:16px;">Start a new topic</h3>
                <form id="createTopicForm">
                    <div style="margin-bottom:16px;">
                        <label style="display:block; margin-bottom:6px; font-weight:600; font-size:14px;">Topic title</label>
                        <input type="text" id="newTopicTitleModal" name="title" placeholder="e.g. Week 4 discussion" required style="width:100%; padding:8px; box-sizing:border-box; border:1px solid #ccc; border-radius:4px;">
                    </div>
                    <div style="display:flex; justify-content:flex-end; gap:8px;">
                        <button type="button" class="btn btn-secondary" onclick="closeCreateTopicModal()" style="background:#e5e7eb; color:#374151; border:none; padding:8px 16px; border-radius:4px; cursor:pointer;">Cancel</button>
                        <button type="submit" class="btn" style="padding:8px 16px;">Create topic</button>
                    </div>
                </form>
            </div>
        </div>
    `;
}
/*------------------------ added---------------------------------*/
function groupMembersModalHtml() {
    return `
        <div id="groupMembersModal" class="modal-overlay" onclick="closeGroupMembersModalOnOuterClick(event)" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
            <div class="modal-content" style="background:white; padding:24px; border-radius:8px; width:90%; max-width:420px; max-height:70vh; overflow-y:auto; box-shadow:0 4px 15px rgba(0,0,0,0.2); position:relative;" onclick="event.stopPropagation()">
                <span class="close-modal-btn" onclick="closeGroupMembersModal()" style="position:absolute; top:12px; right:16px; font-size:24px; cursor:pointer; color:#666; line-height:1;">&times;</span>
                <h3 style="margin-top:0; margin-bottom:16px;">${activeBrowseGroupName} members</h3>
                <div id="groupMembersList" class="muted">Loading members…</div>
            </div>
        </div>
    `;
}

function openCreateTopicModal() {
    const modal = document.getElementById('createTopicModal');
    if (modal) modal.style.display = 'flex';
}
window.openCreateTopicModal = openCreateTopicModal;

function closeCreateTopicModal() {
    const modal = document.getElementById('createTopicModal');
    const form = document.getElementById('createTopicForm');
    if (modal) modal.style.display = 'none';
    if (form) form.reset();
}
window.closeCreateTopicModal = closeCreateTopicModal;

function closeCreateTopicModalOnOuterClick(event) {
    const modal = document.getElementById('createTopicModal');
    if (event.target === modal) closeCreateTopicModal();
}
window.closeCreateTopicModalOnOuterClick = closeCreateTopicModalOnOuterClick;

async function openGroupMembersModal() {
    const modal = document.getElementById('groupMembersModal');
    if (modal) modal.style.display = 'flex';
    await loadGroupMembers();
}
window.openGroupMembersModal = openGroupMembersModal;

function closeGroupMembersModal() {
    const modal = document.getElementById('groupMembersModal');
    if (modal) modal.style.display = 'none';
}
window.closeGroupMembersModal = closeGroupMembersModal;

function closeGroupMembersModalOnOuterClick(event) {
    const modal = document.getElementById('groupMembersModal');
    if (event.target === modal) closeGroupMembersModal();
}
window.closeGroupMembersModalOnOuterClick = closeGroupMembersModalOnOuterClick;

async function loadGroupMembers() {
    if (!activeBrowseGroupId) return;
    const listEl = document.getElementById('groupMembersList');
    if (!listEl) return;
    listEl.innerHTML = 'Loading members…';

    const data = await api(`/groups/${activeBrowseGroupId}/members`);
    allGroupMembers = (data && (data.data || data)) || [];
    groupMembersExpanded = false; // always start collapsed when modal opens

    renderGroupMembersList();
}
window.loadGroupMembers = loadGroupMembers;

function renderGroupMembersList() {
    const listEl = document.getElementById('groupMembersList');
    if (!listEl) return;

    if (!allGroupMembers.length) {
        listEl.innerHTML = '<div class="empty-state">No members yet.</div>';
        return;
    }

    const MIN_SHOWN = 3;
    const visibleMembers = groupMembersExpanded ? allGroupMembers : allGroupMembers.slice(0, MIN_SHOWN);
    const hasMore = allGroupMembers.length > MIN_SHOWN;

    const rowsHtml = visibleMembers.map(m => `
        <div style="display:flex; align-items:center; justify-content:space-between; padding:8px 0; border-bottom:1px solid var(--line);">
            <strong>${m.full_name || m.name}</strong>
            ${m.is_admin ? '<span class="badge" style="background:var(--accent); color:#fff; font-size:11px;">Admin</span>' : ''}
        </div>
    `).join('');

    // Only scrollable once expanded, so the collapsed "peek" of 3 stays compact.
    const scrollWrapStyle = groupMembersExpanded ? 'max-height:220px; overflow-y:auto;' : '';

    const toggleHtml = hasMore ? `
        <button type="button" class="back-link" onclick="toggleGroupMembersExpanded()" style="margin-top:10px; width:100%; justify-content:center;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="transform: rotate(${groupMembersExpanded ? '180deg' : '0deg'}); transition: transform 0.15s ease;">
                <polyline points="6 9 12 15 18 9"/>
            </svg>
            ${groupMembersExpanded ? 'Show less' : `Show ${allGroupMembers.length - MIN_SHOWN} more`}
        </button>
    ` : '';

    listEl.innerHTML = `
        <div style="${scrollWrapStyle}">${rowsHtml}</div>
        ${toggleHtml}
    `;
}

function toggleGroupMembersExpanded() {
    groupMembersExpanded = !groupMembersExpanded;
    renderGroupMembersList();
}
window.toggleGroupMembersExpanded = toggleGroupMembersExpanded;

    function openGroupTopics(groupId, groupName) {
        const g = myGroups.find(x => x.group_id === groupId);
        if (g && (g.is_banned || g.banned)) {
            alert("You are blacklisted and banned from accessing this group.");
            return;
        }
        //////////////added---------------
        const group = myGroups.find(g => g.group_id === groupId);
    const joined = group && (group.is_member || group.is_group_admin);
    if (!joined) {
        alert('Join this group first to view its topics.');
        return;
    }
        activeBrowseGroupId = groupId;
        activeBrowseGroupName = groupName;
        activeBrowseTopicId = null;
        // Reset the search/filter/pagination state whenever we enter a
        // (possibly different) group's topic list fresh.
        browseTopicsPage = 1;
        browseTopicsSearch = '';
        browseTopicsCategory = '';
        browseView = 'topics';
        renderGroupsBrowser();
    }
    window.openGroupTopics = openGroupTopics;

    /////////added------------
    function showNotMemberNotice(groupId) {
    // Hide any other open notices first, so only one shows at a time
    document.querySelectorAll('[id^="notMemberNotice-"]').forEach(el => el.style.display = 'none');

    const el = document.getElementById(`notMemberNotice-${groupId}`);
    if (!el) return;
    el.style.display = 'block';

    // Auto-hide after a few seconds so it doesn't linger forever
    clearTimeout(el._hideTimer);
    el._hideTimer = setTimeout(() => { el.style.display = 'none'; }, 3000);
}
window.showNotMemberNotice = showNotMemberNotice;

    function openTopicPosts(topicId, title) {
        activeBrowseTopicId = topicId;
        activeBrowseTopicTitle = title;
        browseView = 'posts';
        renderGroupsBrowser();
        if (typeof window.subscribeToTopic === 'function') {
            window.subscribeToTopic(topicId); // Explicitly pass the topic ID to subscribe!
        }
    }
    window.openTopicPosts = openTopicPosts;

    function browseGoBack() {
        if (browseView === 'posts') {
            browseView = 'topics';
            activeBrowseTopicId = null;
        } else if (browseView === 'topics') {
            browseView = 'groups';
            activeBrowseGroupId = null;
        }
        renderGroupsBrowser();
    }
    window.browseGoBack = browseGoBack;

    /* ---------- Group members toggle (names under each group card in Groups list) ---------- */
    const groupMembersCache = {};

    async function toggleGroupMembers(event, groupId) {
        event.stopPropagation();
        const namesEl = document.getElementById(`membersNames-${groupId}`);
        const toggleEl = document.getElementById(`membersToggle-${groupId}`);
        if (!namesEl || !toggleEl) return;

        const isOpen = namesEl.classList.contains('open');
        if (isOpen) {
            namesEl.classList.remove('open');
            toggleEl.textContent = 'Show members';
            return;
        }

        toggleEl.textContent = 'Hide members';
        namesEl.classList.add('open');

        if (groupMembersCache[groupId]) {
            namesEl.innerHTML = groupMembersCache[groupId];
            return;
        }

        namesEl.innerHTML = '<span class="muted">Loading members…</span>';

        const data = await api(`/groups/${groupId}/members`);
        const members = (data && (data.data || data)) || [];

        const html = members.map(m => `<span class="member-chip">${m.full_name || m.name}</span>`).join('')
            || '<span class="muted">No members yet.</span>';

        groupMembersCache[groupId] = html;
        namesEl.innerHTML = html;
    }
    window.toggleGroupMembers = toggleGroupMembers;

 
    async function joinGroupInline(event, groupId) {
        event.stopPropagation();
        const ok = window.confirm('By joining, you agree to the group rules (see /group-rules). Continue?');
        if (!ok) return;

        const response = await api(`/groups/${groupId}/join`, { method: 'POST', body: { rules_accepted: true } });
        if (response && response.message && !response.group_id) {
            alert(response.message);
        }
        await loadGroups();
    }
    window.joinGroupInline = joinGroupInline;

    // Only groups where the API says this user can actually view statistics
    // (Administrator, group owner, or active group admin - see
    // GroupController::index) get a card here; everyone else never even
    // sees the "Group Admin" item in the sidebar.
    function renderGroupAdminPanel(groups) {
        const adminGroups = groups.filter(g =>g.admin_id == window.CURRENT_USER.user_id);
        const tab = document.getElementById('navGroupAdmin');

        if (tab) tab.style.display = adminGroups.length ? 'flex' : 'none';

        document.getElementById('groupAdminList').innerHTML = adminGroups.map(g => `
            <div class="card">
                <strong>${g.name}</strong>
                <div class="muted">${g.members_count ?? 0} members · ${g.topics_count ?? 0} topics</div>
                <div style="margin-top: 8px; display:flex; gap:8px;">
                    <a class="btn btn-secondary" href="/groups/${g.group_id}/statistics" style="padding: 4px 10px; font-size: 13px;">View group statistics</a>
                </div>
            </div>
        `).join('');
    }

    // ---- Topics list: search + category filter + pagination, mirroring
    // index.blade.php's loadTopics()/loadCategories(). ----
    async function loadBrowseTopics(reset = true) {
        if (!activeBrowseGroupId) return;
        const listEl = document.getElementById('groupTopicsList');
        if (!listEl) return;
 
        const data = await api(`/groups/${activeBrowseGroupId}/topics`);
        
        // Block access dynamically if API returns ban restrictions
        if (data && (data.error || data.message) && (data.error?.toLowerCase().includes('ban') || data.message?.toLowerCase().includes('ban') || data.error?.toLowerCase().includes('blacklist') || data.message?.toLowerCase().includes('blacklist'))) {
            listEl.innerHTML = `<div class="empty-state" style="color: #dc2626; font-weight: bold;">${data.error || data.message}</div>`;
            const form = document.getElementById('newTopicFormInline');
            if (form) form.style.display = 'none';
            return;
        }

        const topics = (data && (data.data || data)) || [];
 
        listEl.innerHTML = topics.map(t => `

        if (reset) {
            browseTopicsPage = 1;
            listEl.innerHTML = 'Loading topics…';
        }

        const params = new URLSearchParams({ page: browseTopicsPage });
        if (browseTopicsSearch) params.set('search', browseTopicsSearch);
        if (browseTopicsCategory) params.set('category', browseTopicsCategory);

        const data = await api(`/groups/${activeBrowseGroupId}/topics?${params.toString()}`);
        const items = (data && (data.data || data)) || [];

        const rowsHtml = items.map(t => `
            <div class="topic-item" data-topic-id="${t.topic_id}" onclick="openTopicPosts(${t.topic_id}, '${escAttr(t.title)}')">
                <div class="group-info">
                    <strong>${t.title}</strong>
                    <div class="muted">${t.category ?? 'General'} · ${t.posts_count ?? 0} ${(t.posts_count === 1) ? 'reply' : 'replies'}</div>
                </div>
            </div>
        `).join('');

        if (reset) {
            listEl.innerHTML = rowsHtml || '<div class="empty-state">No topics match your search.</div>';
        } else {
            listEl.insertAdjacentHTML('beforeend', rowsHtml);
        }

        const hasMore = !!(data && data.next_page_url);
        const moreBtn = document.getElementById('browseLoadMoreBtn');
        if (moreBtn) moreBtn.style.display = hasMore ? 'inline-block' : 'none';
    }

    async function loadBrowseCategories() {
        if (!activeBrowseGroupId) return;
        const select = document.getElementById('browseCategoryFilter');
        if (!select) return;

        const cats = await api(`/groups/${activeBrowseGroupId}/topics/categories`) || [];
        const previousValue = select.value;
        select.innerHTML = '<option value="">All categories</option>' +
            cats.map(c => `<option value="${c}">${c}</option>`).join('');
        select.value = previousValue;
    }

    async function loadBrowsePosts() {
        if (!activeBrowseTopicId) return;
        const container = document.getElementById('dashPosts');
        if (!container) return;

        const t = await api(`/topics/${activeBrowseTopicId}`);
        if (!t || t.message || t.error) {
            const errorMsg = (t && (t.error || t.message)) || 'This topic could not be loaded.';
            container.innerHTML = `<div class="muted" style="color: #dc2626; font-weight: bold;">${errorMsg}</div>`;
            const composer = document.getElementById('dashComposerForm');
            if (composer) composer.style.display = 'none';
            return;
        }

        // Ensure composer form visibility if accessible
        const composer = document.getElementById('dashComposerForm');
        if (composer) composer.style.display = 'flex';
 
        const myId = window.CURRENT_USER ? window.CURRENT_USER.user_id : null;
        const posts = t.posts || [];
 
        // Reset the lookup table that Forward/Flag use to find a message's
        // full content + id by index, without stuffing raw/quoted text into
        // onclick attrs.
        
        // Reset the lookup table that Forward uses to find a message's full
        // content by index, without stuffing raw/quoted text into onclick attrs.
        currentTopicMessages = [];

        container.innerHTML = posts.map(p => {
            const mine = p.author_id === myId;
            const side = mine ? 'mine' : 'theirs';
            const authorName = p.author ? (p.author.full_name || p.author.name) : 'User';

            const repliesHtml = (p.replies || []).map(r => {
                const replyMine = r.author_id === myId;
                const replySide = replyMine ? 'mine' : 'theirs';
                const replyAuthorName = r.author ? (r.author.full_name || r.author.name) : 'User';
                return renderMsgGroup(replySide, replyAuthorName, r.content, timeOnly(r.replied_at || r.created_at), true, r.reply_id ?? r.post_id, r.is_flagged);
            }).join('');

            return renderMsgGroup(side, authorName, p.content, timeOnly(p.posted_at || p.created_at), false) + repliesHtml;
        }).join('') || '<div class="muted">No messages yet in this topic — start the discussion below.</div>';

        container.scrollTop = container.scrollHeight;
    }
 
    // One bubble + its Reply/Forward/Flag/timestamp row. `isReply` adds the
    // connecting-line modifier class AND tells flagPost() which endpoint to
    // call (MODIFIED — this used to only affect styling; see flagPost
    // below). `postId` is the post/reply's database id (used by Forward's
    // share endpoint and by Flag). `flagged` reflects the post's current
    // moderation state as returned by the API.
    function renderMsgGroup(side, authorName, content, time, isReply, postId, flagged) {
        const msgIndex = currentTopicMessages.length;
        currentTopicMessages.push({ author: authorName, content, postId, isReply: !!isReply, flagged: !!flagged });

        // Flag is only offered to the admin of the group the active topic
        // belongs to — mirrors the server-side authorization that must also
        // be enforced on the /posts/{id}/flag endpoint itself.
        const canFlag = isGroupAdmin(activeBrowseGroupId);
        const flagHtml = canFlag
            ? `<a class="flag-link${flagged ? ' flagged' : ''}" onclick="flagPost(${msgIndex})">${flagged ? 'Flagged' : 'Flag'}</a>`
            : '';
 
        return `
            <div class="msg-group ${side}${isReply ? ' is-reply' : ''}${flagged ? ' is-flagged' : ''}" id="${postId ? 'post-' + postId : ''}">
                <div class="bubble">
                    <span class="bubble-author">${authorName}</span>
                    <p class="bubble-text">${content}</p>
                </div>
                <div class="msg-actions">
                    <a class="reply-link" onclick="focusComposerWithMention('${authorName.replace(/'/g, "\\'")}')">Reply</a>
                    <a class="forward-link" onclick="openForwardModal(${msgIndex})">Forward</a>
                    ${flagHtml}
                    <span class="msg-time">${time}</span>
                </div>
            </div>
        `;
    }

   
    async function flagPost(msgIndex) {
        const msg = currentTopicMessages[msgIndex];
        if (!msg || !msg.postId) return;
        if (!isGroupAdmin(activeBrowseGroupId)) return; // client-side guard only; server must enforce this too

        const ok = window.confirm(msg.flagged ? 'Remove flag from this message?' : 'Flag this message for review?');
        if (!ok) return;

        const endpoint = msg.isReply ? `/replies/${msg.postId}/flag` : `/posts/${msg.postId}/flag`;
        const response = await api(endpoint, {
            method: 'POST',
            body: { flagged: !msg.flagged }
        });

        if (response && response.error) {
            alert(response.error);
            return;
        }

        // Notify if flagging triggered a blacklist and ban
        if (response && response.message) {
            alert(response.message);
        }

        msg.flagged = !msg.flagged;

        // Update the flag link and bubble highlight in place instead of
        // re-rendering the whole thread.
        const postEl = document.getElementById(`post-${msg.postId}`);
        if (postEl) {
            postEl.classList.toggle('is-flagged', msg.flagged);
            const flagLink = postEl.querySelector('.flag-link');
            if (flagLink) {
                flagLink.textContent = msg.flagged ? 'Flagged' : 'Flag';
                flagLink.classList.toggle('flagged', msg.flagged);
            }
        }
    }
    window.flagPost = flagPost;

    /* ---------- Post exclusion checklist ---------- */
    async function loadGroupMembersForExclusion() {
        const listEl = document.getElementById('dashExclusionList');
        if (!listEl || !activeBrowseGroupId) return;

        const data = await api(`/groups/${activeBrowseGroupId}/members`);
        const members = (data && (data.data || data)) || [];
        const myId = window.CURRENT_USER ? window.CURRENT_USER.user_id : null;

        listEl.innerHTML = members
            .filter(m => m.user_id !== myId)
            .map(m => `
                <label>
                    <input type="checkbox" value="${m.user_id}">
                    ${m.full_name || m.name}
                </label>
            `).join('') || '<div class="muted" style="font-size:13px;">No other members in this group.</div>';
    }
    window.loadGroupMembersForExclusion = loadGroupMembersForExclusion;

 
    // Clicking "Reply" under a message jumps to the composer and, if it's
    // empty, pre-fills an @mention of who's being replied to.
    function focusComposerWithMention(authorName) {
        const textarea = document.getElementById('dashComposerInput');
        if (!textarea) return;
        if (!textarea.value.trim()) {
            textarea.value = `@${authorName} `;
            textarea.style.height = 'auto';
            textarea.style.height = (textarea.scrollHeight) + 'px';
        }
        textarea.focus();
    }
    window.focusComposerWithMention = focusComposerWithMention;

    async function exportDashTopicPdf() {
        if (!activeBrowseTopicId) return;
        try {
            const token = localStorage.getItem('sdf_token');
            const headers = { 'Accept': 'application/pdf' };
            if (token) headers['Authorization'] = `Bearer ${token}`;

            const response = await fetch(window.location.origin + `/api/topics/${activeBrowseTopicId}/export`, { method: 'GET', headers });
            if (!response.ok) throw new Error(`Server returned status ${response.status}`);

            const pdfBlob = await response.blob();
            if (pdfBlob.size === 0) throw new Error('The server generated an empty file.');

            const blobUrl = window.URL.createObjectURL(pdfBlob);
            const link = document.createElement('a');
            link.style.display = 'none';
            link.href = blobUrl;
            link.download = `topic-${activeBrowseTopicId}.pdf`;
            document.body.appendChild(link);
            link.click();
            setTimeout(() => { link.remove(); window.URL.revokeObjectURL(blobUrl); }, 150);
        } catch (err) {
            alert(`Failed to export PDF: ${err.message}`);
        }
    }
    window.exportDashTopicPdf = exportDashTopicPdf;

    /* ---------- Forward message modal ---------- */
    /* ---------- Forward & Social Share Modal Controls ---------- */
    let forwardMessageIndex = null;
    let forwardMode = 'internal'; // 'internal' | 'external'

    function openForwardModal(msgIndex) {
        const msg = currentTopicMessages[msgIndex];
        if (!msg) return;
        forwardMessageIndex = msgIndex;

        // Populate raw text preview in the modal
        document.getElementById('forwardPreview').textContent = `${msg.author}: ${msg.content}`;

        // Reset to Internal view on open
        setForwardMode('internal');

        // Populate internal options
        const groupSelect = document.getElementById('forwardGroupSelect');
        groupSelect.innerHTML = myGroups.map(g => `<option value="${g.group_id}">${g.name}</option>`).join('')
            || '<option value="">You have not joined any groups</option>';
        groupSelect.onchange = () => populateForwardTopics(groupSelect.value);

        document.getElementById('forwardModalOverlay').classList.add('open');

        if (myGroups.length) {
            populateForwardTopics(myGroups[0].group_id);
        } else {
            document.getElementById('forwardTopicSelect').innerHTML = '';
        }
    }
    window.openForwardModal = openForwardModal;

    // Toggles fields between Forum Groups and External Social Apps
    function setForwardMode(mode) {
        forwardMode = mode;
        const badge = document.getElementById('modalModeBadge');
        const tabInternal = document.getElementById('tabInternal');
        const tabExternal = document.getElementById('tabExternal');
        const internalFields = document.getElementById('internalForwardFields');
        const externalFields = document.getElementById('externalForwardFields');

        if (mode === 'external') {
            badge.textContent = 'External';
            badge.style.background = '#10b981'; // Green color for external
            tabExternal.className = 'btn';
            tabExternal.style.background = '#fff';
            tabExternal.style.color = '#000';
            tabInternal.className = 'btn secondary';
            tabInternal.style.background = '';
            tabInternal.style.color = '';
            internalFields.style.display = 'none';
            externalFields.style.display = 'block';
        } else {
            badge.textContent = 'Internal';
            badge.style.background = 'var(--accent)';
            tabInternal.className = 'btn';
            tabInternal.style.background = '#fff';
            tabInternal.style.color = '#000';
            tabExternal.className = 'btn secondary';
            tabExternal.style.background = '';
            tabExternal.style.color = '';
            internalFields.style.display = 'block';
            externalFields.style.display = 'none';
        }
    }
    window.setForwardMode = setForwardMode;

    async function populateForwardTopics(groupId) {
        const topicSelect = document.getElementById('forwardTopicSelect');
        if (!groupId) { topicSelect.innerHTML = ''; return; }
        topicSelect.innerHTML = '<option>Loading…</option>';

        const data = await api(`/groups/${groupId}/topics`);
        const topics = (data && (data.data || data)) || [];
        topicSelect.innerHTML = topics.map(t => `<option value="${t.topic_id}">${t.title}</option>`).join('')
            || '<option value="">No topics in this group</option>';
    }

    function closeForwardModal() {
        document.getElementById('forwardModalOverlay').classList.remove('open');
        forwardMessageIndex = null;
    }
    window.closeForwardModal = closeForwardModal;

    /* ---------- API Log integration & Redirection ---------- */
    async function shareToPlatform(platform) {
        if (forwardMessageIndex === null) return;
        const msg = currentTopicMessages[forwardMessageIndex];
        
        const postId = msg.postId || activeBrowseTopicId; 

        try {
            // Write record to social_Media_Share & check exclusion constraints via API
            const response = await api(`/posts/${postId}/share`, {
                method: 'POST',
                body: { platform: platform }
            });

            if (response && response.error) {
                alert(response.error);
                return;
            }

            const shareUrl = response.url;
            const textToShare = `Check out this post on the Student Discussion Forum:\n"${msg.content.substring(0, 100)}..."\nRead more here: ${shareUrl}`;

            // Step 6 & 7: Redirect or Deep Link
            let targetUrl = '';
            switch(platform) {
                case 'WhatsApp':
                    // Uses WhatsApp's public API endpoint to target mobile apps & web clients smoothly
                    targetUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(textToShare)}`;
                    window.open(targetUrl, '_blank');
                    break;
                case 'Twitter':
                    targetUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(textToShare)}`;
                    window.open(targetUrl, '_blank');
                    break;
                case 'Facebook':
                    targetUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`;
                    window.open(targetUrl, '_blank');
                    break;
                case 'LinkedIn':
                    targetUrl = `https://www.linkedin.com/sharing/shareArticle?mini=true&url=${encodeURIComponent(shareUrl)}&title=${encodeURIComponent('Forum Discussion')}&summary=${encodeURIComponent(textToShare)}`;
                    window.open(targetUrl, '_blank');
                    break;
                case 'Clipboard':
                default:
                    await navigator.clipboard.writeText(textToShare);
                    alert("Reference link & message copied to clipboard!");
                    break;
            }
            closeForwardModal();
        } catch (err) {
            alert(`Sharing action failed: ${err.message}`);
        }
    }
    
    window.shareToPlatform = shareToPlatform;

    // Keep existing internal confirmForward function intact
    async function confirmForward() {
        if (forwardMessageIndex === null) return;
        const msg = currentTopicMessages[forwardMessageIndex];
        const topicId = document.getElementById('forwardTopicSelect').value;
        if (!msg || !topicId) return;

        const forwardedContent = `Forwarded from ${msg.author}:\n${msg.content}`;
        await api(`/topics/${topicId}/posts`, { method: 'POST', body: { content: forwardedContent, exclude_user_ids: [] } });

        closeForwardModal();

        if (activeBrowseTopicId && Number(topicId) === activeBrowseTopicId) {
            loadBrowsePosts();
        }
    }
    window.confirmForward = confirmForward;
    /*--------------------------------------------------------*/
    // Delegated: both forms are re-created whenever renderGroupsBrowser()
    // swaps views, so we listen on the always-present container instead of
    // binding directly to elements that come and go.
    document.getElementById('groupsBrowserContent').addEventListener('submit', async (e) => {
        if (e.target && e.target.id === 'createGroupForm') {
            e.preventDefault();
            const nameInput = document.getElementById('groupName');
            const descInput = document.getElementById('groupDescription');
            const response = await api('/groups', 
            { method: 'POST', body: { name: nameInput.value, description: descInput.value } });
            if (response && response.message && !response.group_id) {
                alert(response.message);
                return;
            }
            closeCreateGroupModal(); // closes modal + resets fields, only on success now
            await loadGroups();      // re-renders groupsViewHtml, 
            nameInput.value = '';
            descInput.value = '';
            loadGroups();
            /*//////////////////////////////////////////*/
        } else if (e.target && e.target.id === 'createTopicForm') {
    e.preventDefault();
    if (!activeBrowseGroupId) return;
    const input = document.getElementById('newTopicTitleModal');
    const response = await api(`/groups/${activeBrowseGroupId}/topics`, { method: 'POST', body: { title: input.value } });
    if (response && response.message && !response.topic_id) {
        alert(response.message);
        return;
    }
    closeCreateTopicModal();
    loadBrowseTopics(true); // refreshes groupTopicsList so the new topic shows up under "web"
    loadBrowseCategories(); // in case the new topic introduced a new category

        } else if (e.target && e.target.id === 'dashComposerForm') {
            e.preventDefault();
            if (!activeBrowseTopicId) return;
            const textarea = document.getElementById('dashComposerInput');
            const excludeIds = Array.from(document.querySelectorAll('#dashExclusionList input[type="checkbox"]:checked'))
                .map(cb => Number(cb.value));
            await api(`/topics/${activeBrowseTopicId}/posts`, { method: 'POST', body: { content: textarea.value, exclude_user_ids: excludeIds } });
            textarea.value = '';
            textarea.style.height = 'auto';
            loadBrowsePosts();
        }
    });

    // ---- Topics-list search / category filter / load-more, delegated on
    // the same always-present container (borrowed from index.blade.php). ----
    let browseSearchDebounce;

    document.getElementById('groupsBrowserContent').addEventListener('input', (e) => {
        if (e.target && e.target.id === 'browseTopicSearch') {
            clearTimeout(browseSearchDebounce);
            browseSearchDebounce = setTimeout(() => {
                browseTopicsSearch = e.target.value.trim();
                loadBrowseTopics(true);
            }, 300);
        }
    });

    document.getElementById('groupsBrowserContent').addEventListener('keydown', (e) => {
        if (e.target && e.target.id === 'browseTopicSearch' && e.key === 'Enter') {
            clearTimeout(browseSearchDebounce);
            browseTopicsSearch = e.target.value.trim();
            loadBrowseTopics(true);
        }
    });

    document.getElementById('groupsBrowserContent').addEventListener('click', (e) => {
        if (e.target && e.target.id === 'browseSearchBtn') {
            clearTimeout(browseSearchDebounce);
            const input = document.getElementById('browseTopicSearch');
            browseTopicsSearch = input ? input.value.trim() : '';
            loadBrowseTopics(true);
        }
        if (e.target && e.target.id === 'browseLoadMoreBtn') {
            browseTopicsPage++;
            loadBrowseTopics(false);
        }
    });

    document.getElementById('groupsBrowserContent').addEventListener('change', (e) => {
        if (e.target && e.target.id === 'browseCategoryFilter') {
            browseTopicsCategory = e.target.value;
            loadBrowseTopics(true);
        }
    });

    async function loadMyGrades() {
        const container = document.getElementById('studentGrades');
        if (!myGroups.length) {
            container.innerHTML = '<div class="empty-state">Join a group to see your grades.</div>';
            return;
        }
        const cards = await Promise.all(myGroups.map(async (g) => {
            const grade = await api(`/groups/${g.group_id}/my-grade`);
            if (!grade) return '';
            return `
                <div class="card">
                    <strong>${grade.group}</strong>
                    <div class="muted">Participation: ${Number(grade.participation_total).toFixed(2)} · Quizzes: ${Number(grade.quiz_total).toFixed(2)}</div>
                    <div><strong>Overall total: ${Number(grade.overall_total).toFixed(2)}</strong></div>
                </div>
            `;
        }));
        container.innerHTML = cards.join('') || '<div class="empty-state">No grades recorded yet.</div>';
    }

    let myAttemptsByQuiz = {};

    async function loadStudentQuizzes() {
        const container = document.getElementById('studentQuizzes');
        const attempts = await api('/me/quiz-attempts') || [];
        myAttemptsByQuiz = {};
        attempts.forEach(a => { myAttemptsByQuiz[a.quiz_id] = a; });

        const quizzes = await api('/me/quizzes') || [];

        container.innerHTML = quizzes.map(q => {
            const groupName = q.group?.name ?? 'Unknown group';
            const attempt = myAttemptsByQuiz[q.quiz_id];
            let action;
            if (attempt && attempt.submitted_at) {
                action = `<span class="muted">Submitted — your score: <strong>${attempt.score}</strong></span>`;
            } else if (q.status === 'Open') {
                action = `<a class="btn" href="/quizzes/${q.quiz_id}" style="padding: 6px 12px; font-size: 13px;">${attempt ? 'Resume quiz' : 'Take quiz'}</a>`;
            } else {
                action = '<span class="muted">Quiz closed — no attempt submitted.</span>';
            }
            return `
                <div class="card">
                    <strong>${q.title}</strong> <span class="muted">(${groupName})</span>
                    <div class="muted">Status: ${q.status}</div>
                    <div style="margin-top: 8px;">${action}</div>
                </div>
            `;
        }).join('') || '<div class="empty-state">No published quizzes right now.</div>';
    }

    // ---- Recommendations: card format (title + category + post count),
    // plus the ML relevance_score (0-1, from TopicRecommendation /
    // RecommendationService) surfaced as a "% match" badge + bar. ----
    async function loadRecommendations() {
        const recs = await api('/recommendations') || [];
        document.getElementById('recommendations').innerHTML = recs.map(r => {
            const pct = Math.round(Number(r.relevance_score ?? 0) * 100);
            return `
                <div class="card">
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:10px;">
                        <strong><a href="/topics/${r.topic.topic_id}">${r.topic.title}</a></strong>
                        <span class="badge" style="flex-shrink:0; background:var(--accent); color:#fff; font-size:11px;">${pct}% match</span>
                    </div>
                    <div class="muted">${r.topic.category ?? 'General'} · ${r.topic.posts_count ?? 0} posts</div>
                    <div style="margin-top:6px; height:5px; border-radius:3px; background:#e5e7eb; overflow:hidden;">
                        <div style="height:100%; width:${pct}%; background:var(--accent);"></div>
                    </div>
                </div>
            `;
        }).join('') || '<div class="empty-state">No recommendations yet.</div>';
    }

    async function loadNotifications() {
        const data = await api('/notifications');
        const notifications = (data && (data.data || data)) || [];
        document.getElementById('notifications').innerHTML = notifications.map(n => `
            <div style="margin-bottom: 4px;"><strong>${n.type}</strong>: ${n.message}</div>
        `).join('') || '<div class="empty-state">No notifications yet.</div>';
    }

    async function init() {
        initDashSidebar(document, 'panel-groups');
        await loadWelcome();
        await loadGroups();
        loadMyGrades();
        loadStudentQuizzes();
        loadRecommendations();
        loadNotifications();
    }

    init();
</script>
@endsection