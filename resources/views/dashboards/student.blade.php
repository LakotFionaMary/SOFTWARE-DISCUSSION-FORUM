@extends('layout')
@section('title', 'Discussions — SDF Platform')

@section('page-content')

<div class="discussion-shell">

    {{-- ══ LEFT PANEL: Topics ══ --}}
    <aside class="topics-panel">

        {{-- Panel header --}}
        <div class="panel-header">
            <h2 class="panel-title">Topics</h2>
            <div class="panel-header-actions">
                <button class="btn-icon" title="New topic" onclick="openNewTopicModal()">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                </button>
            </div>
        </div>

        {{-- Search --}}
        <div class="search-wrap">
            <svg class="search-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
            <input type="text" id="topicSearch" placeholder="Search topics…" class="search-input" oninput="filterTopics(this.value)">
        </div>

        {{-- Filter chips --}}
        <div class="filter-chips">
            <button class="chip active" onclick="setFilter('all',this)">All</button>
            <button class="chip" onclick="setFilter('mine',this)">Mine</button>
            <button class="chip" onclick="setFilter('unanswered',this)">❓ Unanswered</button>
            <button class="chip" onclick="setFilter('hot',this)">🔥 Hot</button>
        </div>

        {{-- ML Recommended label --}}
        <div class="recommend-label">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
            Recommended for you <span class="ml-chip">ML</span>
        </div>

        {{-- Topic list --}}
        <ul class="topic-list" id="topicList">
            <li class="topic-item active" data-cat="hot" data-title="System architecture Q&A" data-id="1" onclick="selectTopic(this)">
                <div class="topic-dot emerald"></div>
                <div class="topic-body">
                    <span class="topic-name">System architecture Q&amp;A</span>
                    <span class="topic-meta">Group 4 · CIT 4203 · <strong>12 replies</strong></span>
                </div>
                <span class="topic-badge hot">Hot</span>
            </li>
            <li class="topic-item" data-cat="unanswered" data-title="Laravel-JavaFX sync" data-id="2" onclick="selectTopic(this)">
                <div class="topic-dot amber pulse-dot"></div>
                <div class="topic-body">
                    <span class="topic-name">Laravel-JavaFX sync</span>
                    <span class="topic-meta">Group 2 · CIT 4201 · <strong class="unanswered-text">0 replies</strong></span>
                </div>
                <span class="topic-badge unanswered" title="No replies yet — be the first!">❓</span>
            </li>
            <li class="topic-item" data-cat="all" data-title="Quiz auto-submit logic" data-id="3" onclick="selectTopic(this)">
                <div class="topic-dot brand"></div>
                <div class="topic-body">
                    <span class="topic-name">Quiz auto-submit logic</span>
                    <span class="topic-meta">Group 1 · CIT 4100 · <strong>4 replies</strong></span>
                </div>
            </li>
            <li class="topic-item" data-cat="mine" data-title="ERD feedback" data-id="4" onclick="selectTopic(this)">
                <div class="topic-dot brand"></div>
                <div class="topic-body">
                    <span class="topic-name">ERD feedback</span>
                    <span class="topic-meta">Group 3 · CIT 4302 · <strong>2 replies</strong></span>
                </div>
                <span class="topic-badge new">Mine</span>
            </li>
            <li class="topic-item" data-cat="hot" data-title="Docker containerisation" data-id="5" onclick="selectTopic(this)">
                <div class="topic-dot emerald"></div>
                <div class="topic-body">
                    <span class="topic-name">Docker containerisation</span>
                    <span class="topic-meta">Group 1 · CIT 4100 · <strong>15 replies</strong></span>
                </div>
                <span class="topic-badge hot">Hot</span>
            </li>
            <li class="topic-item" data-cat="all" data-title="REST API design patterns" data-id="6" onclick="selectTopic(this)">
                <div class="topic-dot brand"></div>
                <div class="topic-body">
                    <span class="topic-name">REST API design patterns</span>
                    <span class="topic-meta">Group 4 · CIT 4203 · <strong>9 replies</strong></span>
                </div>
            </li>
        </ul>

        {{-- Footer: export --}}
        <div class="panel-footer">
            <button class="export-btn" onclick="exportTopicsPDF()">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export topic list to PDF
            </button>
        </div>
    </aside>

    {{-- ══ RIGHT PANEL: Chat ══ --}}
    <main class="chat-panel">

        {{-- Chat header --}}
        <div class="chat-header">
            <div class="chat-header-info">
                <h3 class="chat-topic-name" id="chatTopicName">System architecture Q&amp;A</h3>
                <span class="chat-meta">
                    Group 4 · CIT 4203 ·
                    <span class="live-dot"></span> Live ·
                    <span id="onlineCount">6 online</span>
                </span>
            </div>
            <div class="chat-header-actions">
                {{-- Participation score --}}
                <div class="participation-badge" title="Your participation score for this topic">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    <span>12 pts</span>
                </div>

                {{-- Private mode toggle --}}
                <button class="header-btn" id="privateToggle" onclick="togglePrivateMode()" title="Send private message (select recipients)">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Private
                </button>

                {{-- Export this topic's chat --}}
                <button class="header-btn export-chat-btn" onclick="exportChatPDF()" title="Export this conversation to PDF">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Export PDF
                </button>

                {{-- Members count --}}
                <span class="participants-count">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    6 online
                </span>
            </div>
        </div>

        {{-- Private mode banner --}}
        <div class="private-banner" id="privateBanner" style="display:none;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            <span>Private mode — select recipients:</span>
            <div class="private-recipients" id="privateRecipients">
                <label class="priv-check"><input type="checkbox" value="1"> Moses Kintu</label>
                <label class="priv-check"><input type="checkbox" value="2"> Joan Kavuma</label>
                <label class="priv-check"><input type="checkbox" value="3"> Sarah Nakabuye</label>
                <label class="priv-check"><input type="checkbox" value="4"> David Ochieng</label>
            </div>
            <button class="priv-cancel" onclick="togglePrivateMode()">Cancel</button>
        </div>

        {{-- Unanswered alert --}}
        <div class="unanswered-alert" id="unansweredAlert" style="display:none;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01"/></svg>
            This topic has no replies yet. Be the first to respond!
        </div>

        {{-- Messages area --}}
        <div class="messages-area" id="messagesArea">
            <div class="day-divider"><span>Today</span></div>

            {{-- Sample messages --}}
            <div class="msg-row other" data-msg-id="1">
                <div class="msg-avatar" style="background:#7c3aed;">MK</div>
                <div class="msg-bubble-wrap">
                    <span class="msg-name">Moses Kintu <span class="msg-pts">+2pts</span></span>
                    <div class="msg-bubble" id="msg-1">
                        Why did we go with a modular monolith instead of full microservices for this?
                    </div>
                    <div class="msg-actions">
                        <span class="msg-time">10:42 AM</span>
                        <button class="msg-action-btn" onclick="replyTo('Moses Kintu')" title="Reply">↩ Reply</button>
                        <button class="msg-action-btn" onclick="flagMessage(1)" title="Flag as irrelevant">🚩 Flag</button>
                        <button class="msg-action-btn" onclick="shareMessage(1)" title="Share to social media">↗ Share</button>
                    </div>
                </div>
            </div>

            <div class="msg-row other" data-msg-id="2">
                <div class="msg-avatar" style="background:#0891b2;">JK</div>
                <div class="msg-bubble-wrap">
                    <span class="msg-name">Joan Kavuma <span class="msg-pts">+2pts</span></span>
                    <div class="msg-bubble" id="msg-2">
                        Mainly deployment complexity — micro-services would be overkill for our timeline.
                    </div>
                    <div class="msg-actions">
                        <span class="msg-time">10:44 AM</span>
                        <button class="msg-action-btn" onclick="replyTo('Joan Kavuma')" title="Reply">↩ Reply</button>
                        <button class="msg-action-btn" onclick="flagMessage(2)" title="Flag as irrelevant">🚩 Flag</button>
                        <button class="msg-action-btn" onclick="shareMessage(2)" title="Share to social media">↗ Share</button>
                    </div>
                </div>
            </div>

            <div class="msg-row own" data-msg-id="3">
                <div class="msg-bubble-wrap own">
                    <div class="msg-bubble own" id="msg-3">
                        Yes — that's the beauty of a modular monolith. Each module has a clear boundary, so extracting a service later is straightforward.
                    </div>
                    <div class="msg-actions own-actions">
                        <span class="msg-time">10:48 AM</span>
                        <button class="msg-action-btn" onclick="shareMessage(3)" title="Share to social media">↗ Share</button>
                    </div>
                </div>
            </div>

            <div class="msg-row other" data-msg-id="4">
                <div class="msg-avatar" style="background:#059669;">SN</div>
                <div class="msg-bubble-wrap">
                    <span class="msg-name">Sarah Nakabuye <span class="msg-pts">+2pts</span></span>
                    <div class="msg-bubble" id="msg-4">
                        Agreed. Also the shared database is simpler to manage during development.
                    </div>
                    <div class="msg-actions">
                        <span class="msg-time">10:50 AM</span>
                        <button class="msg-action-btn" onclick="replyTo('Sarah Nakabuye')" title="Reply">↩ Reply</button>
                        <button class="msg-action-btn" onclick="flagMessage(4)" title="Flag as irrelevant">🚩 Flag</button>
                        <button class="msg-action-btn" onclick="shareMessage(4)" title="Share to social media">↗ Share</button>
                    </div>
                </div>
            </div>

            <div class="typing-indicator" id="typingIndicator" style="display:none;">
                <div class="msg-avatar" style="background:#7c3aed;width:28px;height:28px;font-size:0.62rem;">MK</div>
                <div class="typing-dots"><span></span><span></span><span></span></div>
            </div>
        </div>

        {{-- Reply preview bar --}}
        <div class="reply-bar" id="replyBar" style="display:none;">
            <div class="reply-bar-inner">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                Replying to <strong id="replyTarget"></strong>
            </div>
            <button onclick="cancelReply()">×</button>
        </div>

        {{-- Compose --}}
        <div class="compose-area">
            <div class="compose-inner">
                <textarea id="messageInput" class="compose-input" placeholder="Write a message…" rows="1"
                    onkeydown="handleKey(event)" oninput="autoResize(this)"></textarea>
                <div class="compose-toolbar">
                    <span class="compose-hint" id="composeHint">Press Enter to send · Shift+Enter for new line</span>
                    <button class="send-btn" onclick="sendMessage()">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        Send
                    </button>
                </div>
            </div>
        </div>
    </main>
</div>

{{-- ══ NEW TOPIC MODAL ══ --}}
<div class="modal-overlay" id="newTopicOverlay" onclick="closeModalOutside(event,'newTopicOverlay')">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Create new topic</h3>
            <button class="modal-close" onclick="closeModal('newTopicOverlay')">×</button>
        </div>
        <div class="modal-body">
            <div class="lq-field">
                <label class="lq-label">Topic title</label>
                <input type="text" class="lq-input" id="newTopicTitle" placeholder="e.g. How does MVC work in Laravel?">
            </div>
            <div class="lq-field">
                <label class="lq-label">Group / Course</label>
                <select class="lq-select" id="newTopicGroup">
                    <option>Group 1 · CIT 4100</option>
                    <option>Group 2 · CIT 4201</option>
                    <option>Group 3 · CIT 4302</option>
                    <option>Group 4 · CIT 4203</option>
                </select>
            </div>
            <div class="lq-field">
                <label class="lq-label">Opening message</label>
                <textarea class="lq-textarea" id="newTopicMsg" rows="3" placeholder="Describe your question or topic…"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="qm-btn-ghost" onclick="closeModal('newTopicOverlay')">Cancel</button>
            <button class="lq-btn-publish" onclick="createTopic()">Post topic</button>
        </div>
    </div>
</div>

{{-- ══ SHARE TO SOCIAL MEDIA MODAL ══ --}}
<div class="modal-overlay" id="shareOverlay" onclick="closeModalOutside(event,'shareOverlay')">
    <div class="modal-box modal-sm">
        <div class="modal-header">
            <h3>Share message</h3>
            <button class="modal-close" onclick="closeModal('shareOverlay')">×</button>
        </div>
        <div class="modal-body">
            <p class="share-preview" id="sharePreviewText"></p>
            <div class="share-platforms">
                <a class="share-platform whatsapp" onclick="doShare('whatsapp')">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    WhatsApp
                </a>
                <a class="share-platform telegram" onclick="doShare('telegram')">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                    Telegram
                </a>
                <a class="share-platform twitter" onclick="doShare('twitter')">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.259 5.63zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    X (Twitter)
                </a>
                <a class="share-platform facebook" onclick="doShare('facebook')">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    Facebook
                </a>
                <a class="share-platform copy" onclick="doShare('copy')">
                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    Copy link
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ══ FLAG MODAL ══ --}}
<div class="modal-overlay" id="flagOverlay" onclick="closeModalOutside(event,'flagOverlay')">
    <div class="modal-box modal-sm">
        <div class="modal-header">
            <h3>🚩 Flag message</h3>
            <button class="modal-close" onclick="closeModal('flagOverlay')">×</button>
        </div>
        <div class="modal-body">
            <p style="font-size:0.875rem;color:var(--text-muted);margin-bottom:1rem;">Why are you flagging this message?</p>
            <div class="flag-options">
                <label class="flag-opt"><input type="radio" name="flagReason" value="irrelevant" checked> Irrelevant to topic</label>
                <label class="flag-opt"><input type="radio" name="flagReason" value="spam"> Spam / flooding</label>
                <label class="flag-opt"><input type="radio" name="flagReason" value="offensive"> Offensive content</label>
                <label class="flag-opt"><input type="radio" name="flagReason" value="other"> Other</label>
            </div>
        </div>
        <div class="modal-footer">
            <button class="qm-btn-ghost" onclick="closeModal('flagOverlay')">Cancel</button>
            <button class="lq-btn-publish" style="background:linear-gradient(135deg,#ef4444,#dc2626);" onclick="submitFlag()">Submit flag</button>
        </div>
    </div>
