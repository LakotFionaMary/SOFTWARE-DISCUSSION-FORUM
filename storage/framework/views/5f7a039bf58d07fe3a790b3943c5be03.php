<?php $__env->startSection('title', 'Group Topics'); ?>

<?php $__env->startSection('content'); ?>
<div class="eyebrow">Discussion Group</div>
<h1 id="groupName">Loading…</h1>

<div class="card">
    <h3>Start a new topic</h3>
    <form id="newTopicForm">
        <input type="text" id="topicTitle" placeholder="What do you want to discuss?" required>
        <button class="btn" type="submit">Launch topic</button>
    </form>
</div>

<div id="topics"></div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
const groupId = <?php echo e($group); ?>;

async function loadGroup() {
    const g = await api(`/groups/${groupId}`);
    document.getElementById('groupName').textContent = g.name;
}

async function loadTopics() {
    const data = await api(`/groups/${groupId}/topics`);
    document.getElementById('topics').innerHTML = (data.data || []).map(t => `
        <div class="card">
            <strong><a href="/topics/${t.topic_id}">${t.title}</a></strong>
            <div class="muted">${t.category ?? 'General'} · ${t.posts_count ?? 0} posts</div>
        </div>
    `).join('') || '<div class="muted">No topics yet — be the first to start one.</div>';
}

document.getElementById('newTopicForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const res = await api(`/groups/${groupId}/topics`, {
        method: 'POST',
        body: { title: document.getElementById('topicTitle').value },
    });
    e.target.reset();
    loadTopics();
});

loadGroup();
loadTopics();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /data/data/com.termux/files/home/forumG/resources/views/topics/index.blade.php ENDPATH**/ ?>