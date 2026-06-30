@extends('layout')
@section('title', 'Admin Dashboard — SDF Platform')

@section('page-content')

<div class="ad-shell">

    {{-- ══ SIDEBAR ══ --}}
    <aside class="ad-sidebar">
        <div class="ad-sidebar-brand">
            <div class="ad-sidebar-icon">SDF</div>
            <div>
                <div class="ad-sidebar-title">Admin Panel</div>
                <div class="ad-sidebar-sub">{{ auth()->user()->name }}</div>
            </div>
        </div>

        <nav class="ad-nav">
            <button class="ad-nav-item active" onclick="showSection('overview', this)">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                Overview
            </button>
            <button class="ad-nav-item" onclick="showSection('members', this)">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Members
                <span class="ad-nav-badge" id="pendingBadge">3</span>
            </button>
            <button class="ad-nav-item" onclick="showSection('groups', this)">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                Groups &amp; Stats
            </button>
            <button class="ad-nav-item" onclick="showSection('flags', this)">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                Flagged Content
                <span class="ad-nav-badge danger" id="flagBadge">5</span>
            </button>
            <button class="ad-nav-item" onclick="showSection('participation', this)">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                Participation
            </button>
            <button class="ad-nav-item" onclick="showSection('blacklist', this)">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                Blacklist
            </button>
            <button class="ad-nav-item" onclick="showSection('settings', this)">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
                Settings
            </button>
        </nav>

        <form method="POST" action="/logout" style="margin:1rem;">
            @csrf
            <button type="submit" class="ad-logout-btn">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Logout
            </button>
        </form>
    </aside>

    {{-- ══ MAIN CONTENT ══ --}}
    <div class="ad-main">

        {{-- ── OVERVIEW ── --}}
        <section class="ad-section active" id="section-overview">
            <div class="ad-section-header">
                <h1 class="ad-section-title">Overview</h1>
                <span class="ad-section-sub">Platform at a glance · {{ now()->format('d M Y') }}</span>
            </div>

            <div class="ad-stats-grid">
                <div class="ad-stat-card" style="border-top:3px solid #4f46e5;">
                    <div class="ad-stat-icon" style="background:#eef2ff;">
                        <svg width="20" height="20" fill="none" stroke="#4f46e5" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <span class="ad-stat-num">124</span>
                    <span class="ad-stat-label">Total members</span>
                    <span class="ad-stat-trend up">↑ 8 this week</span>
                </div>
                <div class="ad-stat-card" style="border-top:3px solid #10b981;">
                    <div class="ad-stat-icon" style="background:#dcfce7;">
                        <svg width="20" height="20" fill="none" stroke="#059669" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <span class="ad-stat-num">342</span>
                    <span class="ad-stat-label">Messages today</span>
                    <span class="ad-stat-trend up">↑ 12% vs yesterday</span>
                </div>
                <div class="ad-stat-card" style="border-top:3px solid #f59e0b;">
                    <div class="ad-stat-icon" style="background:#fef3c7;">
                        <svg width="20" height="20" fill="none" stroke="#d97706" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <span class="ad-stat-num">3</span>
                    <span class="ad-stat-label">Pending approvals</span>
                    <span class="ad-stat-trend warn">Needs review</span>
                </div>
                <div class="ad-stat-card" style="border-top:3px solid #ef4444;">
                    <div class="ad-stat-icon" style="background:#fee2e2;">
                        <svg width="20" height="20" fill="none" stroke="#dc2626" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                    </div>
                    <span class="ad-stat-num">5</span>
                    <span class="ad-stat-label">Flagged messages</span>
                    <span class="ad-stat-trend danger">Needs review</span>
                </div>
                <div class="ad-stat-card" style="border-top:3px solid #7c3aed;">
                    <div class="ad-stat-icon" style="background:#ede9fe;">
                        <svg width="20" height="20" fill="none" stroke="#7c3aed" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    </div>
                    <span class="ad-stat-num">2</span>
                    <span class="ad-stat-label">Blacklisted</span>
                    <span class="ad-stat-trend muted">Active bans</span>
                </div>
                <div class="ad-stat-card" style="border-top:3px solid #0891b2;">
                    <div class="ad-stat-icon" style="background:#cffafe;">
                        <svg width="20" height="20" fill="none" stroke="#0891b2" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </div>
                    <span class="ad-stat-num">8</span>
                    <span class="ad-stat-label">Active quizzes</span>
                    <span class="ad-stat-trend up">2 open now</span>
                </div>
            </div>

            {{-- Recent Activity --}}
            <div class="ad-grid-2">
                <div class="ad-card">
                    <h3 class="ad-card-title">Recent activity</h3>
                    <div class="ad-activity-list">
                        <div class="ad-activity-item">
                            <div class="ad-act-dot" style="background:#10b981;"></div>
                            <div class="ad-act-body"><span class="ad-act-text"><strong>Moses Kintu</strong> joined Group 4</span><span class="ad-act-time">2 mins ago</span></div>
                        </div>
                        <div class="ad-activity-item">
                            <div class="ad-act-dot" style="background:#ef4444;"></div>
                            <div class="ad-act-body"><span class="ad-act-text">Message flagged in <strong>System architecture Q&A</strong></span><span class="ad-act-time">15 mins ago</span></div>
                        </div>
                        <div class="ad-activity-item">
                            <div class="ad-act-dot" style="background:#f59e0b;"></div>
                            <div class="ad-act-body"><span class="ad-act-text"><strong>Alice Tendo</strong> received warning #1</span><span class="ad-act-time">1 hour ago</span></div>
                        </div>
                        <div class="ad-activity-item">
                            <div class="ad-act-dot" style="background:#4f46e5;"></div>
                            <div class="ad-act-body"><span class="ad-act-text"><strong>Dr. Okello</strong> published quiz: OOP Basics</span><span class="ad-act-time">2 hours ago</span></div>
                        </div>
                        <div class="ad-activity-item">
                            <div class="ad-act-dot" style="background:#7c3aed;"></div>
                            <div class="ad-act-body"><span class="ad-act-text"><strong>Brian Rackara</strong> was blacklisted (inactive)</span><span class="ad-act-time">Yesterday</span></div>
                        </div>
                        <div class="ad-activity-item">
                            <div class="ad-act-dot" style="background:#10b981;"></div>
                            <div class="ad-act-body"><span class="ad-act-text">3 new registrations pending approval</span><span class="ad-act-time">Today</span></div>
                        </div>
                    </div>
                </div>

                <div class="ad-card">
                    <h3 class="ad-card-title">Members needing attention</h3>
                    <div class="ad-attention-list">
                        <div class="ad-attention-item warn">
                            <div class="ad-att-avatar" style="background:#f59e0b;">AT</div>
                            <div class="ad-att-body">
                                <div class="ad-att-name">Alice Tendo</div>
                                <div class="ad-att-sub">Inactive 14 days · Warning 1/2 sent</div>
                            </div>
                            <button class="ad-att-btn warn-btn" onclick="sendWarning('Alice Tendo', 2)">Send warning 2</button>
                        </div>
                        <div class="ad-attention-item warn">
                            <div class="ad-att-avatar" style="background:#f59e0b;">JO</div>
                            <div class="ad-att-body">
                                <div class="ad-att-name">James Okoth</div>
                                <div class="ad-att-sub">Inactive 21 days · Warning 2/2 sent</div>
                            </div>
                            <button class="ad-att-btn danger-btn" onclick="blacklistMember('James Okoth')">Blacklist</button>
                        </div>
                        <div class="ad-attention-item danger">
                            <div class="ad-att-avatar" style="background:#ef4444;">PK</div>
                            <div class="ad-att-body">
                                <div class="ad-att-name">Patricia Kyomuhendo</div>
                                <div class="ad-att-sub">3 messages flagged this week</div>
                            </div>
                            <button class="ad-att-btn danger-btn" onclick="blacklistMember('Patricia Kyomuhendo')">Blacklist</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ── MEMBERS ── --}}
        <section class="ad-section" id="section-members">
            <div class="ad-section-header">
                <h1 class="ad-section-title">Members</h1>
                <span class="ad-section-sub">Manage registrations, roles and status</span>
            </div>

            {{-- Pending approvals --}}
            <div class="ad-card" style="margin-bottom:1.25rem;">
                <h3 class="ad-card-title">⏳ Pending approval <span class="ad-count-pill">3</span></h3>
                <p class="ad-card-sub">New members must agree to platform rules before being approved.</p>
                <div class="ad-pending-list">
                    <div class="ad-pending-item">
                        <div class="ad-att-avatar" style="background:#4f46e5;">RB</div>
                        <div class="ad-att-body">
                            <div class="ad-att-name">Ronald Byarugaba</div>
                            <div class="ad-att-sub">ronald@student.cit.ac.ug · Registered 2 hours ago · Rules: <strong class="text-green">Agreed ✓</strong></div>
                        </div>
                        <div class="ad-pending-actions">
                            <button class="ad-btn-approve" onclick="approveM('Ronald Byarugaba', this)">Approve</button>
                            <button class="ad-btn-decline" onclick="declineM('Ronald Byarugaba', this)">Decline</button>
                        </div>
                    </div>
                    <div class="ad-pending-item">
                        <div class="ad-att-avatar" style="background:#0891b2;">SN</div>
                        <div class="ad-att-body">
                            <div class="ad-att-name">Sharon Nakato</div>
                            <div class="ad-att-sub">sharon@student.cit.ac.ug · Registered 5 hours ago · Rules: <strong class="text-green">Agreed ✓</strong></div>
                        </div>
                        <div class="ad-pending-actions">
                            <button class="ad-btn-approve" onclick="approveM('Sharon Nakato', this)">Approve</button>
                            <button class="ad-btn-decline" onclick="declineM('Sharon Nakato', this)">Decline</button>
                        </div>
                    </div>
                    <div class="ad-pending-item">
                        <div class="ad-att-avatar" style="background:#be185d;">DK</div>
                        <div class="ad-att-body">
                            <div class="ad-att-name">David Kiggundu</div>
                            <div class="ad-att-sub">david@student.cit.ac.ug · Registered 1 day ago · Rules: <strong style="color:#ef4444;">Not agreed ✗</strong></div>
                        </div>
                        <div class="ad-pending-actions">
                            <button class="ad-btn-approve" disabled style="opacity:0.4;cursor:not-allowed;">Approve</button>
                            <button class="ad-btn-decline" onclick="declineM('David Kiggundu', this)">Decline</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- All members table --}}
            <div class="ad-card">
                <div class="ad-card-header-row">
                    <h3 class="ad-card-title">All members</h3>
                    <div class="ad-search-wrap">
                        <svg class="ad-search-icon" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
                        <input type="text" class="ad-search" placeholder="Search members…" oninput="searchMembers(this.value)">
                    </div>
                    <div class="ad-filter-chips">
                        <button class="ad-chip active" onclick="filterMembers('all',this)">All</button>
                        <button class="ad-chip" onclick="filterMembers('active',this)">Active</button>
                        <button class="ad-chip" onclick="filterMembers('warned',this)">Warned</button>
                        <button class="ad-chip" onclick="filterMembers('blacklisted',this)">Blacklisted</button>
                    </div>
                </div>
                <div class="ad-table-wrap">
                    <table class="ad-table" id="membersTable">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Role</th>
                                <th>Group</th>
                                <th>Last active</th>
                                <th>Messages</th>
                                <th>Warnings</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="membersTbody">
                            <tr data-status="active">
                                <td class="ad-td-member"><div class="ad-m-avatar" style="background:#7c3aed;">MK</div><div><div class="ad-m-name">Moses Kintu</div><div class="ad-m-email">moses@cit.ac.ug</div></div></td>
                                <td><span class="ad-role student">Student</span></td>
                                <td>Group 4</td>
                                <td>Just now</td>
                                <td>47</td>
                                <td><span class="ad-warn-count">0/2</span></td>
                                <td><span class="ad-status active">Active</span></td>
                                <td class="ad-td-acts">
                                    <button class="ad-act warn-act" onclick="sendWarning('Moses Kintu',1)" title="Warn">⚠️</button>
                                    <button class="ad-act ban-act" onclick="blacklistMember('Moses Kintu')" title="Blacklist">🚫</button>
                                </td>
                            </tr>
                            <tr data-status="active">
                                <td class="ad-td-member"><div class="ad-m-avatar" style="background:#0891b2;">JK</div><div><div class="ad-m-name">Joan Kavuma</div><div class="ad-m-email">joan@cit.ac.ug</div></div></td>
                                <td><span class="ad-role student">Student</span></td>
                                <td>Group 4</td>
                                <td>10 mins ago</td>
                                <td>32</td>
                                <td><span class="ad-warn-count">0/2</span></td>
                                <td><span class="ad-status active">Active</span></td>
                                <td class="ad-td-acts">
                                    <button class="ad-act warn-act" onclick="sendWarning('Joan Kavuma',1)" title="Warn">⚠️</button>
                                    <button class="ad-act ban-act" onclick="blacklistMember('Joan Kavuma')" title="Blacklist">🚫</button>
                                </td>
                            </tr>
                            <tr data-status="warned">
                                <td class="ad-td-member"><div class="ad-m-avatar" style="background:#f59e0b;">AT</div><div><div class="ad-m-name">Alice Tendo</div><div class="ad-m-email">alice@cit.ac.ug</div></div></td>
                                <td><span class="ad-role student">Student</span></td>
                                <td>Group 2</td>
                                <td>14 days ago</td>
                                <td>5</td>
                                <td><span class="ad-warn-count warned">1/2</span></td>
                                <td><span class="ad-status warned">Warned</span></td>
                                <td class="ad-td-acts">
                                    <button class="ad-act warn-act" onclick="sendWarning('Alice Tendo',2)" title="Send warning 2">⚠️</button>
                                    <button class="ad-act ban-act" onclick="blacklistMember('Alice Tendo')" title="Blacklist">🚫</button>
                                </td>
                            </tr>
                            <tr data-status="blacklisted">
                                <td class="ad-td-member"><div class="ad-m-avatar" style="background:#64748b;">BR</div><div><div class="ad-m-name">Brian Rackara</div><div class="ad-m-email">brian@cit.ac.ug</div></div></td>
                                <td><span class="ad-role student">Student</span></td>
                                <td>Group 1</td>
                                <td>30 days ago</td>
                                <td>1</td>
                                <td><span class="ad-warn-count danger">2/2</span></td>
                                <td><span class="ad-status blacklisted">Blacklisted</span></td>
                                <td class="ad-td-acts">
                                    <button class="ad-act unban-act" onclick="unblacklist('Brian Rackara')" title="Unblacklist">✅</button>
                                </td>
                            </tr>
                            <tr data-status="active">
                                <td class="ad-td-member"><div class="ad-m-avatar" style="background:#059669;">DO</div><div><div class="ad-m-name">Dr. Okello James</div><div class="ad-m-email">okello@cit.ac.ug</div></div></td>
                                <td><span class="ad-role lecturer">Lecturer</span></td>
                                <td>All groups</td>
                                <td>1 hour ago</td>
                                <td>18</td>
                                <td><span class="ad-warn-count">—</span></td>
                                <td><span class="ad-status active">Active</span></td>
                                <td class="ad-td-acts"><span style="color:var(--text-muted);font-size:0.75rem;">Admin</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        {{-- ── GROUPS & STATS ── --}}
        <section class="ad-section" id="section-groups">
            <div class="ad-section-header">
                <h1 class="ad-section-title">Groups &amp; Statistics</h1>
                <span class="ad-section-sub">Each group's individual performance</span>
            </div>
            <div class="ad-groups-grid" id="groupsGrid">
                <!-- rendered by JS -->
            </div>
        </section>

        {{-- ── FLAGGED CONTENT ── --}}
        <section class="ad-section" id="section-flags">
            <div class="ad-section-header">
                <h1 class="ad-section-title">Flagged Content</h1>
                <span class="ad-section-sub">Review messages reported by members</span>
            </div>
            <div class="ad-card">
                <div id="flagList"></div>
            </div>
        </section>

        {{-- ── PARTICIPATION ── --}}
        <section class="ad-section" id="section-participation">
            <div class="ad-section-header">
                <h1 class="ad-section-title">Participation Scores</h1>
                <span class="ad-section-sub">Member contribution scores across all topics</span>
            </div>
            <div class="ad-card">
                <div class="ad-card-header-row">
                    <h3 class="ad-card-title">Leaderboard</h3>
                    <select class="ad-select-sm" onchange="filterParticipation(this.value)">
                        <option value="all">All groups</option>
                        <option value="g1">Group 1</option>
                        <option value="g2">Group 2</option>
                        <option value="g3">Group 3</option>
                        <option value="g4">Group 4</option>
                    </select>
                </div>
                <div id="participationList"></div>
            </div>
        </section>

        {{-- ── BLACKLIST ── --}}
        <section class="ad-section" id="section-blacklist">
            <div class="ad-section-header">
                <h1 class="ad-section-title">Blacklist Management</h1>
                <span class="ad-section-sub">Configure auto-blacklist rules and manage banned members</span>
            </div>
            <div class="ad-grid-2">
                <div class="ad-card">
                    <h3 class="ad-card-title">Auto-blacklist rules</h3>
                    <p class="ad-card-sub">Members inactive beyond the threshold receive warnings automatically.</p>
                    <div class="ad-settings-form">
                        <div class="ad-setting-row">
                            <div><div class="ad-setting-label">Inactivity threshold (days)</div><div class="ad-setting-sub">Days before first warning is sent</div></div>
                            <input type="number" class="ad-input-sm" value="7" min="1">
                        </div>
                        <div class="ad-setting-row">
                            <div><div class="ad-setting-label">Time between warnings (days)</div><div class="ad-setting-sub">Gap between warning 1 and warning 2</div></div>
                            <input type="number" class="ad-input-sm" value="7" min="1">
                        </div>
                        <div class="ad-setting-row">
                            <div><div class="ad-setting-label">Blacklist duration (days)</div><div class="ad-setting-sub">How long a member stays blacklisted</div></div>
                            <input type="number" class="ad-input-sm" value="30" min="1">
                        </div>
                        <div class="ad-setting-row">
                            <div><div class="ad-setting-label">Auto-blacklist enabled</div><div class="ad-setting-sub">Automatically blacklist after 2 warnings</div></div>
                            <label class="ad-toggle"><input type="checkbox" checked><span class="ad-toggle-track"><span class="ad-toggle-thumb"></span></span></label>
                        </div>
                        <button class="ad-btn-save" onclick="showToast('Blacklist rules saved!','success')">Save rules</button>
                    </div>
                </div>
                <div class="ad-card">
                    <h3 class="ad-card-title">Currently blacklisted</h3>
                    <div id="blacklistItems"></div>
                </div>
            </div>
        </section>

        {{-- ── SETTINGS ── --}}
        <section class="ad-section" id="section-settings">
            <div class="ad-section-header">
                <h1 class="ad-section-title">Platform Settings</h1>
                <span class="ad-section-sub">Configure platform-wide rules</span>
            </div>
            <div class="ad-grid-2">
                <div class="ad-card">
                    <h3 class="ad-card-title">Onboarding rules</h3>
                    <p class="ad-card-sub">This text is shown to every new member during registration. They must agree before being registered.</p>
                    <textarea class="ad-textarea" rows="8" id="rulesText">Welcome to the Software Discussion Forum (SDF)!