</div>

{{-- Toast --}}
<div class="ds-toast" id="dsToast"></div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
// ── State ──────────────────────────────────────────────
let activeFilter   = 'all';
let replyingTo     = null;
let privateMode    = false;
let currentTopic   = { id:1, name:'System architecture Q&A', group:'Group 4 · CIT 4203' };
let flagTargetId   = null;
let shareTargetId  = null;
let msgCounter     = 10;

// Messages per topic (simulated — replace with Echo/Axios in production)
const topicMessages = {
    1: [
        { id:1, author:'Moses Kintu', initials:'MK', color:'#7c3aed', text:'Why did we go with a modular monolith instead of full microservices for this?', time:'10:42 AM', own:false },
        { id:2, author:'Joan Kavuma', initials:'JK', color:'#0891b2', text:'Mainly deployment complexity — micro-services would be overkill for our timeline.', time:'10:44 AM', own:false },
        { id:3, author:'You', initials:'ME', color:'', text:'Yes — that\'s the beauty of a modular monolith. Each module has a clear boundary.', time:'10:48 AM', own:true },
        { id:4, author:'Sarah Nakabuye', initials:'SN', color:'#059669', text:'Agreed. Also the shared database is simpler to manage during development.', time:'10:50 AM', own:false },
    ],
    2: [], // unanswered
    3: [
        { id:5, author:'Alice Tendo', initials:'AT', color:'#be185d', text:'How does the auto-submit work exactly?', time:'09:10 AM', own:false },
    ],
    4: [
        { id:6, author:'You', initials:'ME', color:'', text:'Can someone review my ERD for the project?', time:'Yesterday', own:true },
        { id:7, author:'Moses Kintu', initials:'MK', color:'#7c3aed', text:'I\'ll take a look — share the diagram here.', time:'Yesterday', own:false },
    ],
    5: [
        { id:8, author:'David Ochieng', initials:'DO', color:'#d97706', text:'Docker compose makes multi-service dev so much easier!', time:'08:00 AM', own:false },
    ],
    6: [
        { id:9, author:'Joan Kavuma', initials:'JK', color:'#0891b2', text:'REST vs GraphQL — which should we use for the mobile app?', time:'11:00 AM', own:false },
    ],
};

