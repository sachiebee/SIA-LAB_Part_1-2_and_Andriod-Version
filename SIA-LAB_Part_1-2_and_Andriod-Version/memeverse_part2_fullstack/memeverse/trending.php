<?php
require_once 'includes/header.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$stmt = $pdo->prepare("
    SELECT p.*, u.username, u.avatar, u.nickname, u.id as user_id,
           c.name as category_name, c.slug as category_slug,
           (SELECT COALESCE(SUM(vote_value), 0) FROM votes WHERE post_id = p.id) as vote_score,
           (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count
    FROM posts p
    JOIN users u ON p.user_id = u.id
    JOIN categories c ON p.category_id = c.id
    WHERE p.created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
    ORDER BY vote_score DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll();

foreach ($posts as &$post) {
    $post['user_vote'] = getUserVote($pdo, $post['id'], $_SESSION['user_id'] ?? 0);
}

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)");
$countStmt->execute();
$total_posts = $countStmt->fetchColumn();
$has_more = ($offset + $limit) < $total_posts;
?>

<div class="row g-4">
    <div class="col-lg-3"><?php include 'includes/sidebar.php'; ?></div>
    <div class="col-lg-6">
        <div class="profile-header mb-4">
            <div class="d-flex align-items-center gap-3">
                <div style="font-size: 3rem;">🔥</div>
                <div><h2 class="mb-0">Trending Memes</h2><p class="text-muted mb-0">Most upvoted memes from the last 7 days</p></div>
            </div>
        </div>
        
        <?php if (empty($posts)): ?>
            <div class="empty-state"><i class="bi bi-fire"></i><h5>No trending memes yet</h5><p>Check back later for popular memes! 🔥</p></div>
        <?php else: ?>
            <div id="posts-container">
                <?php foreach ($posts as $post): ?>
                    <div class="post-card"><?php include 'includes/post_card.php'; ?></div>
                <?php endforeach; ?>
            </div>
            <?php if ($has_more): ?>
                <div class="load-more-wrapper"><button class="load-more-btn" id="load-more-btn" data-page="2">Load More</button></div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <div class="col-lg-3"></div>
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
            const response = await fetch(`${siteBase}api/trending_posts.php?page=${currentPage}&limit=10`);
            const posts = await response.json();
            if (posts.length > 0) {
                const container = document.getElementById('posts-container');
                posts.forEach(post => { container.insertAdjacentHTML('beforeend', createPostHTML(post)); });
                currentPage++;
                if (posts.length < 10) this.style.display = 'none';
                else { this.disabled = false; this.innerHTML = '<i class="bi bi-arrow-repeat"></i> Load More'; }
            } else { this.style.display = 'none'; }
        } catch (error) { console.error(error); this.disabled = false; this.innerHTML = '<i class="bi bi-arrow-repeat"></i> Try Again'; }
        finally { loading = false; }
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>