@extends('layouts.app')

@section('title', 'Lecturer Dashboard')

@section('content')
<style>
    /* ---------- Groups panel: single drill-down view (groups -> topics -> posts) ---------- */
    #groupsBrowserContent { margin-top: 12px; }

    /* Match design from reference screenshot */
    .groups-view-container {
        font-family: inherit;
    }
    .groups-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .groups-header h2 {
        font-size: 26px;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        font-family: serif;
    }
    .groups-header .btn-create-group {
        background-color: #1b5e43;
        color: #ffffff;
        border: none;
        border-radius: 6px;
        padding: 8px 16px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: background-color 0.2s;
    }
    .groups-header .btn-create-group:hover {
        background-color: #144733;
    }

    /* ---------- Groups panel header (mirrors the student dashboard) ---------- */
    .groups-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
    .groups-header h2 { margin: 0; }
    .groups-list { display: flex; flex-direction: column; }

    
    .groups-list {
        display: flex;
        flex-direction: column;
    }
    .group-entry {
        border-bottom: 1px solid #e2e8f0;
    }
    .group-entry:last-child {
        border-bottom: none;
    }
    .group-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 12px;
        cursor: pointer;
        transition: background-color 0.15s;
    }
    .group-item:hover {
        background-color: #f8fafc;
    }
    .group-info {
        flex: 1;
    }
    .group-title-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 4px;
    }
    .group-title-row strong {
        font-size: 17px;
        font-weight: 700;
        color: #0f172a;
        font-family: serif;
    }
    .owner-badge {
        background-color: #dbeafe;
        color: #1d4ed8;
        font-size: 11px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .group-subtext {
        color: #64748b;
        font-size: 13.5px;
    }

    .group-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .status-joined {
        color: #475569;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.5px;
        padding: 4px 8px;
    }
    .status-joined-bg {
        background-color: #f1f5f9;
        border-radius: 12px;
        padding: 3px 10px;
    }
    .btn-green-action {
        background-color: #1b5e43;
        color: #ffffff;
        border: none;
        border-radius: 16px;
        padding: 6px 18px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .btn-green-action:hover {
        background-color: #144733;
    }

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

    .topic-item {
        display: flex; align-items: center; justify-content: space-between; gap: 10px;
        padding: 12px 4px; border-bottom: 1px solid var(--line); cursor: pointer;
    }
    .topic-item:hover { background: #eef2f1; }
    .topic-item .group-info { min-width: 0; }
    .topic-item strong { display: block; font-size: 15px; }
    .topic-item .muted { font-size: 12.5px; }

    /* ---------- Group members toggle ---------- */
    .members-toggle-row { padding: 0 4px 10px; }
    .members-toggle-row .members-toggle-link {
        color: var(--accent); font-size: 12.5px; font-weight: 600; cursor: pointer;
    }
    .members-toggle-row .members-toggle-link:hover { text-decoration: underline; }
    .member-names {
        display: none; flex-wrap: wrap; gap: 6px; margin-top: 8px;
    }
    .member-names.open { display: flex; }
    .member-chip {
        background: #eef2f1; color: #374151; font-size: 12px; font-weight: 500;
        padding: 4px 10px; border-radius: 12px;
    }

    /* ---------- Create-group modal (mirrors the student dashboard) ---------- */
    .create-group-modal-overlay {
        position: fixed; inset: 0; background: rgba(15, 23, 20, 0.45);
        display: none; align-items: center; justify-content: center; z-index: 1000; padding: 16px;
    }
    .create-group-modal-overlay.open { display: flex; }
    .create-group-modal-box {
        background: #fff; border-radius: var(--radius); padding: 20px;
        width: 100%; max-width: 420px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); position: relative;
    }
    .create-group-modal-box .close-modal-btn {
        position: absolute; top: 12px; right: 16px; font-size: 22px; cursor: pointer; color: #666; line-height: 1;
    }

    /* Chat thread + composer, reused from the standalone topic page so the
       inline preview here looks/feels the same. No fixed height/scrolling —
       it simply grows with the conversation. */
    /* Chat thread + composer */
    .chat-thread {
        display: flex; flex-direction: column; gap: 4px;
        background: var(--paper); border: 1px solid var(--line); border-radius: var(--radius);
        padding: 16px; min-height: 260px; flex: 1;
    }
    .msg-group { display: flex; flex-direction: column; margin: 10px 0; max-width: 78%; }
    .msg-group.mine { align-self: flex-end; align-items: flex-end; }
    .msg-group.theirs { align-self: flex-start; align-items: flex-start; }
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

    /* ---------- Exclude-from-post checklist ---------- */
    .exclusion-wrap { margin-top: 14px; }
    .exclusion-wrap .exclusion-label { font-size: 13px; font-weight: 600; color: var(--slate); display: block; margin-bottom: 6px; }
    #dashExclusionList {
        border: 1px solid var(--line); border-radius: 8px; padding: 8px 10px;
        max-height: 140px; overflow-y: auto; background: #fff;
        display: flex; flex-direction: column; gap: 2px;
    }
    #dashExclusionList label {
        display: flex; align-items: center; justify-content: flex-start !important;
        gap: 8px; font-size: 14px; cursor: pointer; padding: 6px 2px; width: 100%;
    }
    #dashExclusionList input[type="checkbox"] { flex-shrink: 0; margin: 0; width: 15px; height: 15px; }
    #dashExclusionList .exclusion-name { flex: 1; text-align: left; white-space: normal; }

    /* ---------- Icon-only download button ---------- */
    .icon-btn {
        display: inline-flex; align-items: center; justify-content: center;
        width: 34px; height: 34px; padding: 0; border-radius: 8px;
    }
    .icon-btn svg { width: 17px; height: 17px; }

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

    /* Mobile overrides */
    .topics-head { display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 12px; margin-bottom: 14px; }
    .topics-head-actions {
        display: flex; gap: 8px; flex-shrink: 0; flex-wrap: wrap; align-items: center; }
    @media (max-width: 640px) {
        .groups-header { flex-direction: column; align-items: stretch; gap: 10px; }

        .topics-head { flex-direction: column; align-items: stretch; gap: 10px; }
        .topics-head-actions { width: 100%; }
        .topics-head-actions .btn { flex: 1; }

        .msg-group { max-width: 90%; }
        .msg-group.is-reply { margin-left: 14px; max-width: calc(90% - 14px); padding-left: 10px; }

        .chat-thread { padding: 12px; min-height: 200px; }
        .composer { padding: 6px 6px 6px 12px; }

        .back-link { font-size: 12.5px; padding: 6px 10px; }
    }
