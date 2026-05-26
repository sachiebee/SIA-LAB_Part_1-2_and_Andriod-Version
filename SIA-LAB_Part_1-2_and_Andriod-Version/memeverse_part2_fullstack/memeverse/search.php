<?php
require_once 'includes/header.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$posts = [];
$users = [];

if ($query && strlen($query) >= 2) {
    $searchTerm = "%$query%";
    
    $stmt = $pdo->prepare("
        SELECT p.*, u.username, u.avatar, u.nickname,
               (SELECT COALESCE(SUM(vote_value), 0) FROM votes WHERE post_id = p.id) as vote_score,
               (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count
        FROM posts p
        JOIN users u ON p.user_id = u.id
        WHERE p.title LIKE ? OR p.description LIKE ? OR u.username LIKE ?
        ORDER BY p.created_at DESC LIMIT 20
    ");
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    $posts = $stmt->fetchAll();
    
    $stmt = $pdo->prepare("SELECT id, username, nickname, avatar FROM users WHERE username LIKE ? OR nickname LIKE ? LIMIT 20");
    $stmt->execute([$searchTerm, $searchTerm]);
    $users = $stmt->fetchAll();
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="search.php">
                    <div class="input-group">
                        <input type="text" name="q" class="form-control form-control-lg" placeholder="Search memes or users..." value="<?= escape($query) ?>" autofocus>
                        <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Search</button>
                    </div>
                </form>
            </div>
        </div>
        
        <?php if ($query): ?>
            <?php if (strlen($query) < 2): ?>
                <div class="alert alert-warning">Please enter at least 2 characters to search.</div>
            <?php else: ?>
                <h4 class="mb-3">Memes (<?= count($posts) ?>)</h4>
                <?php if (empty($posts)): ?>
                    <div class="card mb-4"><div class="card-body text-center py-4"><i class="bi bi-emoji-frown fs-1 text-muted"></i><p class="mt-2">No memes found for "<?= escape($query) ?>"</p></div></div>
                <?php else: ?>
                    <div class="row g-3 mb-4">
                        <?php foreach ($posts as $post): ?>
                            <div class="col-md-4">
                                <div class="card h-100">
                                    <a href="post.php?id=<?= $post['id'] ?>"><img src="<?= SITE_URL . $post['image_path'] ?>" class="card-img-top" style="height: 150px; object-fit: cover;"></a>
                                    <div class="card-body">
                                        <h6 class="card-title"><?= escape($post['title'] ?: 'Untitled') ?></h6>
                                        <small class="text-muted"><i class="bi bi-arrow-up"></i> <?= $post['vote_score'] ?> <i class="bi bi-chat ms-2"></i> <?= $post['comment_count'] ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <h4 class="mb-3">Users (<?= count($users) ?>)</h4>
                <?php if (empty($users)): ?>
                    <div class="card"><div class="card-body text-center py-4"><i class="bi bi-person-x fs-1 text-muted"></i><p class="mt-2">No users found for "<?= escape($query) ?>"</p></div></div>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($users as $user): ?>
                            <a href="profile.php?id=<?= $user['id'] ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex align-items-center">
                                    <img src="<?= getUserAvatar($user, 40) ?>" class="rounded-circle me-2" width="40" height="40">
                                    <div><strong><?= escape($user['nickname'] ?: $user['username']) ?></strong><br><small class="text-muted">@<?= escape($user['username']) ?></small></div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>