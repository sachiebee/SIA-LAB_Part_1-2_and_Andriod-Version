<?php
require_once 'includes/header.php';

// Get user ID from URL or use current user
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : (isLoggedIn() ? $_SESSION['user_id'] : 0);

// If no user ID and not logged in, redirect to login
if (!$user_id) {
    redirect('login.php');
}

// Fetch user data
$stmt = $pdo->prepare("SELECT id, username, nickname, email, bio, avatar, created_at, is_admin FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// If user doesn't exist, redirect to home
if (!$user) {
    redirect('index.php');
}

$is_owner = (isLoggedIn() && $_SESSION['user_id'] == $user_id);
$display_name = !empty($user['nickname']) ? $user['nickname'] : $user['username'];

// Get avatar URL
$avatar_url = getUserAvatar($user, 120);

// Get user's posts with vote and comment counts
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name, c.id as category_id,
           (SELECT COALESCE(SUM(vote_value), 0) FROM votes WHERE post_id = p.id) as vote_score,
           (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count
    FROM posts p
    JOIN categories c ON p.category_id = c.id
    WHERE p.user_id = ?
    ORDER BY p.created_at DESC
");
$stmt->execute([$user_id]);
$user_posts = $stmt->fetchAll();

// Followers/Following counts
$stmt = $pdo->prepare("SELECT COUNT(*) FROM followers WHERE following_id = ?");
$stmt->execute([$user_id]);
$followers = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM followers WHERE follower_id = ?");
$stmt->execute([$user_id]);
$following = (int)$stmt->fetchColumn();

// Check if current user follows this profile
$is_following = false;
if (isLoggedIn() && $_SESSION['user_id'] != $user_id) {
    $stmt = $pdo->prepare("SELECT id FROM followers WHERE follower_id = ? AND following_id = ?");
    $stmt->execute([$_SESSION['user_id'], $user_id]);
    $is_following = (bool)$stmt->fetch();
}

// Get categories for edit modal
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>

<div class="row g-4">
    <!-- Left Sidebar -->
    <div class="col-lg-3">
        <?php include 'includes/sidebar.php'; ?>
    </div>
    
    <!-- Main Content -->
    <div class="col-lg-6">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="row align-items-center">
                <div class="col-md-3 text-center text-md-start">
                    <img src="<?= $avatar_url ?>" class="profile-avatar-large" alt="Avatar">
                </div>
                <div class="col-md-9 text-center text-md-start">
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-2">
                        <h2 class="h3 mb-0"><?= escape($display_name) ?></h2>
                        <span class="text-muted">@<?= escape($user['username']) ?></span>
                        
                        <?php if ($is_owner): ?>
                            <button class="btn btn-sm btn-outline-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                                <i class="bi bi-pencil"></i> Edit Profile
                            </button>
                        <?php elseif (isLoggedIn()): ?>
                            <button class="btn btn-sm <?= $is_following ? 'btn-outline-danger' : 'btn-primary' ?> rounded-pill follow-btn"
                                    data-user-id="<?= $user_id ?>" 
                                    data-action="<?= $is_following ? 'unfollow' : 'follow' ?>">
                                <i class="bi bi-person-<?= $is_following ? 'dash' : 'plus' ?>"></i>
                                <?= $is_following ? 'Unfollow' : 'Follow' ?>
                            </button>
                            <a href="messages.php?with=<?= $user_id ?>" class="btn btn-sm btn-outline-primary rounded-pill">
                                <i class="bi bi-envelope"></i> Message
                            </a>
                            <button class="btn btn-sm btn-outline-danger rounded-pill report-user-btn" data-user-id="<?= $user_id ?>">
                                <i class="bi bi-flag"></i> Report
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($user['is_admin']): ?>
                            <span class="badge bg-danger">Admin</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="profile-stats">
                        <div class="profile-stat">
                            <div class="profile-stat-number"><?= count($user_posts) ?></div>
                            <div class="profile-stat-label">Posts</div>
                        </div>
                        <div class="profile-stat">
                            <div class="profile-stat-number"><?= $followers ?></div>
                            <div class="profile-stat-label">Followers</div>
                        </div>
                        <div class="profile-stat">
                            <div class="profile-stat-number"><?= $following ?></div>
                            <div class="profile-stat-label">Following</div>
                        </div>
                    </div>
                    
                    <?php if (!empty($user['bio'])): ?>
                        <div class="profile-bio">
                            <?= nl2br(escape($user['bio'])) ?>
                        </div>
                    <?php endif; ?>
                    
                    <small class="text-muted">
                        <i class="bi bi-calendar3"></i> Joined <?= date('F Y', strtotime($user['created_at'])) ?>
                    </small>
                </div>
            </div>
        </div>
        
        <!-- Posts Gallery -->
        <h5 class="mb-3"><i class="bi bi-grid-3x3-gap-fill"></i> Posts Gallery</h5>
        
        <?php if (empty($user_posts)): ?>
            <div class="empty-state">
                <i class="bi bi-camera"></i>
                <h5>No posts yet</h5>
                <?php if ($is_owner): ?>
                    <a href="upload.php" class="btn-create">Upload Your First Meme</a>
                <?php else: ?>
                    <p class="text-muted">This user hasn't posted any memes yet.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="posts-grid">
                <?php foreach ($user_posts as $post): ?>
                    <div class="grid-item" data-post-id="<?= $post['id'] ?>">
                        <a href="post.php?id=<?= $post['id'] ?>" class="grid-link">
                            <img src="<?= SITE_URL . $post['image_path'] ?>" alt="Post">
                        </a>
                        <div class="grid-overlay">
                            <div class="grid-stats">
                                <span><i class="bi bi-arrow-up"></i> <?= $post['vote_score'] ?></span>
                                <span><i class="bi bi-chat"></i> <?= $post['comment_count'] ?></span>
                            </div>
                        </div>
                        <?php if ($is_owner): ?>
                            <div class="grid-actions">
                                <button class="grid-action-btn edit-post"
                                        data-post-id="<?= $post['id'] ?>"
                                        data-title="<?= escape($post['title'] ?? '') ?>"
                                        data-description="<?= escape($post['description'] ?? '') ?>"
                                        data-category-id="<?= $post['category_id'] ?>"
                                        data-image="<?= SITE_URL . $post['image_path'] ?>"
                                        title="Edit Post">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <button class="grid-action-btn delete-post"
                                        data-post-id="<?= $post['id'] ?>"
                                        title="Delete Post">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Right Sidebar - Trending -->
    <div class="col-lg-3">
        <div class="sidebar-card">
            <div class="sidebar-title"><i class="bi bi-fire"></i> TRENDING 🔥</div>
            <div id="trending-container" class="category-list">
                <div class="text-center py-2 text-muted">Loading...</div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal (Owner Only) -->
<?php if ($is_owner): ?>
<div class="modal fade" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Edit Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editProfileForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <img id="avatarPreview" src="<?= $avatar_url ?>" class="rounded-circle mb-2" style="width: 100px; height: 100px; object-fit: cover;">
                        <input type="file" name="avatar" id="avatarInput" class="form-control form-control-sm" accept="image/*">
                        <small class="text-muted">Leave empty to keep current avatar</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nickname</label>
                        <input type="text" name="nickname" class="form-control" value="<?= escape($user['nickname'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bio</label>
                        <textarea name="bio" class="form-control" rows="4"><?= escape($user['bio'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Post Modal -->
<div class="modal fade" id="editPostModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Edit Meme</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit_post_id">
                <div class="text-center mb-3">
                    <img id="edit_image_preview" src="" style="max-width: 100%; max-height: 200px; border-radius: 12px;">
                </div>
                <div class="mb-3">
                    <label>Title</label>
                    <input type="text" id="edit_title" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Description</label>
                    <textarea id="edit_description" class="form-control" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label>Category</label>
                    <select id="edit_category" class="form-select">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= getCategoryEmoji($cat['slug']) ?> <?= escape($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="savePostEditBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Post Confirmation Modal -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="confirmation-icon"><i class="bi bi-trash3"></i></div>
                <h5 class="confirmation-title">Delete Meme?</h5>
                <p class="confirmation-message">Are you sure? This meme will vanish forever! 💨</p>
                <div class="confirmation-buttons">
                    <button class="btn btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-danger confirm-delete-btn">Delete Forever!</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Report User Modal -->
<div class="modal fade" id="reportModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-flag"></i> Report User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="report-user-id">
                <div class="mb-3">
                    <label class="form-label">Why are you reporting this user?</label>
                    <textarea id="report-reason" class="form-control" rows="3" placeholder="Please provide details..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-danger" id="submit-report-btn">Submit Report</button>
            </div>
        </div>
    </div>
</div>

<script>
// Make sure siteBase is defined
window.siteBase = '<?= SITE_URL ?>';

// Avatar preview
document.getElementById('avatarInput')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = ev => document.getElementById('avatarPreview').src = ev.target.result;
        reader.readAsDataURL(file);
    }
});

// Edit profile form submission
document.getElementById('editProfileForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    try {
        const response = await fetch(siteBase + 'api/update_profile.php', { 
            method: 'POST', 
            body: formData 
        });
        const data = await response.json();
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Update failed');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Network error');
    }
});

