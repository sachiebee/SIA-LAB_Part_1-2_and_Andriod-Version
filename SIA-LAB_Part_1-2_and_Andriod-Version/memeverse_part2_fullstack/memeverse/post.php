<?php
require_once 'includes/header.php';

$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$post_id) redirect('index.php');

$stmt = $pdo->prepare("
    SELECT p.*, u.username, u.avatar, u.nickname, u.id as user_id,
           c.name as category_name, c.slug as category_slug, c.id as category_id
    FROM posts p
    JOIN users u ON p.user_id = u.id
    JOIN categories c ON p.category_id = c.id
    WHERE p.id = ?
");
$stmt->execute([$post_id]);
$post = $stmt->fetch();
if (!$post) redirect('index.php');

$post['vote_score'] = getVoteScore($pdo, $post_id);
$post['user_vote'] = getUserVote($pdo, $post_id, $_SESSION['user_id'] ?? 0);
$post['comment_count'] = getCommentCount($pdo, $post_id);

$stmt = $pdo->prepare("
    SELECT c.*, u.username, u.avatar, u.nickname
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.post_id = ?
    ORDER BY c.created_at ASC
");
$stmt->execute([$post_id]);
$all_comments = $stmt->fetchAll();

function buildCommentTree($comments) {
    $tree = [];
    $ref = [];
    foreach ($comments as $comment) {
        $comment['replies'] = [];
        $ref[$comment['id']] = $comment;
    }
    foreach ($ref as $id => $comment) {
        if ($comment['parent_id'] == 0 || $comment['parent_id'] === null) {
            $tree[$id] = &$ref[$id];
        } else {
            if (isset($ref[$comment['parent_id']])) {
                $ref[$comment['parent_id']]['replies'][] = &$ref[$id];
            }
        }
    }
    return $tree;
}
$comments_tree = buildCommentTree($all_comments);

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$is_owner = (isLoggedIn() && $_SESSION['user_id'] == $post['user_id']);
?>

<div class="row g-4">
    <div class="col-lg-3">
        <?php include 'includes/sidebar.php'; ?>
    </div>
    
    <div class="col-lg-6">
        <div class="post-card">
            <div class="post-header">
                <img src="<?= getUserAvatar($post, 48) ?>" class="post-avatar">
                <div class="post-user-info">
                    <a href="profile.php?id=<?= $post['user_id'] ?>" class="post-username"><?= escape($post['nickname'] ?: $post['username']) ?></a>
                    <div class="post-time"><?= date('F j, Y \a\t g:i a', strtotime($post['created_at'])) ?></div>
                </div>
                <span class="category-badge"><span><?= getCategoryEmoji($post['category_slug']) ?></span> <?= escape($post['category_name']) ?></span>
            </div>
            <img src="<?= SITE_URL . $post['image_path'] ?>" class="post-image">
            <div class="post-body">
                <h2 class="post-title"><?= escape($post['title'] ?: 'Untitled') ?></h2>
                <?php if ($post['description']): ?>
                    <p class="post-description" style="font-size: 1rem;"><?= nl2br(escape($post['description'])) ?></p>
                <?php endif; ?>
                <div class="post-actions">
                    <div class="vote-group">
                        <button class="vote-btn upvote <?= $post['user_vote'] === 1 ? 'active' : '' ?>" data-post-id="<?= $post['id'] ?>" data-vote="up"><i class="bi bi-arrow-up"></i></button>
                        <span class="vote-count" id="vote-<?= $post['id'] ?>"><?= $post['vote_score'] ?></span>
                        <button class="vote-btn downvote <?= $post['user_vote'] === -1 ? 'active' : '' ?>" data-post-id="<?= $post['id'] ?>" data-vote="down"><i class="bi bi-arrow-down"></i></button>
                    </div>
                    <?php if (isLoggedIn() && !$is_owner): ?>
                        <button class="btn btn-sm btn-outline-danger report-btn" data-type="post" data-id="<?= $post['id'] ?>">Report Post</button>
                    <?php endif; ?>
                </div>
                <?php if ($is_owner): ?>
                    <div class="mt-3 d-flex gap-2">
                        <button class="btn-create edit-post-btn" data-post-id="<?= $post['id'] ?>" data-title="<?= escape($post['title']) ?>" data-description="<?= escape($post['description']) ?>" data-category-id="<?= $post['category_id'] ?>"><i class="bi bi-pencil"></i> Edit Post</button>
                        <button class="btn-outline delete-post-btn" data-post-id="<?= $post['id'] ?>"><i class="bi bi-trash"></i> Delete Post</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="profile-header mt-4">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div style="font-size: 2rem;">💬</div>
                <div><h3 class="mb-0">Comments</h3><p class="text-muted mb-0">Join the conversation</p></div>
            </div>
            
            <?php if (isLoggedIn()): ?>
                <div class="mb-4">
                    <div class="d-flex gap-3">
                        <img src="<?= getUserAvatar($current_user, 44) ?>" class="comment-avatar" style="width: 44px; height: 44px;">
                        <div class="flex-grow-1">
                            <textarea id="comment-text" class="form-control" rows="3" placeholder="What are your thoughts? Share your reaction..."></textarea>
                            <div class="mt-2 text-end">
                                <button class="btn-create" id="post-comment-btn"><i class="bi bi-send"></i> Post Comment</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="sidebar-card text-center mb-4"><p class="mb-0"><a href="login.php" class="text-primary fw-bold">Login</a> to join the conversation! 🎭</p></div>
            <?php endif; ?>
            
            <div id="comments-container">
                <?php if (empty($comments_tree)): ?>
                    <div class="text-center py-4"><i class="bi bi-chat-dots fs-1 text-muted"></i><p class="text-muted mt-2 mb-0">No comments yet. Be the first!</p></div>
                <?php else: ?>
                    <?php foreach ($comments_tree as $comment): ?>
                        <div class="comment-card" id="comment-<?= $comment['id'] ?>">
                            <div class="comment-header">
                                <img src="<?= getUserAvatar($comment, 36) ?>" class="comment-avatar">
                                <div>
                                    <a href="profile.php?id=<?= $comment['user_id'] ?>" class="comment-username"><?= escape($comment['nickname'] ?: $comment['username']) ?></a>
                                    <span class="comment-time"><?= timeAgo($comment['created_at']) ?></span>
                                </div>
                                <?php if (isLoggedIn()): ?>
                                    <button class="reply-btn" data-comment-id="<?= $comment['id'] ?>"><i class="bi bi-reply"></i> Reply</button>
                                <?php endif; ?>
                                <?php if ($current_user && ($current_user['id'] == $comment['user_id'] || $current_user['id'] == $post['user_id'])): ?>
                                    <button class="delete-comment-btn btn-sm" data-id="<?= $comment['id'] ?>"><i class="bi bi-trash"></i></button>
                                <?php endif; ?>
                            </div>
                            <div class="comment-text"><?= nl2br(escape($comment['comment_text'])) ?></div>
                            
                            <?php if (isLoggedIn()): ?>
                                <div class="reply-form" id="reply-form-<?= $comment['id'] ?>" style="display: none;">
                                    <div class="d-flex gap-2 mt-3">
                                        <img src="<?= getUserAvatar($current_user, 32) ?>" class="comment-avatar" style="width: 32px; height: 32px;">
                                        <div class="flex-grow-1">
                                            <textarea class="form-control reply-text" rows="2" placeholder="Write a reply..."></textarea>
                                            <div class="mt-2 text-end">
                                                <button class="btn-sm btn-outline cancel-reply" data-comment-id="<?= $comment['id'] ?>">Cancel</button>
                                                <button class="btn-sm btn-create submit-reply" data-comment-id="<?= $comment['id'] ?>" data-post-id="<?= $post_id ?>">Reply</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($comment['replies'])): ?>
                                <div class="replies-container">
                                    <?php foreach ($comment['replies'] as $reply): ?>
                                        <div class="reply-card">
                                            <div class="comment-header">
                                                <img src="<?= getUserAvatar($reply, 32) ?>" class="comment-avatar" style="width: 32px; height: 32px;">
                                                <div>
                                                    <a href="profile.php?id=<?= $reply['user_id'] ?>" class="comment-username"><?= escape($reply['nickname'] ?: $reply['username']) ?></a>
                                                    <span class="comment-time"><?= timeAgo($reply['created_at']) ?></span>
                                                </div>
                                                <?php if ($current_user && ($current_user['id'] == $reply['user_id'] || $current_user['id'] == $post['user_id'])): ?>
                                                    <button class="delete-comment-btn btn-sm" data-id="<?= $reply['id'] ?>"><i class="bi bi-trash"></i></button>
                                                <?php endif; ?>
                                            </div>
                                            <div class="comment-text" style="margin-left: 2.5rem;"><?= nl2br(escape($reply['comment_text'])) ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3">
        <div class="sidebar-card">
            <div class="sidebar-title"><i class="bi bi-fire"></i> TRENDING 🔥</div>
            <div id="trending-container" class="category-list"><div class="text-center py-2 text-muted">Loading...</div></div>
        </div>
    </div>
</div>

<style>
.comment-card { background: var(--bg-primary); border-radius: 20px; padding: 1rem; margin-bottom: 1rem; }
.comment-header { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; }
.comment-avatar { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; }
.comment-username { font-weight: 600; color: var(--text-primary); text-decoration: none; }
.comment-username:hover { color: var(--primary); }
.comment-time { font-size: 0.7rem; color: var(--text-muted); margin-left: 0.5rem; }
.comment-text { color: var(--text-secondary); margin-left: 3rem; line-height: 1.6; }
.reply-btn { background: transparent; border: none; padding: 0.25rem 0.75rem; border-radius: 40px; font-size: 0.75rem; color: var(--text-muted); cursor: pointer; margin-left: auto; }
.reply-btn:hover { background: var(--bg-primary); color: var(--primary); }
.reply-form { margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color); }
.replies-container { margin-top: 1rem; margin-left: 2.5rem; padding-left: 1rem; border-left: 2px solid var(--border-color); }
.reply-card { margin-top: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--border-color); }
.reply-card:last-child { border-bottom: none; }
.btn-sm { padding: 0.375rem 0.875rem; font-size: 0.8rem; border-radius: 40px; cursor: pointer; }
.btn-sm.btn-outline { background: transparent; border: 1px solid var(--border-color); color: var(--text-secondary); margin-right: 0.5rem; }
.btn-sm.btn-outline:hover { border-color: var(--primary); color: var(--primary); }
.btn-sm.btn-create { background: var(--primary); color: white; border: none; }
.btn-sm.btn-create:hover { background: var(--primary-dark); }
</style>