By joining, you agree to:
1. Post only relevant content related to your course topics.
2. Treat all members with respect.
3. Remain active — members inactive for 7+ days will receive warnings.
4. Not share private communications without consent.
5. Violations may result in blacklisting.

The administrators reserve the right to remove any member who violates these rules.</textarea>
                    <button class="ad-btn-save" style="margin-top:0.75rem;" onclick="showToast('Rules updated and saved!','success')">Save rules</button>
                </div>
                <div class="ad-card">
                    <h3 class="ad-card-title">Participation mark criteria</h3>
                    <p class="ad-card-sub">Points awarded to students for discussion contributions.</p>
                    <div class="ad-settings-form">
                        <div class="ad-setting-row">
                            <div><div class="ad-setting-label">Points per message posted</div></div>
                            <input type="number" class="ad-input-sm" value="2" min="0">
                        </div>
                        <div class="ad-setting-row">
                            <div><div class="ad-setting-label">Points per reply given</div></div>
                            <input type="number" class="ad-input-sm" value="3" min="0">
                        </div>
                        <div class="ad-setting-row">
                            <div><div class="ad-setting-label">Points per topic created</div></div>
                            <input type="number" class="ad-input-sm" value="5" min="0">
                        </div>
                        <div class="ad-setting-row">
                            <div><div class="ad-setting-label">Max participation marks</div></div>
                            <input type="number" class="ad-input-sm" value="20" min="0">
                        </div>
                        <button class="ad-btn-save" onclick="showToast('Criteria saved!','success')">Save criteria</button>
                    </div>
                </div>
            </div>
        </section>

    </div>