// Edit post handlers
document.querySelectorAll('.edit-post').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('edit_post_id').value = this.dataset.postId;
        document.getElementById('edit_title').value = this.dataset.title || '';
        document.getElementById('edit_description').value = this.dataset.description || '';
        document.getElementById('edit_category').value = this.dataset.categoryId;
        document.getElementById('edit_image_preview').src = this.dataset.image;
        new bootstrap.Modal(document.getElementById('editPostModal')).show();
    });
});

document.getElementById('savePostEditBtn')?.addEventListener('click', async function() {
    const formData = new URLSearchParams();
    formData.append('post_id', document.getElementById('edit_post_id').value);
    formData.append('title', document.getElementById('edit_title').value);
    formData.append('description', document.getElementById('edit_description').value);
    formData.append('category_id', document.getElementById('edit_category').value);
    
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';
    
    try {
        const response = await fetch(siteBase + 'api/edit_post.php', { method: 'POST', body: formData });
        const data = await response.json();
        if (data.success) location.reload();
        else alert(data.error || 'Failed to update post');
    } catch (error) {
        alert('Network error');
    } finally {
        this.disabled = false;
        this.innerHTML = 'Save Changes';
    }
});

// Delete post
let postToDelete = null;
document.querySelectorAll('.delete-post').forEach(btn => {
    btn.addEventListener('click', function() {
        postToDelete = this;
        new bootstrap.Modal(document.getElementById('confirmDeleteModal')).show();
    });
});

