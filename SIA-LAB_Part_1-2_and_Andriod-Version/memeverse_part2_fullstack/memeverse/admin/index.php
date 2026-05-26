<?php
require_once '../includes/header.php';
if (!isAdmin($pdo)) redirect('index.php');

// Stats
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalPosts = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
$totalComments = $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();
$visitsToday = $pdo->query("SELECT COUNT(*) FROM visits WHERE DATE(created_at) = CURDATE()")->fetchColumn();

$visits = getDailyVisits($pdo, 7);
$visitLabels = array_column($visits, 'date');
$visitData = array_column($visits, 'count');

$usersList = $pdo->query("
    SELECT id, username, email, nickname, created_at, is_admin, banned,
           (SELECT COUNT(*) FROM posts WHERE user_id = users.id) as post_count
    FROM users ORDER BY created_at DESC
")->fetchAll();

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

$comments = $pdo->query("
    SELECT c.id, c.comment_text, c.created_at, u.username as author, u.id as user_id, p.title as post_title, p.id as post_id
    FROM comments c
    JOIN users u ON c.user_id = u.id
    JOIN posts p ON c.post_id = p.id
    ORDER BY c.created_at DESC LIMIT 50
")->fetchAll();

$reports = $pdo->query("
    SELECT r.*, u.username as reporter, ru.username as reported_user, p.title as reported_post
    FROM reports r
    LEFT JOIN users u ON r.user_id = u.id
    LEFT JOIN users ru ON r.reported_user_id = ru.id
    LEFT JOIN posts p ON r.reported_post_id = p.id
    WHERE r.status = 'pending'
    ORDER BY r.created_at DESC LIMIT 20
")->fetchAll();
?>

<div class="container-fluid px-4 mt-4">
    <div class="row g-4">
        <div class="col-lg-3 col-xl-2">
            <div class="sidebar-card sticky-top" style="top: 90px;">
                <div class="sidebar-title mb-3">🎛️ Admin Menu</div>
                <div class="nav flex-column nav-pills" id="adminTabs" role="tablist">
                    <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#dashboard">Dashboard</button>
                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#users">Users</button>
                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#categories">Categories</button>
                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#comments">Comments</button>
                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#reports">Reports</button>
                </div>
            </div>
        </div>
        
        <div class="col-lg-9 col-xl-10">
            <div class="tab-content">
                <!-- Dashboard Tab -->
                <div class="tab-pane fade show active" id="dashboard">
                    <div class="row g-3 mb-4">
                        <div class="col-md-3"><div class="sidebar-card text-center"><div class="sidebar-title">👥 Users</div><div class="stat-number"><?= number_format($totalUsers) ?></div></div></div>
                        <div class="col-md-3"><div class="sidebar-card text-center"><div class="sidebar-title">📸 Posts</div><div class="stat-number"><?= number_format($totalPosts) ?></div></div></div>
                        <div class="col-md-3"><div class="sidebar-card text-center"><div class="sidebar-title">💬 Comments</div><div class="stat-number"><?= number_format($totalComments) ?></div></div></div>
                        <div class="col-md-3"><div class="sidebar-card text-center"><div class="sidebar-title">👀 Visits Today</div><div class="stat-number"><?= number_format($visitsToday) ?></div></div></div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="sidebar-card">
                                <div class="sidebar-title">📊 7-Day Visits</div>
                                <canvas id="visitsChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Users Tab -->
                <div class="tab-pane fade" id="users">
                    <div class="sidebar-card">
                        <div class="sidebar-title">👥 All Users</div>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Posts</th><th>Admin</th><th>Banned</th><th>Actions</th></tr></thead>
                                <tbody>
                                    <?php foreach ($usersList as $u): ?>
                                    <tr>
                                        <td><?= $u['id'] ?></td>
                                        <td>@<?= escape($u['username']) ?></td>
                                        <td><?= escape($u['email']) ?></td>
                                        <td><?= $u['post_count'] ?></td>
                                        <td><?= $u['is_admin'] ? '<span class="badge bg-danger">Admin</span>' : '<span class="badge bg-secondary">User</span>' ?></td>
                                        <td><?= $u['banned'] ? '<span class="badge bg-warning">Banned</span>' : '<span class="badge bg-success">Active</span>' ?></td>
                                        <td>
                                            <button class="btn-icon edit-user" data-id="<?= $u['id'] ?>" data-username="<?= escape($u['username']) ?>" data-email="<?= escape($u['email']) ?>" data-nickname="<?= escape($u['nickname'] ?? '') ?>" data-admin="<?= $u['is_admin'] ?>"><i class="bi bi-pencil"></i></button>
                                            <button class="btn-icon ban-user" data-id="<?= $u['id'] ?>" data-banned="<?= $u['banned'] ?>"><i class="bi bi-<?= $u['banned'] ? 'unlock' : 'lock' ?>"></i></button>
                                            <button class="btn-icon delete-user" data-id="<?= $u['id'] ?>"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Categories Tab -->
                <div class="tab-pane fade" id="categories">
                    <div class="sidebar-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="sidebar-title mb-0">🏷️ Manage Categories</div>
                            <button class="btn-sm btn-primary" id="addCategoryBtn"><i class="bi bi-plus-lg"></i> Add Category</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead><tr><th>ID</th><th>Name</th><th>Slug</th><th>Actions</th></tr></thead>
                                <tbody>
                                    <?php foreach ($categories as $cat): ?>
                                    <tr data-id="<?= $cat['id'] ?>">
                                        <td><?= $cat['id'] ?></td>
                                        <td class="cat-name"><?= escape($cat['name']) ?></td>
                                        <td class="cat-slug"><?= escape($cat['slug']) ?></td>
                                        <td>
                                            <button class="btn-icon edit-category" data-id="<?= $cat['id'] ?>" data-name="<?= escape($cat['name']) ?>" data-slug="<?= escape($cat['slug']) ?>"><i class="bi bi-pencil"></i></button>
                                            <button class="btn-icon delete-category" data-id="<?= $cat['id'] ?>"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Comments Tab -->
                <div class="tab-pane fade" id="comments">
                    <div class="sidebar-card">
                        <div class="sidebar-title">💬 Moderate Comments</div>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead><tr><th>Comment</th><th>Author</th><th>Post</th><th>Actions</th></tr></thead>
                                <tbody>
                                    <?php foreach ($comments as $c): ?>
                                    <tr>
                                        <td><?= escape(substr($c['comment_text'], 0, 80)) ?>...</td>
                                        <td>@<?= escape($c['author']) ?></td>
                                        <td><a href="<?= SITE_URL ?>post.php?id=<?= $c['post_id'] ?>" target="_blank"><?= escape($c['post_title'] ?: 'Untitled') ?></a></td>
                                        <td>
                                            <button class="btn-icon delete-comment" data-id="<?= $c['id'] ?>"><i class="bi bi-trash"></i></button>
                                            <button class="btn-icon ban-user-from-comment" data-user-id="<?= $c['user_id'] ?>" data-username="<?= escape($c['author']) ?>"><i class="bi bi-lock"></i> Ban User</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Reports Tab -->
                <div class="tab-pane fade" id="reports">
                    <div class="sidebar-card">
                        <div class="sidebar-title">🚩 Pending Reports</div>
                        <?php if (empty($reports)): ?>
                            <div class="text-muted text-center py-3">All clear! No pending reports. 🎉</div>
                        <?php else: ?>
                            <?php foreach ($reports as $r): ?>
                            <div class="border-bottom pb-2 mb-2">
                                <div class="d-flex justify-content-between">
                                    <strong><?= escape($r['reporter']) ?></strong>
                                    <small class="text-muted"><?= timeAgo($r['created_at']) ?></small>
                                </div>
                                <div class="small text-muted">Reported <?= $r['reported_user'] ? 'user @' . escape($r['reported_user']) : ($r['reported_post'] ? 'post "' . escape($r['reported_post']) . '"' : 'content') ?></div>
                                <div class="mt-1"><?= escape(substr($r['reason'], 0, 100)) ?>...</div>
                                <div class="mt-2"><button class="btn btn-sm btn-outline-primary resolve-report" data-id="<?= $r['id'] ?>"><i class="bi bi-check-lg"></i> Resolve</button></div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">✏️ Edit User</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="edit_user_id">
                <div class="mb-3"><label>Username</label><input type="text" id="edit_username" class="form-control"></div>
                <div class="mb-3"><label>Email</label><input type="email" id="edit_email" class="form-control"></div>
                <div class="mb-3"><label>Nickname</label><input type="text" id="edit_nickname" class="form-control"></div>
                <div class="mb-3"><label>Admin Status</label><select id="edit_is_admin" class="form-select"><option value="0">User</option><option value="1">Admin</option></select></div>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" id="saveUserBtn">Save Changes</button></div>
        </div>
    </div>
</div>

<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">➕ Add Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label>Name</label><input type="text" id="new_cat_name" class="form-control" placeholder="e.g., Funny"></div>
                <div class="mb-3"><label>Slug</label><input type="text" id="new_cat_slug" class="form-control" placeholder="e.g., funny"></div>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" id="submitAddCategory">Add Category</button></div>
        </div>
    </div>
</div>

<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">✏️ Edit Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="edit_cat_id">
                <div class="mb-3"><label>Name</label><input type="text" id="edit_cat_name" class="form-control"></div>
                <div class="mb-3"><label>Slug</label><input type="text" id="edit_cat_slug" class="form-control"></div>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" id="submitEditCategory">Save Changes</button></div>
        </div>
    </div>
</div>

<style>
.stat-number { font-size: 2rem; font-weight: 700; color: var(--primary); }
.btn-icon { background: none; border: none; padding: 0.25rem; margin: 0 0.25rem; cursor: pointer; color: var(--text-secondary); }
.btn-icon:hover { color: var(--primary); }
.nav-pills .nav-link { color: var(--text-secondary); border-radius: 12px; padding: 0.6rem 1rem; margin-bottom: 0.25rem; }
.nav-pills .nav-link.active { background: var(--primary); color: white; }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('visitsChart'), {
    type: 'line',
    data: { labels: <?= json_encode($visitLabels) ?>, datasets: [{ label: 'Visits', data: <?= json_encode($visitData) ?>, borderColor: '#ff6b6b', backgroundColor: 'rgba(255,107,107,0.1)', fill: true, tension: 0.4 }] },
    options: { responsive: true, maintainAspectRatio: true }
});

// User Management
const editUserModal = new bootstrap.Modal(document.getElementById('editUserModal'));
document.querySelectorAll('.edit-user').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('edit_user_id').value = btn.dataset.id;
        document.getElementById('edit_username').value = btn.dataset.username;
        document.getElementById('edit_email').value = btn.dataset.email;
        document.getElementById('edit_nickname').value = btn.dataset.nickname;
        document.getElementById('edit_is_admin').value = btn.dataset.admin;
        editUserModal.show();
    });
});