<script>
document.getElementById('post-comment-btn')?.addEventListener('click', async function() {
    const commentText = document.getElementById('comment-text').value.trim();
    if (!commentText) return;
    const formData = new URLSearchParams();
    formData.append('post_id', <?= $post_id ?>);
    formData.append('comment', commentText);
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Posting...';
    try {
        const response = await fetch(siteBase + 'api/comment.php', { method: 'POST', body: formData });
        const data = await response.json();
        if (data.success) location.reload();
        else alert(data.error || 'Failed to post comment');
    } catch (error) { alert('Network error'); }
    finally { this.disabled = false; this.innerHTML = '<i class="bi bi-send"></i> Post Comment'; }
});

document.querySelectorAll('.reply-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const commentId = this.dataset.commentId;
        document.querySelectorAll('.reply-form').forEach(f => f.style.display = 'none');
        document.getElementById(`reply-form-${commentId}`).style.display = 'block';
    });
});

document.querySelectorAll('.cancel-reply').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById(`reply-form-${this.dataset.commentId}`).style.display = 'none';
    });
});

document.querySelectorAll('.submit-reply').forEach(btn => {
    btn.addEventListener('click', async function() {
        const parent = this.dataset.commentId;
        const postId = this.dataset.postId;
        const reply = document.querySelector(`#reply-form-${parent} .reply-text`).value.trim();
        if (!reply) return;
        const formData = new URLSearchParams();
        formData.append('post_id', postId);
        formData.append('comment', reply);
        formData.append('parent_id', parent);
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        try {
            const response = await fetch(siteBase + 'api/comment.php', { method: 'POST', body: formData });
            const data = await response.json();
            if (data.success) location.reload();
            else alert(data.error || 'Failed to post reply');
        } catch (error) { alert('Network error'); }
        finally { this.disabled = false; this.innerHTML = 'Reply'; }
    });
});