</style>

<div class="dash-shell">
    <div class="dash-main">
        <!-- ================= MY GROUPS ================= -->
        <div class="dash-panel" id="panel-groups">
            
            <p class="muted">Groups you own or administer. Statistics and the gradebook are only available for groups where you're the lecturer or an active group admin.</p>

            
            <div class="card" id="groupsBrowserContent" style="margin-top: 14px;">Loading your groups…</div>
        </div>

        <div class="dash-panel" id="panel-quizzes">
            <div class="section-title"><h2 style="margin:0;">Quizzes</h2></div>

            <div class="card" style="border-left: 4px solid #2f5f6f; padding: 20px;">
                <h3>Create a new quiz</h3>

                <form id="quizConfigForm" style="display: flex; gap: 24px; margin-top: 15px; border-top: 1px solid #e2e8f0; padding-top: 20px; flex-wrap: wrap;">

                    <div class="quiz-config-side" style="flex: 1; min-width: 280px; max-width: 340px; display: flex; flex-direction: column; gap: 12px; border-right: 1px solid #e2e8f0; padding-right: 20px;">
                        <h4 style="color:#e11d48; margin:0 0 5px 0; font-size: 16px;">Quiz Configuration</h4>

                        <div>
                            <label style="font-weight: 600; font-size: 13px; display: block; margin-bottom: 4px;">Target Group:</label>
                            <select id="quizGroupId" required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; background: #fff;"></select>
                        </div>
                        <div>
                            <label style="font-weight: 600; font-size: 13px; display: block; margin-bottom: 4px;">Quiz Title:</label>
                            <input type="text" id="quizTitle" placeholder="e.g. Quiz 1" required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px;">
                        </div>
                        <div>
                            <label style="font-weight: 600; font-size: 13px; display: block; margin-bottom: 4px;">Scheduled Date:</label>
                            <input type="date" id="scheduledDate" required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px;">
                        </div>
                        <div>
                            <label style="font-weight: 600; font-size: 13px; display: block; margin-bottom: 4px;">Start Time (24h format):</label>
                            <input type="text" id="startTime" placeholder="14:30" required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px;">
                        </div>
                        <div>
                            <label style="font-weight: 600; font-size: 13px; display: block; margin-bottom: 4px;">Duration (Minutes):</label>
                            <input type="number" id="durationMinutes" placeholder="30" required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px;">
                        </div>

                        <button class="btn btn-secondary" type="button" id="addQuestionBtn" style="margin-top: 10px; width: 100%; padding: 10px; font-weight: bold;">+ Add Question</button>
                        <button class="btn" type="submit" style="background-color: #e11d48; color: white; width: 100%; padding: 10px; font-weight: bold;">Save as Draft</button>
                    </div>

                    <div class="quiz-questions-side" style="flex: 2; min-width: 400px; display: flex; flex-direction: column;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom: 10px;">
                            <h4 style="color:#475569; margin:0; font-size: 16px;">Question Matrix Workplace</h4>
                        </div>

                        <div id="questionMatrix" style="max-height: 520px; overflow-y: auto; padding-right: 8px; border: 1px dashed #cbd5e1; border-radius: 6px; padding: 12px; background: #fdfdfd;">
                            </div>
                    </div>

                </form>
            </div>

            <h3 style="margin-top: 30px;">Your quizzes</h3>
            <div id="lecturerQuizzes" class="card muted">Loading your quizzes…</div>
        </div>

        <div class="dash-panel" id="panel-criteria">
            <div class="section-title"><h2 style="margin:0;">Scoring Criteria</h2></div>
            <div class="card" style="border-left: 4px solid #16a34a;">
                <p class="muted">Define how much each activity is worth per group. A group with no criteria for an activity type earns students zero participation points for it, even if they post.</p>

                <label>Group:</label>
                <select id="criteriaGroupId" style="width: 100%; padding: 6px; margin-bottom: 10px;"></select>

                <div id="criteriaList" class="muted" style="margin-bottom: 12px;">Select a group above.</div>

                <form id="criteriaForm" style="border-top: 1px solid #e2e8f0; padding-top: 12px;">
                    <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: flex-end;">
                        <div style="flex: 2; min-width: 160px;">
                            <label>Description</label>
                            <input type="text" id="criteriaDescription" placeholder="e.g. Discussion post" required style="width: 100%; padding: 6px;">
                        </div>
                        <div style="flex: 1; min-width: 140px;">
                            <label>Activity type</label>
                            <select id="criteriaActivityType" style="width: 100%; padding: 6px;">
                                <option value="post">Post</option>
                                <option value="reply">Reply</option>
                                <option value="quiz_attempt">Quiz attempt</option>
                                <option value="topic_creation">Topic creation</option>
                            </select>
                        </div>
                        <div style="width: 100px;">
                            <label>Max marks</label>
                            <input type="number" id="criteriaMaxMarks" min="0" step="0.5" value="10" required style="width: 100%; padding: 6px;">
                        </div>
                        <button class="btn" type="submit" style="padding: 8px 16px;">Add rule</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="dash-panel" id="panel-notifications">
            <div class="section-title"><h2 style="margin:0;">Notifications</h2></div>
            <div id="notifications" class="card muted">Loading notifications…</div>
        </div>
    </div>