document.getElementById('saveUserBtn').addEventListener('click', async () => {
    const data = {
        id: document.getElementById('edit_user_id').value,
        username: document.getElementById('edit_username').value,
        email: document.getElementById('edit_email').value,
        nickname: document.getElementById('edit_nickname').value,
        is_admin: document.getElementById('edit_is_admin').value
    };
    const res = await fetch('<?= SITE_URL ?>admin/api/edit_user.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
    const result = await res.json();
    if (result.success) location.reload();
    else alert(result.error || 'Update failed');
});

document.querySelectorAll('.ban-user').forEach(btn => {
    btn.addEventListener('click', async () => {
        const userId = btn.dataset.id;
        const isBanned = btn.dataset.banned == '1';
        if (!confirm(`Are you sure you want to ${isBanned ? 'unban' : 'ban'} this user?`)) return;
        const res = await fetch('<?= SITE_URL ?>admin/api/ban_user.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: userId, ban: isBanned ? 0 : 1 }) });
        const data = await res.json();
        if (data.success) location.reload();
        else alert(data.error || 'Action failed');
    });
});

document.querySelectorAll('.delete-user').forEach(btn => {
    btn.addEventListener('click', async () => {
        if (!confirm('Delete this user? All their posts and comments will be removed.')) return;
        const res = await fetch('<?= SITE_URL ?>admin/api/delete_user.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: btn.dataset.id }) });
        const data = await res.json();
        if (data.success) location.reload();
        else alert(data.error || 'Delete failed');
    });
});

// Category Management
const addCatModal = new bootstrap.Modal(document.getElementById('addCategoryModal'));
document.getElementById('addCategoryBtn').addEventListener('click', () => addCatModal.show());

document.getElementById('submitAddCategory').addEventListener('click', async () => {
    const name = document.getElementById('new_cat_name').value.trim();
    const slug = document.getElementById('new_cat_slug').value.trim().toLowerCase().replace(/\s+/g, '-');
    if (!name || !slug) { alert('Name and slug required'); return; }
    const res = await fetch('<?= SITE_URL ?>admin/api/add_category.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ name, slug }) });
    const data = await res.json();
    if (data.success) location.reload();
    else alert(data.error || 'Failed to add category');
});

const editCatModal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
document.querySelectorAll('.edit-category').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('edit_cat_id').value = btn.dataset.id;
        document.getElementById('edit_cat_name').value = btn.dataset.name;
        document.getElementById('edit_cat_slug').value = btn.dataset.slug;
        editCatModal.show();
    });
});