</div>

{{-- Confirm modal --}}
<div class="ad-overlay" id="adConfirmOverlay">
    <div class="ad-modal">
        <div class="ad-modal-icon" id="adModalIcon"></div>
        <h3 class="ad-modal-title" id="adModalTitle"></h3>
        <p class="ad-modal-body" id="adModalBody"></p>
        <div class="ad-modal-actions">
            <button class="qm-btn-ghost" onclick="closeAdModal()">Cancel</button>
            <button class="ad-btn-confirm" id="adModalConfirm">Confirm</button>
        </div>
    </div>
</div>

<div class="ad-toast" id="adToast"></div>

@endsection

@push('scripts')
<script>
// ── Section navigation ─────────────────────────────────
function showSection(id, btn) {
    document.querySelectorAll('.ad-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.ad-nav-item').forEach(b => b.classList.remove('active'));
    document.getElementById('section-' + id).classList.add('active');
    btn.classList.add('active');
}

// ── Groups data ────────────────────────────────────────
const groups = [
    { name:'Group 1', course:'CIT 4100', members:28, messages:412, topics:15, avgPart:14, topMember:'Sarah Nakabuye', active:24 },
    { name:'Group 2', course:'CIT 4201', members:31, messages:389, topics:12, avgPart:11, topMember:'David Ochieng', active:19 },
    { name:'Group 3', course:'CIT 4302', members:22, messages:201, topics:8,  avgPart:9,  topMember:'Alice Tendo', active:14 },
    { name:'Group 4', course:'CIT 4203', members:43, messages:687, topics:22, avgPart:17, topMember:'Moses Kintu', active:38 },
];