// ── Topic selection ────────────────────────────────────
function selectTopic(el) {
    document.querySelectorAll('.topic-item').forEach(i => i.classList.remove('active'));
    el.classList.add('active');

    const id   = parseInt(el.dataset.id);
    const name = el.querySelector('.topic-name').textContent;
    const meta = el.querySelector('.topic-meta').textContent.split('·')[0].trim();
    currentTopic = { id, name, group: meta };

    document.getElementById('chatTopicName').textContent = name;

    // Show unanswered alert
    const msgs = topicMessages[id] || [];
    const unanswered = msgs.length === 0;
    document.getElementById('unansweredAlert').style.display = unanswered ? 'flex' : 'none';

    // Render messages
    renderMessages(msgs);
}

function renderMessages(msgs) {
    const area      = document.getElementById('messagesArea');
    const indicator = document.getElementById('typingIndicator');
    area.innerHTML  = '';

    const divider = document.createElement('div');
    divider.className = 'day-divider';
    divider.innerHTML = '<span>Today</span>';
    area.appendChild(divider);

    if (msgs.length === 0) {
        const empty = document.createElement('div');
        empty.className = 'chat-empty';
        empty.innerHTML = `<svg width="36" height="36" fill="none" stroke="#94a3b8" stroke-width="1.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg><p>No messages yet — start the conversation!</p>`;
        area.appendChild(empty);
    } else {
        msgs.forEach(m => area.appendChild(buildMsgEl(m)));
    }

    area.appendChild(indicator);
    area.scrollTop = area.scrollHeight;
}