</div>

<!-- Forward Modal -->
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

<!-- ================= Create Group modal (mirrors the student dashboard) ================= -->
<div class="create-group-modal-overlay" id="createGroupModalOverlay" onclick="closeCreateGroupModalOnOuterClick(event)">
    <div class="create-group-modal-box" onclick="event.stopPropagation();">
        <span class="close-modal-btn" onclick="closeCreateGroupModal()">&times;</span>
        <h3 style="margin-top:0; margin-bottom:16px;">Create a new group</h3>
        <form id="createGroupForm">
            <div style="margin-bottom: 12px;">
                <label style="display:block; margin-bottom:6px; font-weight:600; font-size:14px;">Group Name</label>
                <input type="text" id="groupName" placeholder="Group name (e.g. CS301 Databases)" required style="width:100%; padding:8px; box-sizing:border-box;">
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display:block; margin-bottom:6px; font-weight:600; font-size:14px;">Description</label>
                <textarea id="groupDescription" placeholder="What is this group for?" rows="3" style="width:100%; padding:8px; box-sizing:border-box; resize:vertical;"></textarea>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:8px;">
                <button type="button" class="btn btn-secondary" onclick="closeCreateGroupModal()" style="padding: 8px 16px;">Cancel</button>
                <button type="submit" class="btn" style="padding: 8px 16px;">Create group</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    if (!localStorage.getItem('sdf_token')) { window.location.href = '/'; }

    let myGroups = [];

    function handleIncomingShareLink() {
    const params = new URLSearchParams(window.location.search);
    const groupId = params.get('group_id');
    const topicId = params.get('topic_id');
    const postId = params.get('post_id');

    if (!groupId || !topicId) return; // not a shared-link visit, nothing to do

    const groupName = params.get('group_name') ? decodeURIComponent(params.get('group_name')) : '';
    const topicTitle = params.get('topic_title') ? decodeURIComponent(params.get('topic_title')) : '';

    // Drive the same functions a real click on the group/topic would call.
    openGroupTopics(Number(groupId), groupName);
    openTopicPosts(Number(topicId), topicTitle);

    // Once posts load, scroll to and briefly highlight the specific post.
    if (postId) {
        setTimeout(() => {
            const el = document.getElementById('post-' + postId);
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                el.style.outline = '2px solid var(--accent)';
                setTimeout(() => { el.style.outline = ''; }, 2000);
            }
        }, 800); // give loadBrowsePosts() time to finish its API call + render
    }

    // Clean the URL so refreshing doesn't re-trigger this / doesn't look odd.
    window.history.replaceState({}, '', '/dashboard/student');
}


    async function loadWelcome() {
        const me = await loadCurrentUser();
        if (!me) return;
        if (window.CURRENT_ROLE === 'student') {
            window.location.replace('/dashboard/student' + window.location.search);
            return;
        }
        if (window.CURRENT_ROLE === 'administrator') {
            window.location.replace('/dashboard/admin' + window.location.search);
            return;
        }
    }

    let browseView = 'groups'; // 'groups' | 'topics' | 'posts'
    let activeBrowseGroupId = null;
    let activeBrowseGroupName = '';
    let activeBrowseTopicId = null;
    let activeBrowseTopicTitle = '';
    let currentTopicMessages = []; // index -> {author, content, postId, isReply, flagged}, used by Forward + Flag
     /**********adddedd**************** */
    let groupMembersExpanded = false;
    let allGroupMembers = []; 

  let browseTopicsPage = 1;
    let browseTopicsSearch = '';
    let browseTopicsCategory = '';
   

     
    function timeOnly(dt) {
        if (!dt) return '';
        return new Date(dt).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    function escAttr(str) {
        return (str || '').replace(/'/g, "\\'");
    }

    function canViewGroupStats(groupId) {
        if (!groupId || !window.CURRENT_USER) return false;
        const g = myGroups.find(x => x.group_id === groupId);
        return !!(g && (g.admin_id === window.CURRENT_USER.user_id || g.is_group_admin));
    }
    window.canViewGroupStats = canViewGroupStats;

    function isGroupOwner(groupId) {
        if (!groupId || !window.CURRENT_USER) return false;
        const g = myGroups.find(x => x.group_id === groupId);
        return !!(g && g.admin_id === window.CURRENT_USER.user_id);
    }
    window.isGroupOwner = isGroupOwner;

    function canFlagInGroup(groupId) {
        const g = myGroups.find(x => x.group_id === groupId);
        return !!g;
    }
    window.canFlagInGroup = canFlagInGroup;

    window.currentSubscriptionId = null;
    window._echoWaitAttempts = 0;

    window.subscribeToTopic = function (topicId) {
        if (!topicId) return;

        if (typeof window.Echo === 'undefined') {
            window._echoWaitAttempts++;
            if (window._echoWaitAttempts > 20) {
                console.warn("Laravel Echo never loaded after 10s - giving up on live updates for this topic. Check your Echo/Reverb setup.");
                return;
            }
            console.warn("Laravel Echo is not loaded yet! Retrying in 500ms...");
            setTimeout(() => window.subscribeToTopic(topicId), 500);
            return;
        }
        window._echoWaitAttempts = 0;

        if (window.currentSubscriptionId === topicId) {
            return;
        }

        if (window.currentSubscriptionId && window.currentSubscriptionId !== topicId) {
            console.log("Leaving old channel: topic." + window.currentSubscriptionId);
            window.Echo.leave(`topic.${window.currentSubscriptionId}`);
        }

        window.currentSubscriptionId = topicId;
        console.log("Joining Presence Channel: topic." + topicId);

        window.Echo.join(`topic.${topicId}`)
            .here((users) => {
                console.log("Connected to Presence Channel! Users online:", users);
            })
            .joining((user) => {
                const joinerName = user.full_name || user.name || "A user";
                console.log(joinerName + " joined the chat");
            })
            .leaving((user) => {
                const leaverName = user.full_name || user.name || "A user";
                console.log(leaverName + " left the chat");
            })
            .listen('.MessageBroadcast', (e) => {
                if (activeBrowseTopicId !== e.topicId) return;

                const container = document.getElementById('dashPosts');
                if (!container) return;

                const emptyMsg = container.querySelector('.muted');
                if (emptyMsg && emptyMsg.textContent.includes('No messages yet')) {
                    container.innerHTML = '';
                }

                const myId = window.CURRENT_USER ? window.CURRENT_USER.user_id : null;

                const excludedIds = (e.excluded_user_ids || e.reply?.excluded_user_ids || []).map(Number);
                if (myId !== null && excludedIds.includes(Number(myId))) return;

                const mine = e.reply.author_id === myId;
                const side = mine ? 'mine' : 'theirs';
                const authorName = e.reply.author ? (e.reply.author.full_name || e.reply.author.name) : 'User';
                const timeStr = timeOnly(e.reply.posted_at || e.reply.created_at);

                const newPostHtml = renderMsgGroup(side, authorName, e.reply.content, timeStr, false, e.reply.post_id ?? e.reply.reply_id, e.reply.is_flagged);

                container.insertAdjacentHTML('beforeend', newPostHtml);
                container.scrollTop = container.scrollHeight;
            })
            .error((error) => {
                console.error("Presence channel subscription error:", error);
            });
    };
 
    async function loadWelcome() {
        const me = await loadCurrentUser();
        if (!me) return;
        if (window.CURRENT_ROLE === 'student') {
            window.location.replace('/dashboard/student' + window.location.search);
            return;
        }
        if (window.CURRENT_ROLE === 'administrator') {
            window.location.replace('/dashboard/admin' + window.location.search);
            return;
        }
    }

    async function loadGroups() {
        const data = await api('/groups');
        const groups = (data && (data.data || data)) || [];
        myGroups = groups;

        renderGroupsBrowser();

        ['quizGroupId', 'criteriaGroupId'].forEach(id => {
            const dropdown = document.getElementById(id);
            if (!dropdown) return;
            if (groups.length > 0) {
                dropdown.innerHTML = '<option value="">Select a group</option>' +
                    groups.map(g => `<option value="${g.group_id}">${g.name}</option>`).join('');
            } else {
                dropdown.innerHTML = '<option value="" disabled>Create a group first</option>';
            }
        });
    }

    async function joinGroupInline(event, groupId) {
        event.stopPropagation();
        const res = await api(`/groups/${groupId}/join`, { method: 'POST' });
        if (res && (res.message || res.status === 'success' || !res.error)) {
            await loadGroups();
        } else {
            alert(res?.error || res?.message || 'Could not join this group.');
        }
    }
    window.joinGroupInline = joinGroupInline;

    function showNotMemberNotice(groupId) {
        const notice = document.getElementById(`notMemberNotice-${groupId}`);
        if (notice) {
            notice.style.display = notice.style.display === 'none' ? 'block' : 'none';
        }
    }
    window.showNotMemberNotice = showNotMemberNotice;

    function renderGroupsBrowser() {
        const el = document.getElementById('groupsBrowserContent');
        if (browseView === 'topics') {
            el.innerHTML = topicsViewHtml();
            loadBrowseTopics();
            loadBrowseCategories();
        } else if (browseView === 'posts') {
            el.innerHTML = postsViewHtml();
            loadBrowsePosts();
            loadGroupMembersForExclusion();
        } else {
            el.innerHTML = groupsViewHtml();
        }
    }

    function groupsViewHtml() {
        const rows = myGroups.map(g => {
            const joined = g.is_member || g.is_group_admin || g.admin_id === (window.CURRENT_USER ? window.CURRENT_USER.user_id : null);
            const isBanned = g.is_banned || g.banned;
            const isOwner = g.admin_id === (window.CURRENT_USER ? window.CURRENT_USER.user_id : null);

            const clickHandler = isBanned
                ? `alert('You are blacklisted/banned from this group.')`
                : (joined
                    ? `openGroupTopics(${g.group_id}, '${escAttr(g.name)}')`
                    : `showNotMemberNotice(${g.group_id})`);

            let actionButtonsHtml = '';
            if (isOwner) {
                actionButtonsHtml = `
                    <button type="button" class="btn-green-action" onclick="event.stopPropagation(); window.location.href='/groups/${g.group_id}/statistics'">Statistics</button>
                    <button type="button" class="btn-green-action" onclick="event.stopPropagation(); window.location.href='/groups/${g.group_id}/gradebook'">Gradebook</button>
                `;
            } else if (joined) {
                actionButtonsHtml = `<span class="status-joined status-joined-bg">JOINED</span>`;
            } else {
                actionButtonsHtml = `<button type="button" class="btn-green-action" onclick="joinGroupInline(event, ${g.group_id})">Join</button>`;
            }

            return `
                <div class="group-entry">
                    <div class="group-item" data-group-id="${g.group_id}" onclick="${clickHandler}">
                        <div class="group-info">
                            <div class="group-title-row">
                                <strong>${g.name}</strong>
                                ${isOwner ? '<span class="owner-badge">OWNER</span>' : ''}
                                ${isBanned ? '<span class="badge" style="background:#dc2626; color:#fff; font-size:11px;">Banned</span>' : ''}
                            </div>
                            <div class="group-subtext">
                                ${g.description ?? ''} ${g.description ? '· ' : ''}${g.members_count ?? 0} members · ${g.topics_count ?? 0} topics
                            </div>
                            ${!isBanned && !joined ? `
                                <div id="notMemberNotice-${g.group_id}" style="display:none; color:#b45309; font-weight:600; margin-top:4px; font-size:12.5px;">
                                    You're not a member of this group yet — join to view topics.
                                </div>
                            ` : ''}
                        </div>
                        <div class="group-actions">
                            ${actionButtonsHtml}
                        </div>
                    </div>
                </div>
            `;
        }).join('') || '<div class="empty-state">You are not in any groups yet. Create one below.</div>';

        const headerHtml = `
            <div class="groups-header">
                <h2>Groups</h2>
                <button class="btn-create-group" type="button" onclick="openCreateGroupModal(event)">+ Create Group</button>
            </div>
        `;

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

    function openCreateGroupModal(event) {
        if (event) event.stopPropagation();
        const modal = document.getElementById('createGroupModal');
        if (modal) modal.style.display = 'flex';
    }
    window.openCreateGroupModal = openCreateGroupModal;

    function closeCreateGroupModal() {
        const modal = document.getElementById('createGroupModal');
        const form = document.getElementById('createGroupForm');
        if (modal) modal.style.display = 'none';
        if (form) form.reset();
    }
    window.closeCreateGroupModal = closeCreateGroupModal;

    function closeCreateGroupModalOnOuterClick(event) {
        const modal = document.getElementById('createGroupModal');
        if (event.target === modal) closeCreateGroupModal();
    }
    window.closeCreateGroupModalOnOuterClick = closeCreateGroupModalOnOuterClick;

    function topicsViewHtml() {
        const statsShortcut = canViewGroupStats(activeBrowseGroupId)
            ? `<a class="btn secondary" href="/groups/${activeBrowseGroupId}/statistics">Statistics</a>
               <a class="btn secondary" href="/groups/${activeBrowseGroupId}/gradebook">Gradebook</a>`
            : '';

        return `
        <a class="back-link" onclick="browseGoBack()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"/>
                <polyline points="12 19 5 12 12 5"/>
            </svg>
            Back to groups
        </a>

        <div class="topics-head">
            <div>
                <h3>${activeBrowseGroupName}</h3>
                <p class="muted">Topics in this group</p>
            </div>
            <div class="topics-head-actions">
                <button class="btn secondary" type="button" onclick="openGroupMembersModal()" title="View members" style="display:flex; align-items:center; justify-content:center; gap:6px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Members
                </button>
                <button class="btn" type="button" onclick="openCreateTopicModal()">+ New Topic</button>
                ${statsShortcut}
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
        return `
            <a class="back-link" onclick="browseGoBack()"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Back to topics
            </a>
            <div style="display:flex; align-items:center; justify-content:space-between; margin: 12px 0 14px;">
                <h3 style="margin:0;">${activeBrowseTopicTitle}</h3>
                <button class="btn secondary icon-btn" type="button" onclick="exportDashTopicPdf()" title="Download as PDF" aria-label="Download as PDF">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                </button>
            </div>
            <div class="chat-thread" id="dashPosts"><div class="muted">Loading messages…</div></div>
            <div class="exclusion-wrap">
                <span class="exclusion-label">Exclude these members from seeing your next post</span>
                <div id="dashExclusionList">Loading members…</div>
            </div>
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
        groupMembersExpanded = false;

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
        activeBrowseGroupId = groupId;
        activeBrowseGroupName = groupName;
        activeBrowseTopicId = null;

        browseTopicsPage = 1;
        browseTopicsSearch = '';
        browseTopicsCategory = '';
        browseView = 'topics';
        renderGroupsBrowser();
    }
    window.openGroupTopics = openGroupTopics;

    function openTopicPosts(topicId, title) {
        activeBrowseTopicId = topicId;
        activeBrowseTopicTitle = title;
        browseView = 'posts';
        renderGroupsBrowser();
        if (typeof window.subscribeToTopic === 'function') {
            window.subscribeToTopic(topicId);
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

    async function loadBrowseTopics(reset = true) {
        if (!activeBrowseGroupId) return;
        const listEl = document.getElementById('groupTopicsList');
        if (!listEl) return;

        if (reset) {
            browseTopicsPage = 1;
            listEl.innerHTML = 'Loading topics…';
        }

        const params = new URLSearchParams({ page: browseTopicsPage });
        if (browseTopicsSearch) params.set('search', browseTopicsSearch);
        if (browseTopicsCategory) params.set('category', browseTopicsCategory);

        const data = await api(`/groups/${activeBrowseGroupId}/topics?${params.toString()}`);

        const errMsg = data && (data.error || data.message);
        const isBanMsg = errMsg && (
            errMsg.toLowerCase().includes('ban') || errMsg.toLowerCase().includes('blacklist')
        );
        if (isBanMsg) {
            listEl.innerHTML = `<div class="empty-state" style="color:#dc2626; font-weight:bold;">${errMsg}</div>`;
            const moreBtn = document.getElementById('browseLoadMoreBtn');
            if (moreBtn) moreBtn.style.display = 'none';
            return;
        }
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

            return renderMsgGroup(side, authorName, p.content, timeOnly(p.posted_at || p.created_at), false, p.post_id, p.is_flagged) + repliesHtml;
        }).join('') || '<div class="muted">No messages yet in this topic — start the discussion below.</div>';

        container.scrollTop = container.scrollHeight;
    }

    function renderMsgGroup(side, authorName, content, time, isReply, postId, flagged) {
        const msgIndex = currentTopicMessages.length;
        currentTopicMessages.push({ author: authorName, content, postId, isReply: !!isReply, flagged: !!flagged });

        const canFlag = canFlagInGroup(activeBrowseGroupId);
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
        if (!canFlagInGroup(activeBrowseGroupId)) return;

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

        if (response && response.message) {
            alert(response.message);
        }

        msg.flagged = !msg.flagged;

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

    async function loadGroupMembersForExclusion() {
        const listEl = document.getElementById('dashExclusionList');
        if (!listEl || !activeBrowseGroupId) return;

        const data = await api(`/groups/${activeBrowseGroupId}/members`);
        const members = (data && (data.data || data)) || [];
        const myId = window.CURRENT_USER ? window.CURRENT_USER.user_id : null;

        listEl.innerHTML = members
            .filter(m => (m.user_id ?? m.id) !== myId)
            .map(m => `
                <label>
                    <input type="checkbox" value="${m.user_id ?? m.id}">
                    <span class="exclusion-name">${m.full_name || m.name}</span>
                </label>
            `).join('') || '<div class="muted" style="font-size:13px;">No other members in this group.</div>';
    }
    window.loadGroupMembersForExclusion = loadGroupMembersForExclusion;

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

    let forwardMessageIndex = null;
    let forwardMode = 'internal';

    function openForwardModal(msgIndex) {
        const msg = currentTopicMessages[msgIndex];
        if (!msg) return;
        forwardMessageIndex = msgIndex;

        document.getElementById('forwardPreview').textContent = `${msg.author}: ${msg.content}`;

        setForwardMode('internal');

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

    function setForwardMode(mode) {
        forwardMode = mode;
        const badge = document.getElementById('modalModeBadge');
        const tabInternal = document.getElementById('tabInternal');
        const tabExternal = document.getElementById('tabExternal');
        const internalFields = document.getElementById('internalForwardFields');
        const externalFields = document.getElementById('externalForwardFields');

        if (mode === 'external') {
            badge.textContent = 'External';
            badge.style.background = '#10b981';
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

    async function shareToPlatform(platform) {
    if (forwardMessageIndex === null) return;
    const msg = currentTopicMessages[forwardMessageIndex];

    const postId = msg.postId || activeBrowseTopicId;
    const endpoint = msg.isReply ? `/replies/${postId}/share` : `/posts/${postId}/share`;

    try {
        const response = await api(endpoint, {
            method: 'POST',
            body: { platform: platform }
        });

        if (response && response.error) {
            alert(response.error);
            return;
        }
        if (!response || !response.shared_url) {
            alert('Could not generate a share link for this post. Please try again.');
            return;
        }

        const shareUrl = response.shared_url;
        const textToShare = `Check out this post on the Student Discussion Forum:\n"${msg.content.substring(0, 100)}..."\nRead more here: ${shareUrl}`;

            let targetUrl = '';
            switch(platform) {
                case 'WhatsApp':
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

    document.getElementById('groupsBrowserContent').addEventListener('submit', async (e) => {
        if (e.target && e.target.id === 'createGroupForm') {
            e.preventDefault();
            const nameInput = document.getElementById('groupName');
            const descInput = document.getElementById('groupDescription');
            const response = await api('/groups', {
                method: 'POST',
                body: { name: nameInput.value, description: descInput.value },
            });
            if (response && response.message && !response.group_id) {
                alert(response.message);
                return;
            }
            closeCreateGroupModal();
            await loadGroups();
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
            loadBrowseTopics(true);
            loadBrowseCategories();
        } else if (e.target && e.target.id === 'dashComposerForm') {
            e.preventDefault();
            if (!activeBrowseTopicId) return;
            const textarea = document.getElementById('dashComposerInput');
            const excludeCheckboxes = Array.from(document.querySelectorAll('#dashExclusionList input[type="checkbox"]:checked'));
            const excludeIds = excludeCheckboxes
                .map(cb => Number(cb.value))
                .filter(id => !Number.isNaN(id));

            const res = await api(`/topics/${activeBrowseTopicId}/posts`, {
                method: 'POST',
                body: { content: textarea.value, exclude_user_ids: excludeIds }
            });

            if (!res || res.errors || res.error) {
                alert('Your message could not be posted. Please try again.');
                return;
            }

            textarea.value = '';
            textarea.style.height = 'auto';
            excludeCheckboxes.forEach(cb => { cb.checked = false; });
            loadBrowsePosts();
        }
    });

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

    const toggleBtn = document.getElementById('toggleQuizFormBtn');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const form = document.getElementById('quizConfigForm');
            form.style.display = form.style.display === 'none' ? 'flex' : 'none';
        });
    }

    let questionRowCount = 0;

    function escHtmlAttr(str) {
        return (str ?? '').toString().replace(/&/g, '&amp;').replace(/"/g, '&quot;');
    }

    function addQuestionRow(existing) {
        questionRowCount++;
        const wrapper = document.createElement('div');
        wrapper.className = 'question-row';
        wrapper.id = `qrow-${questionRowCount}`;
        wrapper.style.cssText = 'background:#f8fafc; padding:10px; border-radius:4px; margin-top:10px; position:relative;';
        wrapper.innerHTML = `
            <button type="button" class="removeQuestionBtn" style="position:absolute; top:8px; right:8px; background:none; border:none; color:#e11d48; cursor:pointer; font-weight:bold;">✕ remove</button>
            <div class="muted" style="margin-bottom:6px;">Question ${questionRowCount}</div>
            <input type="text" class="qText" placeholder="Enter question..." required value="${escHtmlAttr(existing?.question_text)}" style="width: 100%; margin-bottom: 8px; padding: 6px;">
            <input type="text" class="qOptA" placeholder="Option A" required value="${escHtmlAttr(existing?.option_a)}" style="width: 100%; margin-bottom: 4px; padding: 6px;">
            <input type="text" class="qOptB" placeholder="Option B" required value="${escHtmlAttr(existing?.option_b)}" style="width: 100%; margin-bottom: 4px; padding: 6px;">
            <input type="text" class="qOptC" placeholder="Option C" required value="${escHtmlAttr(existing?.option_c)}" style="width: 100%; margin-bottom: 4px; padding: 6px;">
            <input type="text" class="qOptD" placeholder="Option D" required value="${escHtmlAttr(existing?.option_d)}" style="width: 100%; margin-bottom: 8px; padding: 6px;">
            <label>Correct Answer Option:</label>
            <select class="qCorrect"><option>A</option><option>B</option><option>C</option><option>D</option></select>
            <label style="margin-left:10px;">Marks:</label>
            <input type="number" class="qMarks" value="${existing?.marks ?? 1}" min="1" style="width:60px; padding:4px;">
        `;
        document.getElementById('questionMatrix').appendChild(wrapper);

        if (existing?.correct_option) {
            wrapper.querySelector('.qCorrect').value = existing.correct_option;
        }

        const workspace = document.getElementById('questionMatrix');
        workspace.scrollTop = workspace.scrollHeight;

        wrapper.querySelector('.removeQuestionBtn').addEventListener('click', () => {
            if (document.querySelectorAll('.question-row').length > 1) {
                wrapper.remove();
            } else {
                alert('A quiz needs at least one question.');
            }
        });
    }

    document.getElementById('addQuestionBtn').addEventListener('click', addQuestionRow);

    function resetQuestionMatrix() {
        document.getElementById('questionMatrix').innerHTML = '';
        questionRowCount = 0;
        addQuestionRow();
    }
    resetQuestionMatrix();

    let editingQuizId = null;

    function exitQuizEditMode() {
        editingQuizId = null;
        const submitBtn = document.querySelector('#quizConfigForm button[type="submit"]');
        if (submitBtn) submitBtn.textContent = 'Save as Draft';
        const cancelBtn = document.getElementById('cancelEditQuizBtn');
        if (cancelBtn) cancelBtn.style.display = 'none';
    }

    async function startEditQuiz(quizId) {
        const quiz = await api(`/quizzes/${quizId}`);
        if (!quiz) { alert('Could not load this quiz for review.'); return; }

        editingQuizId = quizId;

        const groupSelect = document.getElementById('quizGroupId');
        const groupIdForQuiz = quiz.group_id ?? quiz.group?.group_id;
        if (groupIdForQuiz && groupSelect) groupSelect.value = groupIdForQuiz;

        document.getElementById('quizTitle').value = quiz.title ?? '';
        document.getElementById('scheduledDate').value = quiz.configuration?.scheduled_date ?? '';
        document.getElementById('startTime').value = quiz.configuration?.start_time ?? '';
        document.getElementById('durationMinutes').value = quiz.configuration?.duration_minutes ?? '';

        document.getElementById('questionMatrix').innerHTML = '';
        questionRowCount = 0;
        const questions = quiz.questions || [];
        if (questions.length) {
            questions.forEach(q => addQuestionRow(q));
        } else {
            addQuestionRow();
        }

        const submitBtn = document.querySelector('#quizConfigForm button[type="submit"]');
        if (submitBtn) {
            submitBtn.textContent = 'Update Quiz';
            let cancelBtn = document.getElementById('cancelEditQuizBtn');
            if (!cancelBtn) {
                cancelBtn = document.createElement('button');
                cancelBtn.type = 'button';
                cancelBtn.id = 'cancelEditQuizBtn';
                cancelBtn.className = 'btn btn-secondary';
                cancelBtn.style.cssText = 'width:100%; padding:10px; margin-top:8px;';
                cancelBtn.textContent = 'Cancel review / start new quiz';
                cancelBtn.addEventListener('click', () => {
                    exitQuizEditMode();
                    document.getElementById('quizConfigForm').reset();
                    resetQuestionMatrix();
                });
                submitBtn.insertAdjacentElement('afterend', cancelBtn);
            }
            cancelBtn.style.display = 'block';
        }

        const form = document.getElementById('quizConfigForm');
        form.style.display = 'flex';
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    window.startEditQuiz = startEditQuiz;

    document.getElementById('quizConfigForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const groupId = document.getElementById('quizGroupId').value;
        const questions = Array.from(document.querySelectorAll('.question-row')).map(row => ({
            question_text: row.querySelector('.qText').value,
            option_a: row.querySelector('.qOptA').value,
            option_b: row.querySelector('.qOptB').value,
            option_c: row.querySelector('.qOptC').value,
            option_d: row.querySelector('.qOptD').value,
            correct_option: row.querySelector('.qCorrect').value,
            marks: parseInt(row.querySelector('.qMarks').value) || 1,
        }));

        const payload = {
            title: document.getElementById('quizTitle').value,
            scheduled_date: document.getElementById('scheduledDate').value,
            start_time: document.getElementById('startTime').value,
            duration_minutes: parseInt(document.getElementById('durationMinutes').value),
            questions,
        };

        if (editingQuizId) {
            const res = await api(`/quizzes/${editingQuizId}`, { method: 'PUT', body: payload });
            if (res && !res.errors) {
                alert('Quiz updated. Review it below, then hit Publish when you\'re ready to push it live.');
                e.target.reset();
                resetQuestionMatrix();
                exitQuizEditMode();
                document.getElementById('quizConfigForm').style.display = 'none';
                loadLecturerQuizzes();
            } else {
                alert('Failed to save changes. Check that every question row is filled in and start time is HH:MM (e.g. 14:00).');
            }
            return;
        }

        const res = await api(`/groups/${groupId}/quizzes`, { method: 'POST', body: payload });
        if (res && !res.errors) {
            alert(`Quiz scheduled with ${questions.length} question(s). It will open automatically at the scheduled time.`);
            e.target.reset();
            resetQuestionMatrix();
            document.getElementById('quizConfigForm').style.display = 'none';
            loadLecturerQuizzes();
        } else {
            alert('Failed to save. Check that every question row is filled in and start time is HH:MM (e.g. 14:00).');
        }
    });

    async function loadLecturerQuizzes() {
        const container = document.getElementById('lecturerQuizzes');
        const quizzes = await api('/me/quizzes') || [];

        container.innerHTML = quizzes.map(q => {
            const groupName = q.group?.name ?? 'Unknown group';
            const schedule = q.configuration ? `${q.configuration.scheduled_date} at ${q.configuration.start_time}` : '';
            let actions = '';
            if (q.status === 'Scheduled') {
                actions += `<button class="btn publish-quiz-btn" type="button" data-quiz-id="${q.quiz_id}" style="padding: 6px 12px; font-size: 13px;">Publish</button>`;
                actions += `<button class="btn btn-secondary edit-quiz-btn" type="button" data-quiz-id="${q.quiz_id}" style="padding: 6px 12px; font-size: 13px; margin-left: 6px;">Review / Edit</button>`;
            } else if (q.status === 'Open') {
                actions += `<button class="btn btn-secondary close-quiz-btn" type="button" data-quiz-id="${q.quiz_id}" style="padding: 6px 12px; font-size: 13px; margin-left: 6px;">Close</button>`;
            }
            actions += `<button class="btn btn-secondary view-results-btn" type="button" data-quiz-id="${q.quiz_id}" style="padding: 6px 12px; font-size: 13px; margin-left: 6px;">View results</button>`;

            return `
                <div class="card">
                    <strong>${q.title}</strong> <span class="muted">(${groupName})</span>
                    <div class="muted">Status: ${q.status}${schedule ? ' · ' + schedule : ''}</div>
                    <div style="margin-top: 8px;">${actions}</div>
                    <div class="quiz-results" data-quiz-id="${q.quiz_id}" style="margin-top: 10px; display: none;"></div>
                </div>
            `;
        }).join('') || '<div class="empty-state">You have not created any quizzes yet.</div>';

        container.querySelectorAll('.publish-quiz-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                await api(`/quizzes/${btn.dataset.quizId}/publish`, { method: 'POST' });
                loadLecturerQuizzes();
            });
        });
        container.querySelectorAll('.edit-quiz-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                startEditQuiz(btn.dataset.quizId);
            });
        });
        container.querySelectorAll('.close-quiz-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                await api(`/quizzes/${btn.dataset.quizId}/close`, { method: 'POST' });
                loadLecturerQuizzes();
            });
        });
        container.querySelectorAll('.view-results-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const quizId = btn.dataset.quizId;
                const resultsBox = container.querySelector(`.quiz-results[data-quiz-id="${quizId}"]`);
                if (resultsBox.style.display === 'block') { resultsBox.style.display = 'none'; return; }

                const results = await api(`/quizzes/${quizId}/results`) || [];
                resultsBox.innerHTML = results.length ? `
                    <table>
                        <thead><tr><th>Student</th><th>Score</th><th>Submitted</th></tr></thead>
                        <tbody>
                            ${results.map(r => `
                                <tr>
                                    <td>${r.user?.full_name ?? 'Unknown'}</td>
                                    <td>${r.score}</td>
                                    <td>${r.submitted_at ? new Date(r.submitted_at).toLocaleString() : ''}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                ` : '<div class="empty-state">No submissions yet.</div>';
                resultsBox.style.display = 'block';
            });
        });
    }

    async function loadCriteriaList(groupId) {
        const listEl = document.getElementById('criteriaList');
        if (!groupId) { listEl.textContent = 'Select a group above.'; return; }

        listEl.textContent = 'Loading…';
        const criteria = await api(`/groups/${groupId}/scoring-criteria`) || [];
        listEl.innerHTML = criteria.length ? `
            <table>
                <thead><tr><th>Description</th><th>Activity</th><th>Max marks</th></tr></thead>
                <tbody>
                    ${criteria.map(c => `
                        <tr><td>${c.description}</td><td>${c.activity_type}</td><td>${Number(c.max_marks).toFixed(2)}</td></tr>
                    `).join('')}
                </tbody>
            </table>
        ` : '<div class="empty-state">No scoring criteria set for this group yet.</div>';
    }

    document.getElementById('criteriaGroupId').addEventListener('change', (e) => {
        loadCriteriaList(e.target.value);
    });

    document.getElementById('criteriaForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const groupId = document.getElementById('criteriaGroupId').value;
        if (!groupId) { alert('Please select a group first.'); return; }

        const payload = {
            description: document.getElementById('criteriaDescription').value,
            activity_type: document.getElementById('criteriaActivityType').value,
            max_marks: parseFloat(document.getElementById('criteriaMaxMarks').value),
        };

        const res = await api(`/groups/${groupId}/scoring-criteria`, { method: 'POST', body: payload });
        if (res && !res.errors) {
            e.target.reset();
            document.getElementById('criteriaMaxMarks').value = 10;
            loadCriteriaList(groupId);
        } else {
            alert('Failed to save scoring criteria.');
        }
    });

    let currentNotifications = [];

async function loadNotifications() {
    const data = await api('/notifications');
    currentNotifications = (data && (data.data || data)) || [];
    renderNotifications();
}

function renderNotifications() {
    document.getElementById('notifications').innerHTML = currentNotifications.map((n, i) => {
        const meta = notifIconMeta(n.type);
        return `
            <div class="notif-card${!n.is_read ? ' unread' : ''}" onclick="markOneNotificationRead(${i})">
                <div class="notif-icon ${meta.cls}">${meta.icon}</div>
                <div class="notif-body">
                    <div class="notif-title">${n.type}</div>
                    <div class="notif-message">${n.message}</div>
                    <div class="notif-time">${relativeTime(n.created_at)}</div>
                </div>
                ${!n.is_read ? '<span class="notif-dot"></span>' : ''}
            </div>
        `;
    }).join('') || '<div class="empty-state">No notifications yet.</div>';
}

async function markOneNotificationRead(index) {
    const n = currentNotifications[index];
    if (!n || n.is_read) return;
    await api(`/notifications/${n.notification_id}/read`, { method: 'PATCH', body: {} });
    n.is_read = true;
    renderNotifications();
    refreshNotifBadge();
}
window.markOneNotificationRead = markOneNotificationRead;

window.prependLiveNotification = function (e) {
    currentNotifications.unshift({
        notification_id: e.notification_id,
        type: e.type,
        message: e.message,
        created_at: e.created_at,
        is_read: false,
    });
    renderNotifications();
};
    async function init() {
        initDashSidebar(document, 'panel-groups');
        await loadWelcome();
        await loadGroups();
        loadLecturerQuizzes();
        loadNotifications();
        handleIncomingShareLink(); // <-- add this, after loadGroups() so myGroups is populated

    }

    init();
</script>
@endsection