function renderGroups() {
    const grid = document.getElementById('groupsGrid');
    grid.innerHTML = groups.map((g, i) => `
        <div class="ad-group-card">
            <div class="ad-group-header">
                <div>
                    <div class="ad-group-name">${g.name}</div>
                    <div class="ad-group-course">${g.course}</div>
                </div>
                <div class="ad-group-active">${g.active} online</div>
            </div>
            <div class="ad-group-stats">
                <div class="ad-gs"><span class="ad-gs-num">${g.members}</span><span class="ad-gs-label">Members</span></div>
                <div class="ad-gs"><span class="ad-gs-num">${g.messages}</span><span class="ad-gs-label">Messages</span></div>
                <div class="ad-gs"><span class="ad-gs-num">${g.topics}</span><span class="ad-gs-label">Topics</span></div>
                <div class="ad-gs"><span class="ad-gs-num">${g.avgPart}</span><span class="ad-gs-label">Avg pts</span></div>
            </div>
            <div class="ad-group-bar-wrap">
                <div class="ad-group-bar-label">Activity level</div>
                <div class="ad-group-bar-track"><div class="ad-group-bar-fill" style="width:${Math.min(g.messages/7,100)}%"></div></div>
            </div>
            <div class="ad-group-top">🏆 Top contributor: <strong>${g.topMember}</strong></div>
        </div>`).join('');
}

// ── Flagged content ────────────────────────────────────
const flags = [
    { id:1, reporter:'Joan Kavuma', author:'Unknown Member', text:'Check out this free iPhone giveaway link!!!', reason:'Spam / flooding', topic:'System architecture Q&A', time:'15 mins ago' },
    { id:2, reporter:'Moses Kintu', author:'David Ochieng', text:'This topic is boring, nobody cares about OOP', reason:'Irrelevant to topic', topic:'OOP Discussion', time:'1 hour ago' },
    { id:3, reporter:'Sarah Nakabuye', author:'Alice Tendo', text:'Has anyone done assignment 3? Send me answers plz', reason:'Irrelevant to topic', topic:'ERD feedback', time:'2 hours ago' },
    { id:4, reporter:'Dr. Okello', author:'Unknown', text:'[External link to non-academic site]', reason:'Spam / flooding', topic:'Laravel sync', time:'Yesterday' },
    { id:5, reporter:'Brian R.', author:'Patricia K.', text:'Why do we even study this? Total waste of time', reason:'Offensive content', topic:'Docker containerisation', time:'Yesterday' },
];

function renderFlags() {
    const list = document.getElementById('flagList');
    list.innerHTML = flags.map(f => `
        <div class="ad-flag-item" id="flag-${f.id}">
            <div class="ad-flag-header">
                <span class="ad-flag-reason">🚩 ${f.reason}</span>
                <span class="ad-flag-topic">in: ${f.topic}</span>
                <span class="ad-flag-time">${f.time}</span>
            </div>
            <div class="ad-flag-msg">"${f.text}"</div>
            <div class="ad-flag-meta">Reported by <strong>${f.reporter}</strong> · Author: <strong>${f.author}</strong></div>
            <div class="ad-flag-actions">
                <button class="ad-btn-dismiss" onclick="dismissFlag(${f.id})">Dismiss — keep message</button>
                <button class="ad-btn-remove" onclick="removeFlag(${f.id})">Remove message</button>
                <button class="ad-btn-warn-author" onclick="showToast('Warning sent to ${f.author}','amber')">Warn author</button>
            </div>
        </div>`).join('');
}