function buildMsgEl(m) {
    const row = document.createElement('div');
    row.className = `msg-row ${m.own ? 'own' : 'other'}`;
    row.dataset.msgId = m.id;

    if (m.own) {
        row.innerHTML = `
            <div class="msg-bubble-wrap own">
                <div class="msg-bubble own" id="msg-${m.id}">${esc(m.text)}</div>
                <div class="msg-actions own-actions">
                    <span class="msg-time">${m.time}</span>
                    <button class="msg-action-btn" onclick="shareMessage(${m.id})">↗ Share</button>
                </div>
            </div>`;
    } else {
        row.innerHTML = `
            <div class="msg-avatar" style="background:${m.color};">${m.initials}</div>
            <div class="msg-bubble-wrap">
                <span class="msg-name">${esc(m.author)} <span class="msg-pts">+2pts</span></span>
                <div class="msg-bubble" id="msg-${m.id}">${esc(m.text)}</div>
                <div class="msg-actions">
                    <span class="msg-time">${m.time}</span>
                    <button class="msg-action-btn" onclick="replyTo('${esc(m.author)}')">↩ Reply</button>
                    <button class="msg-action-btn" onclick="flagMessage(${m.id})">🚩 Flag</button>
                    <button class="msg-action-btn" onclick="shareMessage(${m.id})">↗ Share</button>
                </div>
            </div>`;
    }
    return row;
}

// ── Filter / search ────────────────────────────────────
function filterTopics(q) {
    q = q.toLowerCase();
    document.querySelectorAll('.topic-item').forEach(item => {
        item.style.display = item.dataset.title.toLowerCase().includes(q) ? '' : 'none';
    });
}
function setFilter(filter, btn) {
    activeFilter = filter;
    document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.topic-item').forEach(item => {
        if (filter === 'all') { item.style.display = ''; return; }
        item.style.display = item.dataset.cat === filter ? '' : 'none';
    });
}

// ── Messaging ──────────────────────────────────────────
function handleKey(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
}

function sendMessage() {
    const input = document.getElementById('messageInput');
    const text  = input.value.trim();
    if (!text) return;

    const now   = new Date().toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
    msgCounter++;

    // Build prefix for reply
    const prefix = replyingTo ? `[↩ ${replyingTo}] ` : '';

    // Private badge
    const isPrivate = privateMode;
    const recipients = isPrivate
        ? Array.from(document.querySelectorAll('#privateRecipients input:checked')).map(c => c.nextSibling.textContent.trim())
        : [];

    const msg = { id: msgCounter, author:'You', initials:'ME', color:'', text: prefix + text, time: now, own: true };

    // Add to local store
    if (!topicMessages[currentTopic.id]) topicMessages[currentTopic.id] = [];
    topicMessages[currentTopic.id].push(msg);

    const area = document.getElementById('messagesArea');
    const indicator = document.getElementById('typingIndicator');
    const el = buildMsgEl(msg);

    // Add private badge if needed
    if (isPrivate && recipients.length > 0) {
        const badge = document.createElement('div');
        badge.className = 'private-msg-badge';
        badge.textContent = `🔒 Private · Only to: ${recipients.join(', ')}`;
        el.querySelector('.msg-bubble-wrap').insertBefore(badge, el.querySelector('.msg-bubble'));
    }

    area.insertBefore(el, indicator);
    area.scrollTop = area.scrollHeight;

    input.value = '';
    input.style.height = 'auto';
    cancelReply();

    // Update unanswered
    document.getElementById('unansweredAlert').style.display = 'none';

    // Simulate typing reply
    setTimeout(() => {
        indicator.style.display = 'flex';
        area.scrollTop = area.scrollHeight;
        setTimeout(() => {
            indicator.style.display = 'none';
            const replies = ["Great point, thanks for sharing!", "Interesting — can you elaborate?", "That makes sense!", "I agree with that approach."];
            const reply = { id: ++msgCounter, author:'Moses Kintu', initials:'MK', color:'#7c3aed', text: replies[Math.floor(Math.random()*replies.length)], time: new Date().toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'}), own:false };
            topicMessages[currentTopic.id].push(reply);
            area.insertBefore(buildMsgEl(reply), indicator);
            area.scrollTop = area.scrollHeight;
        }, 2000);
    }, 1200);

    // TODO: Broadcast via Laravel Echo
    // window.Echo.private(`topic.${currentTopic.id}`).whisper('message', { text, private: isPrivate, recipients });
}

function autoResize(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 140) + 'px';
}

// ── Reply ──────────────────────────────────────────────
function replyTo(name) {
    replyingTo = name;
    document.getElementById('replyTarget').textContent = name;
    document.getElementById('replyBar').style.display = 'flex';
    document.getElementById('messageInput').focus();
}
function cancelReply() {
    replyingTo = null;
    document.getElementById('replyBar').style.display = 'none';
}

// ── Private mode ───────────────────────────────────────
function togglePrivateMode() {
    privateMode = !privateMode;
    const banner = document.getElementById('privateBanner');
    const btn    = document.getElementById('privateToggle');
    banner.style.display = privateMode ? 'flex' : 'none';
    btn.classList.toggle('active-btn', privateMode);
    document.getElementById('composeHint').textContent = privateMode
        ? '🔒 Private message — only selected recipients will see this'
        : 'Press Enter to send · Shift+Enter for new line';
}

// ── Flag ───────────────────────────────────────────────
function flagMessage(id) {
    flagTargetId = id;
    document.getElementById('flagOverlay').classList.add('open');
}
function submitFlag() {
    const reason = document.querySelector('input[name="flagReason"]:checked')?.value;
    showToast(`Message flagged as "${reason}". Admins will review it.`, 'amber');
    closeModal('flagOverlay');
}

// ── Share ──────────────────────────────────────────────
function shareMessage(id) {
    shareTargetId = id;
    const el = document.getElementById('msg-' + id);
    const text = el ? el.textContent.trim().slice(0, 100) + '…' : '';
    document.getElementById('sharePreviewText').textContent = `"${text}"`;
    document.getElementById('shareOverlay').classList.add('open');
}