document.getElementById('submitEditCategory').addEventListener('click', async () => {
    const id = document.getElementById('edit_cat_id').value;
    const name = document.getElementById('edit_cat_name').value.trim();
    const slug = document.getElementById('edit_cat_slug').value.trim().toLowerCase().replace(/\s+/g, '-');
    if (!name || !slug) { alert('Name and slug required'); return; }
    const res = await fetch('<?= SITE_URL ?>admin/api/edit_category.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id, name, slug }) });
    const data = await res.json();
    if (data.success) location.reload();
    else alert(data.error || 'Failed to edit category');
});

document.querySelectorAll('.delete-category').forEach(btn => {
    btn.addEventListener('click', async () => {
        if (!confirm('Delete this category? Posts will be uncategorized.')) return;
        const res = await fetch('<?= SITE_URL ?>admin/api/delete_category.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: btn.dataset.id }) });
        const data = await res.json();
        if (data.success) location.reload();
        else alert(data.error || 'Failed to delete category');
    });
});

document.querySelectorAll('.delete-comment').forEach(btn => {
    btn.addEventListener('click', async () => {
        if (!confirm('Delete this comment?')) return;
        const res = await fetch('<?= SITE_URL ?>admin/api/delete_comment.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: btn.dataset.id }) });
        const data = await res.json();
        if (data.success) btn.closest('tr').remove();
        else alert(data.error || 'Delete failed');
    });
});

document.querySelectorAll('.resolve-report').forEach(btn => {
    btn.addEventListener('click', async () => {
        const res = await fetch('<?= SITE_URL ?>admin/api/resolve_report.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: btn.dataset.id }) });
        const data = await res.json();
        if (data.success) btn.closest('.border-bottom').remove();
        else alert(data.error || 'Failed to resolve');
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>