function dismissFlag(id) {
    document.getElementById('flag-' + id).remove();
    showToast('Flag dismissed.', 'success');
    updateFlagBadge();
}
function removeFlag(id) {
    document.getElementById('flag-' + id).remove();
    showToast('Message removed and author notified.', 'success');
    updateFlagBadge();
}
function updateFlagBadge() {
    const remaining = document.querySelectorAll('[id^="flag-"]').length;
    document.getElementById('flagBadge').textContent = remaining;
}

// ── Participation leaderboard ──────────────────────────
const participants = [
    { name:'Moses Kintu',    group:'Group 4', msgs:47, replies:23, topics:5, pts:174 },
    { name:'Joan Kavuma',    group:'Group 4', msgs:32, replies:18, topics:3, pts:119 },
    { name:'Sarah Nakabuye', group:'Group 1', msgs:28, replies:15, topics:4, pts:101 },
    { name:'David Ochieng',  group:'Group 2', msgs:21, replies:11, topics:2, pts:72  },
    { name:'Alice Tendo',    group:'Group 2', msgs:5,  replies:2,  topics:1, pts:16  },
    { name:'Brian Rackara',  group:'Group 1', msgs:1,  replies:0,  topics:0, pts:2   },
];

function filterParticipation(group) { renderParticipation(); }

function renderParticipation() {
    const list = document.getElementById('participationList');
    const max  = Math.max(...participants.map(p => p.pts));
    list.innerHTML = participants.map((p, i) => `
        <div class="ad-part-row">
            <span class="ad-part-rank ${i===0?'gold':i===1?'silver':i===2?'bronze':''}">${i+1}</span>
            <div class="ad-part-avatar">${p.name.split(' ').map(n=>n[0]).join('').slice(0,2)}</div>
            <div class="ad-part-info">
                <div class="ad-part-name">${p.name}</div>
                <div class="ad-part-group">${p.group} · ${p.msgs} msgs · ${p.replies} replies · ${p.topics} topics</div>
            </div>
            <div class="ad-part-bar-wrap">
                <div class="ad-part-bar-track">
                    <div class="ad-part-bar-fill ${i<3?'top':''}" style="width:${(p.pts/max*100).toFixed(0)}%"></div>
                </div>
            </div>
            <span class="ad-part-pts">${p.pts} pts</span>
        </div>`).join('');
}

// ── Blacklist section ──────────────────────────────────
const blacklisted = [
    { name:'Brian Rackara', email:'brian@cit.ac.ug', since:'2026-05-29', until:'2026-06-28', reason:'Inactive 30 days — 2 warnings ignored' },
    { name:'Patricia Kyomuhendo', email:'patk@cit.ac.ug', since:'2026-06-01', until:'2026-07-01', reason:'Repeated flagged messages' },
];

function renderBlacklist() {
    const el = document.getElementById('blacklistItems');
    if (!blacklisted.length) { el.innerHTML = '<div class="ad-empty-sm">No members currently blacklisted.</div>'; return; }
    el.innerHTML = blacklisted.map(b => `
        <div class="ad-bl-item">
            <div class="ad-bl-avatar">${b.name.split(' ').map(n=>n[0]).join('').slice(0,2)}</div>
            <div class="ad-bl-body">
                <div class="ad-bl-name">${b.name}</div>
                <div class="ad-bl-reason">${b.reason}</div>
                <div class="ad-bl-dates">Blacklisted: ${b.since} → Expires: ${b.until}</div>
            </div>
            <button class="ad-btn-approve" onclick="unblacklist('${b.name}')">Lift ban</button>
        </div>`).join('');
}

// ── Member actions ─────────────────────────────────────
function approveM(name, btn) {
    btn.closest('.ad-pending-item').style.opacity = '0.4';
    btn.closest('.ad-pending-item').style.pointerEvents = 'none';
    showToast(`${name} approved and notified by email.`, 'success');
    const badge = document.getElementById('pendingBadge');
    badge.textContent = Math.max(0, parseInt(badge.textContent) - 1);
}
function declineM(name, btn) {
    btn.closest('.ad-pending-item').remove();
    showToast(`${name} declined.`, 'amber');
}
function sendWarning(name, num) {
    showToast(`Warning ${num} sent to ${name}. They have 7 days to respond.`, 'amber');
}
function blacklistMember(name) {
    showToast(`${name} has been blacklisted for 30 days.`, 'error');
}
function unblacklist(name) {
    showToast(`${name}'s ban lifted. They can now access the platform.`, 'success');
}

// ── Member search/filter ───────────────────────────────
function searchMembers(q) {
    q = q.toLowerCase();
    document.querySelectorAll('#membersTbody tr').forEach(row => {
        const name = row.querySelector('.ad-m-name')?.textContent.toLowerCase() || '';
        row.style.display = name.includes(q) ? '' : 'none';
    });
}
function filterMembers(status, btn) {
    document.querySelectorAll('.ad-chip').forEach(c => c.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('#membersTbody tr').forEach(row => {
        row.style.display = status === 'all' || row.dataset.status === status ? '' : 'none';
    });
}

// ── Modal ──────────────────────────────────────────────
function closeAdModal() { document.getElementById('adConfirmOverlay').style.display = 'none'; }

// ── Toast ──────────────────────────────────────────────
function showToast(msg, type='success') {
    const colors = { success:'#059669', amber:'#d97706', error:'#ef4444' };
    const t = document.getElementById('adToast');
    t.textContent = msg;
    t.style.background = colors[type] || colors.success;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3500);
}

// Init
renderGroups();
renderFlags();
renderParticipation();
renderBlacklist();
</script>
@endpush