document.querySelector('.confirm-delete-btn')?.addEventListener('click', async () => {
    if (!postToDelete) return;
    const formData = new URLSearchParams();
    formData.append('post_id', postToDelete.dataset.postId);
    
    try {
        const response = await fetch(siteBase + 'api/delete_post.php', { method: 'POST', body: formData });
        const data = await response.json();
        if (data.success) {
            const gridItem = postToDelete.closest('.grid-item');
            if (gridItem) gridItem.remove();
            bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal')).hide();
            if (document.querySelectorAll('.grid-item').length === 0) {
                location.reload();
            }
        } else {
            alert(data.error || 'Delete failed');
        }
    } catch (error) {
        alert('Network error');
    }
});

// Follow/Unfollow
document.querySelectorAll('.follow-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        const formData = new URLSearchParams();
        formData.append('user_id', this.dataset.userId);
        formData.append('action', this.dataset.action);
        try {
            const response = await fetch(siteBase + 'api/follow.php', { method: 'POST', body: formData });
            const data = await response.json();
            if (data.success) location.reload();
            else alert(data.error || 'Action failed');
        } catch (error) {
            alert('Network error');
        }
    });
});

// Report user
document.querySelectorAll('.report-user-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('report-user-id').value = this.dataset.userId;
        new bootstrap.Modal(document.getElementById('reportModal')).show();
    });
});

document.getElementById('submit-report-btn')?.addEventListener('click', async () => {
    const userId = document.getElementById('report-user-id').value;
    const reason = document.getElementById('report-reason').value.trim();
    if (!reason) {
        alert('Please provide a reason');
        return;
    }
    try {
        const response = await fetch(siteBase + 'api/report.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ type: 'user', id: userId, reason })
        });
        const data = await response.json();
        if (data.success) {
            alert('Report submitted. Thank you!');
            bootstrap.Modal.getInstance(document.getElementById('reportModal')).hide();
            document.getElementById('report-reason').value = '';
        } else {
            alert(data.error || 'Failed to submit report');
        }
    } catch (error) {
        alert('Network error');
    }
});

