<?php
require_once 'includes/header.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
if (empty($slug)) redirect('index.php');

$stmt = $pdo->prepare("SELECT id, name, slug FROM categories WHERE slug = ?");
$stmt->execute([$slug]);
$category = $stmt->fetch();
if (!$category) redirect('index.php');

$category_id = $category['id'];
$category_name = $category['name'];
$category_emoji = getCategoryEmoji($slug);

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$stmt = $pdo->prepare("
    SELECT p.*, u.username, u.avatar, u.nickname, u.id as user_id,
           c.name as category_name, c.slug as category_slug
    FROM posts p
    JOIN users u ON p.user_id = u.id
    JOIN categories c ON p.category_id = c.id
    WHERE p.category_id = :category_id
    ORDER BY p.created_at DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll();

foreach ($posts as &$post) {
    $post['vote_score'] = getVoteScore($pdo, $post['id']);
    $post['comment_count'] = getCommentCount($pdo, $post['id']);
    $post['user_vote'] = getUserVote($pdo, $post['id'], $_SESSION['user_id'] ?? 0);
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE category_id = ?");
$stmt->execute([$category_id]);
$total_posts = $stmt->fetchColumn();
?>

<div class="row g-4">
    <div class="col-lg-3">
        <?php include 'includes/sidebar.php'; ?>
    </div>
    <div class="col-lg-6">
        <div class="profile-header mb-4">
            <div class="d-flex align-items-center gap-3">
                <div style="font-size: 3rem;"><?= $category_emoji ?></div>
                <div>
                    <h2 class="mb-0"><?= escape($category_name) ?> Memes</h2>
                    <p class="text-muted mb-0"><?= $total_posts ?> memes in this category</p>
                </div>
            </div>
        </div>
        
        <?php if (empty($posts)): ?>
            <div class="empty-state">
                <i class="bi bi-emoji-smile"></i>
                <h5>No memes in <?= escape($category_name) ?> yet!</h5>
                <p>Be the first to share something <?= strtolower($category_name) ?> and funny!</p>
                <?php if ($current_user): ?>
                    <a href="upload.php" class="btn-create">Upload Meme</a>
                <?php else: ?>
                    <a href="login.php" class="btn-create">Login to Post</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div id="posts-container">
                <?php foreach ($posts as $post): ?>
                    <div class="post-card">
                        <div class="post-header">
                            <img src="<?= getUserAvatar($post, 48) ?>" class="post-avatar">
                            <div class="post-user-info">
                                <a href="profile.php?id=<?= $post['user_id'] ?>" class="post-username"><?= escape($post['nickname'] ?: $post['username']) ?></a>
                                <div class="post-time"><?= timeAgo($post['created_at']) ?></div>
                            </div>
                            <span class="category-badge"><span><?= $category_emoji ?></span> <?= escape($post['category_name']) ?></span>
                        </div>
                        <a href="post.php?id=<?= $post['id'] ?>"><img src="<?= SITE_URL . $post['image_path'] ?>" class="post-image"></a>
                        <div class="post-body">
                            <h3 class="post-title"><?= escape($post['title'] ?: 'Untitled') ?></h3>
                            <?php if ($post['description']): ?>
                                <p class="post-description"><?= escape(substr($post['description'], 0, 150)) ?></p>
                            <?php endif; ?>
                            <div class="post-actions">
                                <div class="vote-group">
                                    <button class="vote-btn upvote <?= ($post['user_vote'] ?? 0) === 1 ? 'active' : '' ?>" data-post-id="<?= $post['id'] ?>" data-vote="up"><i class="bi bi-arrow-up"></i></button>
                                    <span class="vote-count" id="vote-<?= $post['id'] ?>"><?= $post['vote_score'] ?></span>
                                    <button class="vote-btn downvote <?= ($post['user_vote'] ?? 0) === -1 ? 'active' : '' ?>" data-post-id="<?= $post['id'] ?>" data-vote="down"><i class="bi bi-arrow-down"></i></button>
                                </div>
                                <a href="post.php?id=<?= $post['id'] ?>" class="comment-btn"><i class="bi bi-chat"></i> <span><?= $post['comment_count'] ?></span></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (count($posts) === $limit && count($posts) < $total_posts): ?>
                <div class="load-more-wrapper">
                    <button class="load-more-btn" id="load-more-btn" data-page="2" data-category="<?= $category_id ?>">
                        <i class="bi bi-arrow-repeat"></i> Load More <?= escape($category_name) ?> Memes
                    </button>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <div class="col-lg-3">
        <div class="sidebar-card">
            <div class="sidebar-title"><i class="bi bi-fire"></i> TRENDING 🔥</div>
            <div id="trending-container" class="category-list"><div class="text-center py-2 text-muted">Loading...</div></div>
        </div>
    </div>
</div>

<script>
let currentPage = 2;
let loading = false;
const loadMoreBtn = document.getElementById('load-more-btn');

if (loadMoreBtn) {
    loadMoreBtn.addEventListener('click', async function() {
        if (loading) return;
        loading = true;
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Loading...';
        
        try {
            const categoryId = this.dataset.category;
            const response = await fetch(`${siteBase}api/posts.php?page=${currentPage}&limit=10&category_id=${categoryId}`);
            const posts = await response.json();
            
            if (posts.length > 0) {
                const container = document.getElementById('posts-container');
                posts.forEach(post => {
                    container.insertAdjacentHTML('beforeend', createPostHTML(post));
                });
                currentPage++;
                if (posts.length < 10) this.style.display = 'none';
                else {
                    this.disabled = false;
                    this.innerHTML = '<i class="bi bi-arrow-repeat"></i> Load More Memes';
                }
            } else {
                this.style.display = 'none';
            }
        } catch (error) {
            console.error(error);
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-arrow-repeat"></i> Try Again';
        } finally {
            loading = false;
        }
    });
}

function createPostHTML(post) {
    const upActive = post.user_vote === 1 ? 'active' : '';
    const downActive = post.user_vote === -1 ? 'active' : '';
    const avatarUrl = post.avatar_url || `https://ui-avatars.com/api/?background=ff6b6b&color=fff&bold=true&size=48&name=${encodeURIComponent(post.username)}`;
    const imageUrl = siteBase + post.image_path;
    const categoryEmoji = getCategoryEmoji(post.category_slug);
    
    return `
        <div class="post-card">
            <div class="post-header">
                <img src="${avatarUrl}" class="post-avatar">
                <div class="post-user-info">
                    <a href="profile.php?id=${post.user_id}" class="post-username">${escapeHtml(post.nickname || post.username)}</a>
                    <div class="post-time">${timeAgo(post.created_at)}</div>
                </div>
                <span class="category-badge"><span>${categoryEmoji}</span> ${escapeHtml(post.category_name)}</span>
            </div>
            <a href="post.php?id=${post.id}"><img src="${imageUrl}" class="post-image"></a>
            <div class="post-body">
                <h3 class="post-title">${escapeHtml(post.title || 'Untitled')}</h3>
                ${post.description ? `<p class="post-description">${escapeHtml(post.description.substring(0, 150))}</p>` : ''}
                <div class="post-actions">
                    <div class="vote-group">
                        <button class="vote-btn upvote ${upActive}" data-post-id="${post.id}" data-vote="up"><i class="bi bi-arrow-up"></i></button>
                        <span class="vote-count" id="vote-${post.id}">${post.vote_score}</span>
                        <button class="vote-btn downvote ${downActive}" data-post-id="${post.id}" data-vote="down"><i class="bi bi-arrow-down"></i></button>
                    </div>
                    <a href="post.php?id=${post.id}" class="comment-btn"><i class="bi bi-chat"></i> <span>${post.comment_count}</span></a>
                </div>
            </div>
        </div>
    `;
}

function getCategoryEmoji(slug) {
    const emojis = { funny: '😂', animals: '🐾', music: '🎵', movies: '🎬', gaming: '🎮', food: '🍕', travel: '✈️', awesome: '✨' };
    return emojis[slug] || '🏷️';
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function timeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);
    if (seconds < 60) return 'just now';
    if (seconds < 3600) return Math.floor(seconds / 60) + ' min ago';
    if (seconds < 86400) return Math.floor(seconds / 3600) + ' hours ago';
    if (seconds < 604800) return Math.floor(seconds / 86400) + ' days ago';
    return date.toLocaleDateString();
}

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

<?php require_once 'includes/footer.php'; ?>