function doShare(platform) {
    const el   = document.getElementById('msg-' + shareTargetId);
    const text = el ? el.textContent.trim() : '';
    const url  = encodeURIComponent(window.location.href);
    const msg  = encodeURIComponent(`[SDF - ${currentTopic.name}] ${text}`);
    let link   = '';

    if (platform === 'whatsapp')  link = `https://wa.me/?text=${msg}`;
    if (platform === 'telegram')  link = `https://t.me/share/url?url=${url}&text=${msg}`;
    if (platform === 'twitter')   link = `https://twitter.com/intent/tweet?text=${msg}`;
    if (platform === 'facebook')  link = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
    if (platform === 'copy') {
        navigator.clipboard.writeText(text);
        showToast('Message copied to clipboard!', 'success');
        closeModal('shareOverlay');
        return;
    }

    if (link) window.open(link, '_blank');
    closeModal('shareOverlay');
}

// ── Export chat to PDF ─────────────────────────────────
function exportChatPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    const msgs = topicMessages[currentTopic.id] || [];

    // Header
    doc.setFillColor(79, 70, 229);
    doc.rect(0, 0, 210, 28, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(14);
    doc.setFont('helvetica', 'bold');
    doc.text('SOFTWARE DISCUSSION FORUM', 14, 12);
    doc.setFontSize(10);
    doc.setFont('helvetica', 'normal');
    doc.text(`Topic: ${currentTopic.name}`, 14, 20);
    doc.text(`Exported: ${new Date().toLocaleString()}`, 14, 26);

    // Messages
    doc.setTextColor(30, 41, 59);
    let y = 38;

    if (msgs.length === 0) {
        doc.setFontSize(11);
        doc.text('No messages in this topic yet.', 14, y);
    } else {
        msgs.forEach(m => {
            if (y > 270) { doc.addPage(); y = 20; }
            doc.setFontSize(9);
            doc.setFont('helvetica', 'bold');
            doc.setTextColor(79, 70, 229);
            doc.text(`${m.own ? 'You' : m.author}  ·  ${m.time}`, 14, y);
            y += 5;
            doc.setFont('helvetica', 'normal');
            doc.setTextColor(30, 41, 59);
            const lines = doc.splitTextToSize(m.text, 180);
            doc.text(lines, 14, y);
            y += lines.length * 5 + 4;
            doc.setDrawColor(226, 232, 240);
            doc.line(14, y, 196, y);
            y += 4;
        });
    }

    // Footer
    doc.setFontSize(8);
    doc.setTextColor(100, 116, 139);
    doc.text(`SDF Platform · ${currentTopic.group} · Page 1`, 14, 290);

    doc.save(`SDF_${currentTopic.name.replace(/\s+/g,'_')}_${Date.now()}.pdf`);
    showToast('Chat exported to PDF!', 'success');
}

function exportTopicsPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    doc.setFillColor(79, 70, 229);
    doc.rect(0, 0, 210, 28, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(14);
    doc.setFont('helvetica', 'bold');
    doc.text('SOFTWARE DISCUSSION FORUM — Topic List', 14, 12);
    doc.setFontSize(9);
    doc.setFont('helvetica', 'normal');
    doc.text(`Exported: ${new Date().toLocaleString()}`, 14, 24);

    let y = 36;
    document.querySelectorAll('.topic-item').forEach((item, i) => {
        if (item.style.display === 'none') return;
        if (y > 270) { doc.addPage(); y = 20; }
        doc.setFontSize(10);
        doc.setFont('helvetica', 'bold');
        doc.setTextColor(30, 41, 59);
        doc.text(`${i+1}. ${item.querySelector('.topic-name').textContent}`, 14, y);
        y += 5;
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(8);
        doc.setTextColor(100, 116, 139);
        doc.text(item.querySelector('.topic-meta').textContent.trim(), 18, y);
        y += 8;
    });

    doc.save(`SDF_TopicList_${Date.now()}.pdf`);
    showToast('Topic list exported to PDF!', 'success');
}

// ── New topic ──────────────────────────────────────────
function openNewTopicModal() {
    document.getElementById('newTopicOverlay').classList.add('open');
}
function createTopic() {
    const title = document.getElementById('newTopicTitle').value.trim();
    if (!title) { showToast('Please enter a topic title.', 'amber'); return; }
    const group = document.getElementById('newTopicGroup').value;
    const id = Date.now();
    const li = document.createElement('li');
    li.className = 'topic-item';
    li.dataset.cat = 'mine';
    li.dataset.title = title.toLowerCase();
    li.dataset.id = id;
    li.onclick = function() { selectTopic(this); };
    li.innerHTML = `
        <div class="topic-dot brand"></div>
        <div class="topic-body">
            <span class="topic-name">${esc(title)}</span>
            <span class="topic-meta">${esc(group)} · <strong>0 replies</strong></span>
        </div>
        <span class="topic-badge new">Mine</span>`;
    document.getElementById('topicList').appendChild(li);
    topicMessages[id] = [];
    closeModal('newTopicOverlay');
    showToast('Topic created!', 'success');
    selectTopic(li);
}

// ── Modals ─────────────────────────────────────────────
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
function closeModalOutside(e, id) { if (e.target === document.getElementById(id)) closeModal(id); }

// ── Toast ──────────────────────────────────────────────
function showToast(msg, type='success') {
    const colors = { success:'#059669', amber:'#d97706', error:'#ef4444' };
    const t = document.getElementById('dsToast');
    t.textContent = msg;
    t.style.background = colors[type] || colors.success;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3000);
}