let commentToDelete = null;
document.querySelectorAll('.delete-comment-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        commentToDelete = this.dataset.id;
        const modal = new bootstrap.Modal(document.getElementById('deleteCommentModal'));
        modal.show();
    });
});

document.querySelector('.confirm-delete-comment-btn')?.addEventListener('click', async () => {
    if (!commentToDelete) return;
    const formData = new URLSearchParams();
    formData.append('comment_id', commentToDelete);
    try {
        const res = await fetch(siteBase + 'api/comment.php', { method: 'DELETE', body: formData });
        const data = await res.json();
        if (data.success) location.reload();
        else alert(data.error || 'Delete failed');
    } catch (err) { alert('Network error'); }
    finally { bootstrap.Modal.getInstance(document.getElementById('deleteCommentModal')).hide(); commentToDelete = null; }
});

async function loadTrending() {
    try {
        const response = await fetch(siteBase + 'api/trending.php');
        const posts = await response.json();
        const container = document.getElementById('trending-container');
        if (!posts || posts.length === 0) { container.innerHTML = '<div class="text-center py-2 text-muted">No trending posts yet</div>'; return; }
        let html = '';
        posts.forEach((post, index) => {
            html += `<a href="post.php?id=${post.id}" class="category-link"><span class="emoji">${index === 0 ? '🥇' : index === 1 ? '🥈' : index === 2 ? '🥉' : '🔥'}</span><span>${escapeHtml(post.title || 'Meme')}</span><span class="ms-auto small">+${post.vote_score}</span></a>`;
        });
        container.innerHTML = html;
    } catch (error) { console.error(error); }
}
loadTrending();
</script>

<!-- Delete Comment Modal -->
<div class="modal fade" id="deleteCommentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content confirmation-modal">
            <div class="modal-body text-center py-4">
                <div class="confirmation-icon"><i class="bi bi-trash3"></i></div>
                <h5 class="confirmation-title">Delete Comment?</h5>
                <p class="confirmation-message">Are you sure? This comment will vanish! 💨</p>
                <div class="confirmation-buttons">
                    <button class="btn btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-danger confirm-delete-comment-btn">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>