<style>
:root { --brand:#4f46e5; --brand-dark:#4338ca; --surface:#fff; --bg:#f1f5f9; --text-primary:#1e293b; --text-muted:#64748b; --border:#e2e8f0; --emerald:#10b981; --amber:#f59e0b; --rose:#ef4444; --nav-h:64px; }

/* ══ SHELL ══ */
.ad-shell { display:grid; grid-template-columns:220px 1fr; height:calc(100vh - var(--nav-h)); overflow:hidden; }

/* ══ SIDEBAR ══ */
.ad-sidebar { background:#0f172a; display:flex; flex-direction:column; overflow-y:auto; }
.ad-sidebar-brand { display:flex; align-items:center; gap:0.75rem; padding:1.25rem 1rem; border-bottom:1px solid rgba(255,255,255,0.08); }
.ad-sidebar-icon { width:36px; height:36px; background:var(--brand); border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:0.8rem; font-weight:800; color:white; flex-shrink:0; }
.ad-sidebar-title { font-size:0.85rem; font-weight:700; color:white; }
.ad-sidebar-sub { font-size:0.72rem; color:rgba(255,255,255,0.45); margin-top:0.1rem; }
.ad-nav { padding:0.75rem 0.6rem; flex:1; display:flex; flex-direction:column; gap:0.2rem; }
.ad-nav-item { display:flex; align-items:center; gap:0.65rem; padding:0.6rem 0.75rem; border-radius:9px; background:none; border:none; color:rgba(255,255,255,0.55); font-size:0.83rem; font-family:'Inter',sans-serif; font-weight:500; cursor:pointer; transition:all 0.18s; text-align:left; width:100%; }
.ad-nav-item:hover { background:rgba(255,255,255,0.07); color:rgba(255,255,255,0.85); }
.ad-nav-item.active { background:rgba(79,70,229,0.35); color:white; }
.ad-nav-badge { margin-left:auto; font-size:0.65rem; font-weight:700; padding:1px 6px; border-radius:999px; background:#f59e0b; color:#451a03; }
.ad-nav-badge.danger { background:#ef4444; color:white; }
.ad-logout-btn { width:100%; display:flex; align-items:center; gap:0.5rem; padding:0.55rem 0.75rem; background:rgba(239,68,68,0.12); border:1px solid rgba(239,68,68,0.25); border-radius:9px; color:rgba(255,255,255,0.7); font-size:0.82rem; font-family:'Inter',sans-serif; font-weight:500; cursor:pointer; transition:background 0.18s; }
.ad-logout-btn:hover { background:rgba(239,68,68,0.25); color:white; }

/* ══ MAIN ══ */
.ad-main { overflow-y:auto; background:var(--bg); padding:1.5rem; }
.ad-main::-webkit-scrollbar { width:4px; }
.ad-main::-webkit-scrollbar-thumb { background:var(--border); border-radius:4px; }

/* ══ SECTIONS ══ */
.ad-section { display:none; }
.ad-section.active { display:block; }
.ad-section-header { margin-bottom:1.25rem; }
.ad-section-title { font-size:1.35rem; font-weight:700; color:var(--text-primary); }
.ad-section-sub { font-size:0.82rem; color:var(--text-muted); margin-top:0.2rem; display:block; }

/* Stats grid */
.ad-stats-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; margin-bottom:1.25rem; }
@media(max-width:900px){ .ad-stats-grid { grid-template-columns:1fr 1fr; } }
.ad-stat-card { background:white; border-radius:12px; padding:1.1rem; display:flex; flex-direction:column; gap:0.3rem; border:1px solid var(--border); transition:box-shadow 0.18s; }
.ad-stat-card:hover { box-shadow:0 4px 16px rgba(0,0,0,0.08); }
.ad-stat-icon { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; margin-bottom:0.3rem; }
.ad-stat-num { font-size:1.7rem; font-weight:800; color:var(--text-primary); line-height:1; }
.ad-stat-label { font-size:0.78rem; color:var(--text-muted); font-weight:500; }
.ad-stat-trend { font-size:0.72rem; font-weight:600; margin-top:0.2rem; }
.ad-stat-trend.up { color:#059669; }
.ad-stat-trend.warn { color:#d97706; }
.ad-stat-trend.danger { color:#dc2626; }
.ad-stat-trend.muted { color:var(--text-muted); }

/* Cards */
.ad-card { background:white; border:1px solid var(--border); border-radius:14px; padding:1.3rem 1.4rem; margin-bottom:1rem; }
.ad-card-title { font-size:0.95rem; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:0.5rem; }
.ad-card-sub { font-size:0.78rem; color:var(--text-muted); margin:0.3rem 0 0.85rem; }
.ad-card-header-row { display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap; margin-bottom:1rem; }
.ad-count-pill { font-size:0.7rem; font-weight:700; background:#fef3c7; color:#92400e; padding:2px 8px; border-radius:999px; }
.text-green { color:#059669; }
.ad-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:1.1rem; }
@media(max-width:900px){ .ad-grid-2 { grid-template-columns:1fr; } }

/* Activity */
.ad-activity-list { display:flex; flex-direction:column; gap:0.6rem; }
.ad-activity-item { display:flex; align-items:center; gap:0.75rem; padding:0.5rem 0; border-bottom:1px solid var(--border); }
.ad-activity-item:last-child { border-bottom:none; }
.ad-act-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
.ad-act-body { flex:1; display:flex; justify-content:space-between; align-items:center; gap:0.5rem; flex-wrap:wrap; }
.ad-act-text { font-size:0.82rem; color:var(--text-primary); }
.ad-act-time { font-size:0.72rem; color:var(--text-muted); white-space:nowrap; }

/* Attention list */
.ad-attention-list { display:flex; flex-direction:column; gap:0.7rem; }
.ad-attention-item { display:flex; align-items:center; gap:0.75rem; padding:0.75rem; border-radius:10px; }
.ad-attention-item.warn   { background:#fefce8; border:1px solid #fde68a; }
.ad-attention-item.danger { background:#fff1f2; border:1px solid #fecaca; }
.ad-att-avatar { width:34px; height:34px; border-radius:50%; color:white; font-size:0.72rem; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.ad-att-body { flex:1; }
.ad-att-name { font-size:0.875rem; font-weight:600; color:var(--text-primary); }
.ad-att-sub { font-size:0.75rem; color:var(--text-muted); margin-top:0.1rem; }
.ad-att-btn { padding:0.35rem 0.75rem; border-radius:7px; font-size:0.78rem; font-family:'Inter',sans-serif; font-weight:600; border:none; cursor:pointer; white-space:nowrap; }
.warn-btn   { background:#fef3c7; color:#92400e; }
.danger-btn { background:#fee2e2; color:#991b1b; }

/* Pending */
.ad-pending-list { display:flex; flex-direction:column; gap:0.75rem; }
.ad-pending-item { display:flex; align-items:center; gap:0.75rem; padding:0.85rem; background:#f8fafc; border-radius:10px; border:1px solid var(--border); flex-wrap:wrap; }
.ad-pending-actions { display:flex; gap:0.5rem; margin-left:auto; }
.ad-btn-approve { padding:0.4rem 0.9rem; background:#dcfce7; color:#166534; border:none; border-radius:7px; font-size:0.8rem; font-family:'Inter',sans-serif; font-weight:600; cursor:pointer; transition:background 0.18s; }
.ad-btn-approve:hover { background:#bbf7d0; }
.ad-btn-decline { padding:0.4rem 0.9rem; background:#fee2e2; color:#991b1b; border:none; border-radius:7px; font-size:0.8rem; font-family:'Inter',sans-serif; font-weight:600; cursor:pointer; }

/* Table */
.ad-table-wrap { overflow-x:auto; }
.ad-table { width:100%; border-collapse:collapse; font-size:0.83rem; min-width:700px; }
.ad-table thead th { padding:0.55rem 0.85rem; text-align:left; font-size:0.7rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em; background:#f8fafc; border-bottom:1.5px solid var(--border); }
.ad-table tbody tr { border-bottom:1px solid var(--border); transition:background 0.12s; }
.ad-table tbody tr:last-child { border-bottom:none; }
.ad-table tbody tr:hover { background:#f8fafc; }
.ad-table td { padding:0.75rem 0.85rem; vertical-align:middle; }
.ad-td-member { display:flex; align-items:center; gap:0.65rem; min-width:160px; }
.ad-m-avatar { width:30px; height:30px; border-radius:50%; color:white; font-size:0.67rem; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.ad-m-name { font-weight:600; font-size:0.83rem; }
.ad-m-email { font-size:0.72rem; color:var(--text-muted); }
.ad-role { font-size:0.7rem; font-weight:700; padding:2px 8px; border-radius:999px; }
.ad-role.student  { background:#eef2ff; color:#3730a3; }
.ad-role.lecturer { background:#f0fdf4; color:#166534; }
.ad-warn-count { font-size:0.78rem; font-weight:700; color:var(--text-muted); }
.ad-warn-count.warned { color:#d97706; }
.ad-warn-count.danger { color:#dc2626; }
.ad-status { font-size:0.72rem; font-weight:700; padding:2px 8px; border-radius:999px; }
.ad-status.active      { background:#dcfce7; color:#166534; }
.ad-status.warned      { background:#fef3c7; color:#92400e; }
.ad-status.blacklisted { background:#fee2e2; color:#991b1b; }
.ad-td-acts { white-space:nowrap; }
.ad-act { width:28px; height:28px; border:none; background:none; cursor:pointer; border-radius:6px; font-size:0.9rem; transition:background 0.15s; }
.ad-act:hover { background:var(--border); }

/* Search/filter */
.ad-search-wrap { position:relative; }
.ad-search-icon { position:absolute; left:0.65rem; top:50%; transform:translateY(-50%); color:var(--text-muted); pointer-events:none; }
.ad-search { padding:0.45rem 0.75rem 0.45rem 1.9rem; border:1.5px solid var(--border); border-radius:8px; font-size:0.82rem; font-family:'Inter',sans-serif; width:180px; }
.ad-search:focus { outline:none; border-color:var(--brand); }
.ad-filter-chips { display:flex; gap:0.3rem; flex-wrap:wrap; }
.ad-chip { padding:0.25rem 0.65rem; border-radius:999px; border:1.5px solid var(--border); background:white; font-size:0.75rem; font-family:'Inter',sans-serif; font-weight:500; color:var(--text-muted); cursor:pointer; transition:all 0.18s; }
.ad-chip:hover { border-color:var(--brand); color:var(--brand); }
.ad-chip.active { background:var(--brand); border-color:var(--brand); color:white; }

/* Groups */
.ad-groups-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:1.1rem; }
@media(max-width:900px){ .ad-groups-grid { grid-template-columns:1fr; } }
.ad-group-card { background:white; border:1px solid var(--border); border-radius:14px; padding:1.2rem 1.3rem; }
.ad-group-header { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:1rem; }
.ad-group-name { font-size:1rem; font-weight:700; color:var(--text-primary); }
.ad-group-course { font-size:0.75rem; color:var(--text-muted); margin-top:0.15rem; }
.ad-group-active { font-size:0.75rem; font-weight:600; color:var(--emerald); background:#dcfce7; padding:3px 9px; border-radius:999px; white-space:nowrap; }
.ad-group-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:0.5rem; margin-bottom:0.85rem; }
.ad-gs { text-align:center; padding:0.5rem; background:#f8fafc; border-radius:8px; }
.ad-gs-num { display:block; font-size:1.1rem; font-weight:700; color:var(--brand); }
.ad-gs-label { font-size:0.65rem; color:var(--text-muted); font-weight:500; }
.ad-group-bar-wrap { margin-bottom:0.65rem; }
.ad-group-bar-label { font-size:0.7rem; color:var(--text-muted); font-weight:600; margin-bottom:0.3rem; }
.ad-group-bar-track { height:6px; background:#f1f5f9; border-radius:999px; overflow:hidden; }
.ad-group-bar-fill { height:100%; background:linear-gradient(90deg,#4f46e5,#818cf8); border-radius:999px; }
.ad-group-top { font-size:0.78rem; color:var(--text-muted); }

/* Flags */
.ad-flag-item { padding:1rem; border-bottom:1px solid var(--border); }
.ad-flag-item:last-child { border-bottom:none; }
.ad-flag-header { display:flex; align-items:center; gap:0.75rem; margin-bottom:0.5rem; flex-wrap:wrap; }
.ad-flag-reason { font-size:0.78rem; font-weight:700; color:#dc2626; background:#fee2e2; padding:2px 8px; border-radius:999px; }
.ad-flag-topic { font-size:0.75rem; color:var(--text-muted); }
.ad-flag-time { font-size:0.72rem; color:var(--text-muted); margin-left:auto; }
.ad-flag-msg { font-size:0.875rem; color:var(--text-primary); font-style:italic; background:#f8fafc; border-left:3px solid #ef4444; padding:0.5rem 0.75rem; border-radius:0 6px 6px 0; margin-bottom:0.5rem; }
.ad-flag-meta { font-size:0.75rem; color:var(--text-muted); margin-bottom:0.65rem; }
.ad-flag-actions { display:flex; gap:0.5rem; flex-wrap:wrap; }
.ad-btn-dismiss    { padding:0.35rem 0.8rem; background:#f1f5f9; border:1.5px solid var(--border); border-radius:7px; font-size:0.78rem; font-family:'Inter',sans-serif; font-weight:600; color:var(--text-muted); cursor:pointer; }
.ad-btn-remove     { padding:0.35rem 0.8rem; background:#fee2e2; border:none; border-radius:7px; font-size:0.78rem; font-family:'Inter',sans-serif; font-weight:600; color:#991b1b; cursor:pointer; }
.ad-btn-warn-author{ padding:0.35rem 0.8rem; background:#fef3c7; border:none; border-radius:7px; font-size:0.78rem; font-family:'Inter',sans-serif; font-weight:600; color:#92400e; cursor:pointer; }

/* Participation */
.ad-part-row { display:flex; align-items:center; gap:0.75rem; padding:0.7rem 0; border-bottom:1px solid var(--border); }
.ad-part-row:last-child { border-bottom:none; }
.ad-part-rank { width:24px; height:24px; border-radius:50%; background:#f1f5f9; font-size:0.75rem; font-weight:700; color:var(--text-muted); display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.ad-part-rank.gold   { background:#fef3c7; color:#92400e; }
.ad-part-rank.silver { background:#f1f5f9; color:#475569; }
.ad-part-rank.bronze { background:#fef2e4; color:#9a3412; }
.ad-part-avatar { width:30px; height:30px; border-radius:50%; background:linear-gradient(135deg,#4f46e5,#6366f1); color:white; font-size:0.67rem; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.ad-part-info { min-width:140px; }
.ad-part-name { font-size:0.85rem; font-weight:600; color:var(--text-primary); }
.ad-part-group { font-size:0.72rem; color:var(--text-muted); }
.ad-part-bar-wrap { flex:1; }
.ad-part-bar-track { height:7px; background:#f1f5f9; border-radius:999px; overflow:hidden; }
.ad-part-bar-fill { height:100%; background:#e2e8f0; border-radius:999px; transition:width 0.5s ease; }
.ad-part-bar-fill.top { background:linear-gradient(90deg,#4f46e5,#818cf8); }
.ad-part-pts { font-size:0.85rem; font-weight:700; color:var(--brand); white-space:nowrap; }

/* Blacklist */
.ad-bl-item { display:flex; align-items:center; gap:0.75rem; padding:0.85rem 0; border-bottom:1px solid var(--border); }
.ad-bl-item:last-child { border-bottom:none; }
.ad-bl-avatar { width:34px; height:34px; border-radius:50%; background:#64748b; color:white; font-size:0.72rem; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.ad-bl-body { flex:1; }
.ad-bl-name { font-size:0.875rem; font-weight:600; }
.ad-bl-reason { font-size:0.75rem; color:var(--text-muted); margin-top:0.1rem; }
.ad-bl-dates { font-size:0.72rem; color:#dc2626; margin-top:0.1rem; }

/* Settings */
.ad-settings-form { display:flex; flex-direction:column; gap:0; }
.ad-setting-row { display:flex; align-items:center; justify-content:space-between; padding:0.85rem 0; border-bottom:1px solid var(--border); gap:1rem; }
.ad-setting-row:last-of-type { border-bottom:none; }
.ad-setting-label { font-size:0.875rem; font-weight:600; color:var(--text-primary); }
.ad-setting-sub { font-size:0.75rem; color:var(--text-muted); margin-top:0.15rem; }
.ad-input-sm { width:70px; padding:0.4rem 0.6rem; border:1.5px solid var(--border); border-radius:7px; font-size:0.875rem; font-family:'Inter',sans-serif; text-align:center; }
.ad-input-sm:focus { outline:none; border-color:var(--brand); }
.ad-textarea { width:100%; padding:0.75rem; border:1.5px solid var(--border); border-radius:8px; font-size:0.875rem; font-family:'Inter',sans-serif; line-height:1.6; resize:vertical; }
.ad-textarea:focus { outline:none; border-color:var(--brand); }
.ad-select-sm { padding:0.4rem 0.7rem; border:1.5px solid var(--border); border-radius:7px; font-size:0.82rem; font-family:'Inter',sans-serif; background:white; }
.ad-btn-save { margin-top:1rem; padding:0.6rem 1.3rem; background:linear-gradient(135deg,#4f46e5,#6366f1); color:white; border:none; border-radius:8px; font-size:0.875rem; font-family:'Inter',sans-serif; font-weight:600; cursor:pointer; transition:opacity 0.18s; }
.ad-btn-save:hover { opacity:0.9; }

/* Toggle */
.ad-toggle { position:relative; cursor:pointer; flex-shrink:0; }
.ad-toggle input { opacity:0; width:0; height:0; position:absolute; }
.ad-toggle-track { display:block; width:40px; height:22px; background:var(--border); border-radius:999px; transition:background 0.2s; position:relative; }
.ad-toggle input:checked + .ad-toggle-track { background:var(--brand); }
.ad-toggle-thumb { position:absolute; top:3px; left:3px; width:16px; height:16px; background:white; border-radius:50%; transition:left 0.2s; box-shadow:0 1px 3px rgba(0,0,0,0.2); }
.ad-toggle input:checked + .ad-toggle-track .ad-toggle-thumb { left:21px; }

/* Modal */
.ad-overlay { position:fixed; inset:0; background:rgba(15,23,42,0.45); backdrop-filter:blur(3px); display:none; align-items:center; justify-content:center; z-index:9999; }
.ad-modal { background:white; border-radius:16px; padding:2rem; max-width:400px; width:90%; text-align:center; box-shadow:0 20px 60px rgba(0,0,0,0.2); }
.ad-modal-icon { width:52px; height:52px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1rem; }
.ad-modal-title { font-size:1rem; font-weight:700; margin-bottom:0.5rem; }
.ad-modal-body { font-size:0.875rem; color:var(--text-muted); margin-bottom:1.25rem; }
.ad-modal-actions { display:flex; justify-content:center; gap:0.75rem; }
.ad-btn-confirm { padding:0.55rem 1.2rem; background:linear-gradient(135deg,#4f46e5,#6366f1); border:none; border-radius:8px; font-size:0.875rem; font-family:'Inter',sans-serif; font-weight:600; color:white; cursor:pointer; }
.qm-btn-ghost { padding:0.55rem 1.1rem; background:none; border:1.5px solid var(--border); border-radius:8px; font-size:0.875rem; font-family:'Inter',sans-serif; font-weight:500; color:var(--text-muted); cursor:pointer; }
.ad-empty-sm { text-align:center; padding:1.5rem; color:var(--text-muted); font-size:0.875rem; }

/* Toast */
.ad-toast { position:fixed; bottom:1.5rem; right:1.5rem; color:white; font-size:0.875rem; font-weight:500; font-family:'Inter',sans-serif; padding:0.75rem 1.2rem; border-radius:10px; box-shadow:0 4px 20px rgba(0,0,0,0.18); opacity:0; transform:translateY(10px); transition:opacity 0.3s,transform 0.3s; z-index:99999; pointer-events:none; }
.ad-toast.show { opacity:1; transform:translateY(0); }

@media(max-width:768px){ .ad-shell { grid-template-columns:1fr; } .ad-sidebar { display:none; } }
</style>