function esc(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// Init
document.getElementById('messagesArea').scrollTop = 9999;
</script>
@endpush

<style>
/* ══ BASE VARIABLES ══ */
:root {
    --brand:#4f46e5; --brand-dark:#4338ca;
    --surface:#ffffff; --bg:#f1f5f9;
    --text-primary:#1e293b; --text-muted:#64748b;
    --border:#e2e8f0; --emerald:#10b981;
    --amber:#f59e0b; --rose:#ef4444;
    --nav-h:64px;
}
/* ══ SHELL ══ */
.discussion-shell { display:grid; grid-template-columns:320px 1fr; height:calc(100vh - var(--nav-h)); overflow:hidden; }
/* ══ LEFT PANEL ══ */
.topics-panel { display:flex; flex-direction:column; background:white; border-right:1px solid var(--border); overflow:hidden; }
.panel-header { display:flex; align-items:center; justify-content:space-between; padding:1rem 1.1rem 0.7rem; border-bottom:1px solid var(--border); }
.panel-title { font-size:1rem; font-weight:700; }
.panel-header-actions { display:flex; gap:0.35rem; }
.btn-icon { width:28px; height:28px; background:var(--brand); border:none; border-radius:7px; cursor:pointer; color:white; display:flex; align-items:center; justify-content:center; transition:background 0.18s; }
.btn-icon:hover { background:var(--brand-dark); }
.search-wrap { position:relative; padding:0.7rem 1rem 0.45rem; }
.search-icon { position:absolute; left:1.65rem; top:50%; transform:translateY(-50%); color:var(--text-muted); pointer-events:none; }
.search-input { width:100%; padding:0.48rem 0.8rem 0.48rem 2.1rem; border:1.5px solid var(--border); border-radius:8px; font-size:0.85rem; font-family:'Inter',sans-serif; background:var(--bg); color:var(--text-primary); transition:border-color 0.18s; }
.search-input:focus { outline:none; border-color:var(--brand); background:white; box-shadow:0 0 0 3px rgba(79,70,229,0.1); }
.filter-chips { display:flex; gap:0.35rem; padding:0 1rem 0.65rem; flex-wrap:wrap; }
.chip { padding:0.22rem 0.65rem; border-radius:999px; border:1.5px solid var(--border); background:white; font-size:0.75rem; font-family:'Inter',sans-serif; font-weight:500; color:var(--text-muted); cursor:pointer; transition:all 0.18s; }
.chip:hover { border-color:var(--brand); color:var(--brand); }
.chip.active { background:var(--brand); border-color:var(--brand); color:white; }
.recommend-label { display:flex; align-items:center; gap:0.4rem; font-size:0.7rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.06em; padding:0 1rem 0.45rem; }
.ml-chip { background:linear-gradient(135deg,#7c3aed,#4f46e5); color:white; font-size:0.62rem; padding:1px 5px; border-radius:4px; }
.topic-list { list-style:none; flex:1; overflow-y:auto; padding:0 0.5rem; }
.topic-list::-webkit-scrollbar { width:3px; }
.topic-list::-webkit-scrollbar-thumb { background:var(--border); border-radius:3px; }
.topic-item { display:flex; align-items:center; gap:0.6rem; padding:0.65rem 0.7rem; border-radius:9px; cursor:pointer; transition:background 0.15s; margin-bottom:0.1rem; }
.topic-item:hover { background:#f8fafc; }
.topic-item.active { background:#eef2ff; }
.topic-dot { width:7px; height:7px; border-radius:50%; flex-shrink:0; }
.topic-dot.emerald { background:var(--emerald); }
.topic-dot.amber   { background:var(--amber); }
.topic-dot.brand   { background:var(--brand); }
.topic-dot.muted   { background:#cbd5e1; }
.pulse-dot { animation:dotPulse 2s infinite; }
@keyframes dotPulse { 0%,100%{box-shadow:0 0 0 0 rgba(245,158,11,0.5)} 50%{box-shadow:0 0 0 5px rgba(245,158,11,0)} }
.topic-body { flex:1; min-width:0; }
.topic-name { display:block; font-size:0.85rem; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.topic-meta { display:block; font-size:0.73rem; color:var(--text-muted); margin-top:0.12rem; }
.unanswered-text { color:var(--amber) !important; }
.topic-badge { font-size:0.67rem; font-weight:700; padding:2px 6px; border-radius:999px; flex-shrink:0; }
.topic-badge.hot        { background:#fef3c7; color:#92400e; }
.topic-badge.new        { background:#eef2ff; color:#3730a3; }
.topic-badge.unanswered { background:#fef3c7; color:#92400e; font-size:0.8rem; }
.panel-footer { padding:0.7rem 1rem; border-top:1px solid var(--border); }
.export-btn { width:100%; display:flex; align-items:center; justify-content:center; gap:0.45rem; padding:0.5rem; background:#f8fafc; border:1.5px solid var(--border); border-radius:8px; font-size:0.8rem; font-family:'Inter',sans-serif; font-weight:600; color:var(--text-muted); cursor:pointer; transition:all 0.18s; }
.export-btn:hover { background:#eef2ff; border-color:var(--brand); color:var(--brand); }
/* ══ RIGHT PANEL ══ */
.chat-panel { display:flex; flex-direction:column; background:#f8fafc; overflow:hidden; }
.chat-header { display:flex; align-items:center; justify-content:space-between; padding:0.85rem 1.3rem; background:white; border-bottom:1px solid var(--border); box-shadow:0 1px 4px rgba(0,0,0,0.04); gap:0.5rem; flex-wrap:wrap; }
.chat-topic-name { font-size:0.98rem; font-weight:700; }
.chat-meta { font-size:0.75rem; color:var(--text-muted); display:flex; align-items:center; gap:0.4rem; margin-top:0.12rem; flex-wrap:wrap; }
.live-dot { display:inline-block; width:7px; height:7px; background:var(--emerald); border-radius:50%; animation:pulse 2s infinite; }
@keyframes pulse { 0%,100%{box-shadow:0 0 0 0 rgba(16,185,129,0.5)} 50%{box-shadow:0 0 0 5px rgba(16,185,129,0)} }
.chat-header-actions { display:flex; align-items:center; gap:0.45rem; flex-wrap:wrap; }
.header-btn { display:flex; align-items:center; gap:0.3rem; padding:0.35rem 0.7rem; background:#f1f5f9; border:1.5px solid var(--border); border-radius:7px; font-size:0.75rem; font-family:'Inter',sans-serif; font-weight:600; color:var(--text-muted); cursor:pointer; transition:all 0.18s; white-space:nowrap; }
.header-btn:hover { border-color:var(--brand); color:var(--brand); background:#eef2ff; }
.header-btn.active-btn { background:#eef2ff; border-color:var(--brand); color:var(--brand); }
.export-chat-btn:hover { border-color:#059669; color:#059669; background:#f0fdf4; }
.participation-badge { display:flex; align-items:center; gap:0.3rem; padding:0.35rem 0.7rem; background:#fef3c7; border:1.5px solid #fde68a; border-radius:7px; font-size:0.75rem; font-weight:700; color:#92400e; white-space:nowrap; }
.participants-count { display:flex; align-items:center; gap:0.3rem; font-size:0.75rem; color:var(--text-muted); background:#f1f5f9; padding:0.28rem 0.65rem; border-radius:999px; font-weight:500; white-space:nowrap; }
/* Private banner */
.private-banner { display:flex; align-items:center; gap:0.6rem; padding:0.6rem 1.3rem; background:#ede9fe; border-bottom:1px solid #c4b5fd; font-size:0.82rem; color:#5b21b6; font-weight:500; flex-wrap:wrap; }
.private-recipients { display:flex; gap:0.75rem; flex-wrap:wrap; }
.priv-check { display:flex; align-items:center; gap:0.3rem; font-size:0.8rem; cursor:pointer; font-weight:500; }
.priv-check input { accent-color:var(--brand); }
.priv-cancel { margin-left:auto; background:none; border:1.5px solid #c4b5fd; border-radius:6px; padding:0.2rem 0.6rem; font-size:0.78rem; font-family:'Inter',sans-serif; color:#5b21b6; cursor:pointer; }
/* Unanswered alert */
.unanswered-alert { display:flex; align-items:center; gap:0.5rem; padding:0.6rem 1.3rem; background:#fef3c7; border-bottom:1px solid #fde68a; font-size:0.82rem; color:#92400e; font-weight:500; }
/* Messages */
.messages-area { flex:1; overflow-y:auto; padding:1.1rem 1.3rem; display:flex; flex-direction:column; gap:0.55rem; }
.messages-area::-webkit-scrollbar { width:3px; }
.messages-area::-webkit-scrollbar-thumb { background:var(--border); border-radius:3px; }
.day-divider { display:flex; align-items:center; gap:0.7rem; margin:0.4rem 0; }
.day-divider::before,.day-divider::after { content:''; flex:1; height:1px; background:var(--border); }
.day-divider span { font-size:0.7rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.06em; }
.chat-empty { display:flex; flex-direction:column; align-items:center; gap:0.6rem; padding:3rem 1rem; color:var(--text-muted); font-size:0.875rem; text-align:center; }
.msg-row { display:flex; align-items:flex-end; gap:0.55rem; max-width:75%; }
.msg-row.own { align-self:flex-end; flex-direction:row-reverse; }
.msg-row.other { align-self:flex-start; }
.msg-avatar { width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:0.67rem; font-weight:700; color:white; flex-shrink:0; }
.msg-bubble-wrap { display:flex; flex-direction:column; gap:0.15rem; }
.msg-bubble-wrap.own { align-items:flex-end; }
.msg-name { font-size:0.7rem; font-weight:600; color:var(--text-muted); padding-left:0.2rem; }
.msg-pts { font-size:0.65rem; color:#d97706; font-weight:700; background:#fef3c7; padding:1px 5px; border-radius:999px; margin-left:0.3rem; }
.msg-bubble { background:white; border:1px solid var(--border); border-radius:13px 13px 13px 3px; padding:0.55rem 0.8rem; font-size:0.875rem; line-height:1.5; box-shadow:0 1px 3px rgba(0,0,0,0.05); }
.msg-bubble.own { background:linear-gradient(135deg,#4f46e5,#6366f1); color:white; border:none; border-radius:13px 13px 3px 13px; box-shadow:0 2px 8px rgba(79,70,229,0.28); }
.msg-actions { display:flex; align-items:center; gap:0.4rem; padding:0.15rem 0.2rem; opacity:0; transition:opacity 0.18s; flex-wrap:wrap; }
.msg-row:hover .msg-actions { opacity:1; }
.own-actions { justify-content:flex-end; }
.msg-time { font-size:0.67rem; color:var(--text-muted); }
.msg-action-btn { background:none; border:none; font-size:0.7rem; color:var(--text-muted); cursor:pointer; padding:0.1rem 0.3rem; border-radius:4px; transition:background 0.15s,color 0.15s; font-family:'Inter',sans-serif; white-space:nowrap; }
.msg-action-btn:hover { background:var(--border); color:var(--text-primary); }
.private-msg-badge { font-size:0.68rem; font-weight:600; color:#5b21b6; background:#ede9fe; border-radius:5px; padding:2px 7px; margin-bottom:0.2rem; display:inline-block; }
/* Typing */
.typing-indicator { display:flex; align-items:flex-end; gap:0.55rem; }
.typing-dots { background:white; border:1px solid var(--border); border-radius:13px 13px 13px 3px; padding:0.6rem 0.9rem; display:flex; gap:0.28rem; align-items:center; }
.typing-dots span { width:6px; height:6px; background:var(--text-muted); border-radius:50%; animation:bounce 1.4s infinite; }
.typing-dots span:nth-child(2) { animation-delay:0.2s; }
.typing-dots span:nth-child(3) { animation-delay:0.4s; }
@keyframes bounce { 0%,60%,100%{transform:translateY(0);opacity:0.4} 30%{transform:translateY(-5px);opacity:1} }
/* Reply bar */
.reply-bar { display:flex; align-items:center; justify-content:space-between; padding:0.5rem 1.3rem; background:#f0fdf4; border-top:1px solid #86efac; font-size:0.8rem; color:#166534; gap:0.5rem; }
.reply-bar-inner { display:flex; align-items:center; gap:0.4rem; }
.reply-bar button { background:none; border:none; cursor:pointer; font-size:1rem; color:var(--text-muted); padding:0.1rem 0.3rem; }
/* Compose */
.compose-area { padding:0.85rem 1.3rem; background:white; border-top:1px solid var(--border); }
.compose-inner { border:1.5px solid var(--border); border-radius:12px; overflow:hidden; transition:border-color 0.18s; }
.compose-inner:focus-within { border-color:var(--brand); box-shadow:0 0 0 3px rgba(79,70,229,0.1); }
.compose-input { width:100%; padding:0.65rem 1rem; border:none; font-size:0.9rem; font-family:'Inter',sans-serif; resize:none; line-height:1.5; max-height:120px; background:white; color:var(--text-primary); }
.compose-input:focus { outline:none; }
.compose-toolbar { display:flex; align-items:center; justify-content:space-between; padding:0.4rem 0.75rem 0.5rem; background:#f8fafc; border-top:1px solid var(--border); }
.compose-hint { font-size:0.7rem; color:var(--text-muted); }
.send-btn { display:flex; align-items:center; gap:0.4rem; padding:0.5rem 1rem; background:linear-gradient(135deg,#4f46e5,#6366f1); color:white; border:none; border-radius:8px; font-size:0.82rem; font-family:'Inter',sans-serif; font-weight:600; cursor:pointer; transition:opacity 0.18s,transform 0.1s; }
.send-btn:hover { opacity:0.9; transform:translateY(-1px); }
/* Modals */
.modal-overlay { position:fixed; inset:0; background:rgba(15,23,42,0.45); backdrop-filter:blur(3px); display:none; align-items:center; justify-content:center; z-index:9999; padding:1rem; }
.modal-overlay.open { display:flex; }
.modal-box { background:white; border-radius:16px; width:100%; max-width:480px; box-shadow:0 20px 60px rgba(0,0,0,0.18); overflow:hidden; }
.modal-sm { max-width:380px; }
.modal-header { display:flex; align-items:center; justify-content:space-between; padding:1rem 1.3rem; border-bottom:1px solid var(--border); }
.modal-header h3 { font-size:0.98rem; font-weight:700; }
.modal-close { background:none; border:none; font-size:1.3rem; cursor:pointer; color:var(--text-muted); padding:0 0.3rem; line-height:1; }
.modal-body { padding:1.1rem 1.3rem; }
.modal-footer { display:flex; justify-content:flex-end; gap:0.65rem; padding:0.85rem 1.3rem; border-top:1px solid var(--border); background:#f8fafc; }
/* Share */
.share-preview { font-size:0.82rem; color:var(--text-muted); font-style:italic; background:#f8fafc; border-radius:8px; padding:0.6rem 0.85rem; margin-bottom:1rem; border-left:3px solid var(--border); }
.share-platforms { display:grid; grid-template-columns:1fr 1fr; gap:0.6rem; }
.share-platform { display:flex; align-items:center; gap:0.6rem; padding:0.65rem 0.9rem; border-radius:10px; font-size:0.85rem; font-weight:600; cursor:pointer; transition:all 0.18s; text-decoration:none; border:1.5px solid var(--border); color:var(--text-primary); background:white; }
.share-platform:hover { transform:translateY(-1px); box-shadow:0 4px 12px rgba(0,0,0,0.1); }
.share-platform.whatsapp:hover { border-color:#25D366; color:#25D366; background:#f0fdf4; }
.share-platform.telegram:hover { border-color:#0088cc; color:#0088cc; background:#eff6ff; }
.share-platform.twitter:hover  { border-color:#000; color:#000; background:#f8fafc; }
.share-platform.facebook:hover { border-color:#1877f2; color:#1877f2; background:#eff6ff; }
.share-platform.copy:hover     { border-color:var(--brand); color:var(--brand); background:#eef2ff; }
/* Flag */
.flag-options { display:flex; flex-direction:column; gap:0.55rem; }
.flag-opt { display:flex; align-items:center; gap:0.5rem; font-size:0.875rem; cursor:pointer; padding:0.5rem 0.75rem; border-radius:8px; transition:background 0.15s; }
.flag-opt:hover { background:#f8fafc; }
.flag-opt input { accent-color:var(--rose); }
/* Toast */
.ds-toast { position:fixed; bottom:1.5rem; right:1.5rem; color:white; font-size:0.875rem; font-weight:500; font-family:'Inter',sans-serif; padding:0.75rem 1.2rem; border-radius:10px; box-shadow:0 4px 20px rgba(0,0,0,0.18); opacity:0; transform:translateY(10px); transition:opacity 0.3s,transform 0.3s; z-index:99999; pointer-events:none; }
.ds-toast.show { opacity:1; transform:translateY(0); }
/* Reuse lq- classes from quiz blade */
.lq-field { margin-bottom:1rem; }
.lq-label { display:block; margin-bottom:0.4rem; font-size:0.82rem; font-weight:600; color:var(--text-primary); }
.lq-input { width:100%; padding:0.55rem 0.85rem; border:1.5px solid var(--border); border-radius:8px; font-size:0.9rem; font-family:'Inter',sans-serif; color:var(--text-primary); transition:border-color 0.18s; }
.lq-input:focus { outline:none; border-color:var(--brand); box-shadow:0 0 0 3px rgba(79,70,229,0.1); }
.lq-select { width:100%; padding:0.55rem 0.85rem; border:1.5px solid var(--border); border-radius:8px; font-size:0.9rem; font-family:'Inter',sans-serif; color:var(--text-primary); background:white; }
.lq-textarea { width:100%; padding:0.55rem 0.85rem; border:1.5px solid var(--border); border-radius:8px; font-size:0.875rem; font-family:'Inter',sans-serif; resize:vertical; line-height:1.5; }
.lq-textarea:focus,.lq-select:focus { outline:none; border-color:var(--brand); box-shadow:0 0 0 3px rgba(79,70,229,0.1); }
.lq-btn-publish { display:inline-flex; align-items:center; gap:0.4rem; padding:0.55rem 1.2rem; background:linear-gradient(135deg,#4f46e5,#6366f1); border:none; border-radius:8px; font-size:0.875rem; font-family:'Inter',sans-serif; font-weight:600; color:white; cursor:pointer; }
.qm-btn-ghost { padding:0.55rem 1.1rem; background:none; border:1.5px solid var(--border); border-radius:8px; font-size:0.875rem; font-family:'Inter',sans-serif; font-weight:500; color:var(--text-muted); cursor:pointer; }
/* Responsive */
@media(max-width:768px){
    .discussion-shell { grid-template-columns:1fr; grid-template-rows:45vh 1fr; }
    .topics-panel { height:45vh; border-right:none; border-bottom:1px solid var(--border); }
    .msg-row { max-width:90%; }
    .chat-header-actions { gap:0.3rem; }
    .header-btn span,.participation-badge span { display:none; }
}
</style>