// Load trending
async function loadTrending() {
    try {
        const response = await fetch(siteBase + 'api/trending.php');
        const posts = await response.json();
        const container = document.getElementById('trending-container');
        if (!posts || posts.length === 0) {
            container.innerHTML = '<div class="text-center py-2 text-muted">No trending posts yet</div>';
            return;
        }
        let html = '';
        posts.forEach((post, index) => {
            html += `<a href="post.php?id=${post.id}" class="category-link">
                        <span class="emoji">${index === 0 ? '🥇' : index === 1 ? '🥈' : index === 2 ? '🥉' : '🔥'}</span>
                        <span>${escapeHtml(post.title || 'Meme')}</span>
                        <span class="ms-auto small">+${post.vote_score}</span>
                    </a>`;
        });
        container.innerHTML = html;
    } catch (error) {
        console.error('Trending error:', error);
        document.getElementById('trending-container').innerHTML = '<div class="text-center py-2 text-muted">Failed to load</div>';
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

loadTrending();
</script>

<style>
.profile-header {
    background: var(--bg-card);
    border-radius: 28px;
    padding: 2rem;
    margin-bottom: 2rem;
    border: 1px solid var(--border-color);
}
.profile-avatar-large {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid var(--primary);
}
.profile-stats {
    display: flex;
    gap: 2rem;
    margin: 1rem 0;
}
.profile-stat {
    text-align: center;
}
.profile-stat-number {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--primary);
}
.profile-stat-label {
    font-size: 0.7rem;
    color: var(--text-muted);
    text-transform: uppercase;
}
.profile-bio {
    background: var(--bg-primary);
    padding: 1rem;
    border-radius: 20px;
    margin-top: 1rem;
    font-size: 0.9rem;
    color: var(--text-secondary);
}
.posts-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.25rem;
}
.grid-item {
    position: relative;
    aspect-ratio: 1 / 1;
    border-radius: 20px;
    overflow: hidden;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    transition: all 0.3s;
}
.grid-item:hover {
    transform: scale(1.02);
    box-shadow: var(--shadow-lg);
}
.grid-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.grid-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
    padding: 1rem;
    opacity: 0;
    transition: opacity 0.3s;
}
.grid-item:hover .grid-overlay {
    opacity: 1;
}
.grid-stats {
    display: flex;
    gap: 1rem;
    color: white;
    font-size: 0.85rem;
    font-weight: 500;
}
.grid-actions {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    display: flex;
    gap: 0.5rem;
    opacity: 0;
    transition: opacity 0.3s;
}
.grid-item:hover .grid-actions {
    opacity: 1;
}
.grid-action-btn {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: rgba(0,0,0,0.7);
    backdrop-filter: blur(4px);
    border: none;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}
.grid-action-btn.edit-post:hover {
    background: var(--primary);
}
.grid-action-btn.delete-post:hover {
    background: var(--danger);
}
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: var(--bg-card);
    border-radius: 28px;
    border: 1px solid var(--border-color);
}
.empty-state i {
    font-size: 4rem;
    color: var(--text-muted);
    opacity: 0.5;
    margin-bottom: 1rem;
}
.btn-create {
    background: linear-gradient(135deg, var(--primary), #ff8e8e);
    color: white;
    padding: 0.5rem 1.25rem;
    border-radius: 40px;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    border: none;
    cursor: pointer;
}
.btn-create:hover {
    transform: translateY(-2px);
    color: white;
}
.confirmation-icon {
    width: 70px;
    height: 70px;
    background: rgba(239, 71, 111, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 2rem;
    color: var(--danger);
}
.confirmation-title {
    font-size: 1.3rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--text-primary);
}
.confirmation-message {
    color: var(--text-muted);
    margin-bottom: 1.5rem;
}
.confirmation-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
}
.btn-cancel {
    background: transparent;
    border: 1px solid var(--border-color);
    padding: 0.5rem 1.5rem;
    border-radius: 40px;
    color: var(--text-secondary);
    font-weight: 500;
    transition: all 0.2s;
}
.btn-cancel:hover {
    background: var(--bg-primary);
    border-color: var(--primary);
    color: var(--primary);
}
.btn-danger {
    background: var(--danger);
    border: none;
    padding: 0.5rem 1.5rem;
    border-radius: 40px;
    color: white;
    font-weight: 500;
}
@media (max-width: 768px) {
    .profile-header { padding: 1rem; text-align: center; }
    .profile-avatar-large { width: 80px; height: 80px; }
    .profile-stats { gap: 1rem; justify-content: center; }
    .posts-grid { grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
}
</style>

<?php require_once 'includes/footer.php'; ?>