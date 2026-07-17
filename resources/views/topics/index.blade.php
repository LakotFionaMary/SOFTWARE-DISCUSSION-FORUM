@extends('layouts.app')

@section('title', 'Group Topics')

@section('content')
<div class="eyebrow">Discussion Group</div>
<h1 id="groupName">Loading…</h1>

<div class="card">
    <h3>Start a new topic</h3>
    <form id="newTopicForm">
        <input type="text" id="topicTitle" placeholder="What do you want to discuss?" required>
        <button class="btn" type="submit">Launch topic</button>
    </form>
</div>

<div class="card" style="margin-top: 12px;">
    <div style="display:flex; gap:10px; flex-wrap:wrap;">
        <div style="position:relative; flex:1; min-width:180px;">
            <input type="text" id="topicSearch" placeholder="Search topics…" style="width:100%; padding:8px 40px 8px 8px; box-sizing:border-box;">
            <button type="button" id="searchBtn" aria-label="Search" style="position:absolute; right:4px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; font-size:18px; padding:6px;">🔍</button>
        </div>
        <select id="categoryFilter" style="min-width:170px; padding:8px;">
            <option value="">All categories</option>
        </select>
    </div>
</div>

<div id="topics"></div>

<div style="text-align:center; margin: 14px 0;">
    <button class="btn secondary" id="loadMoreBtn" type="button" style="display:none;">Load more</button>
</div>

<button id="backToTopBtn" type="button" style="display:none; position:fixed; bottom:22px; right:18px; width:48px; height:48px; border-radius:50%; border:none; background:#1f6f5c; color:#fff; font-size:20px; box-shadow:0 4px 12px rgba(0,0,0,0.25); z-index:500;">↑</button>
@endsection

@section('scripts')
<script>
const groupId = {{ $group }};

let currentPage = 1;
let currentSearch = '';
let currentCategory = '';

async function loadGroup() {
    const g = await api(`/groups/${groupId}`);
    document.getElementById('groupName').textContent = g.name;
}

async function loadCategories() {
    const cats = await api(`/groups/${groupId}/topics/categories`) || [];
    const select = document.getElementById('categoryFilter');
    const previousValue = select.value;
    select.innerHTML = '<option value="">All categories</option>' +
        cats.map(c => `<option value="${c}">${c}</option>`).join('');
    select.value = previousValue;
}

function topicCardHtml(t) {
    return `
        <div class="card">
            <strong><a href="/topics/${t.topic_id}">${t.title}</a></strong>
            <div class="muted">${t.category ?? 'General'} · ${t.posts_count ?? 0} posts</div>
        </div>
    `;
}

async function loadTopics(reset = true) {
    if (reset) {
        currentPage = 1;
        document.getElementById('topics').innerHTML = '';
    }

    const params = new URLSearchParams({ page: currentPage });
    if (currentSearch) params.set('search', currentSearch);
    if (currentCategory) params.set('category', currentCategory);

    const data = await api(`/groups/${groupId}/topics?${params.toString()}`);
    const items = (data && data.data) || [];
    const container = document.getElementById('topics');

    if (reset && items.length === 0) {
        container.innerHTML = '<div class="muted">No topics match your search.</div>';
    } else {
        container.insertAdjacentHTML('beforeend', items.map(topicCardHtml).join(''));
    }

    const hasMore = !!(data && data.next_page_url);
    document.getElementById('loadMoreBtn').style.display = hasMore ? 'inline-block' : 'none';
}

let searchDebounce;
document.getElementById('topicSearch').addEventListener('input', (e) => {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(() => {
        currentSearch = e.target.value.trim();
        loadTopics(true);
    }, 300);
});

document.getElementById('topicSearch').addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
        clearTimeout(searchDebounce);
        currentSearch = e.target.value.trim();
        loadTopics(true);
    }
});

document.getElementById('searchBtn').addEventListener('click', () => {
    clearTimeout(searchDebounce);
    currentSearch = document.getElementById('topicSearch').value.trim();
    loadTopics(true);
});

document.getElementById('categoryFilter').addEventListener('change', (e) => {
    currentCategory = e.target.value;
    loadTopics(true);
});

document.getElementById('loadMoreBtn').addEventListener('click', () => {
    currentPage++;
    loadTopics(false);
});

document.getElementById('newTopicForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    await api(`/groups/${groupId}/topics`, {
        method: 'POST',
        body: { title: document.getElementById('topicTitle').value },
    });
    e.target.reset();
    loadTopics(true);
    loadCategories();
});

const backToTopBtn = document.getElementById('backToTopBtn');

window.addEventListener('scroll', () => {
    backToTopBtn.style.display = window.scrollY > 400 ? 'block' : 'none';
});

backToTopBtn.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
});

loadGroup();
loadCategories();
loadTopics();
</script>
@endsection
