<?php
require_once 'includes/header.php';
if (!isLoggedIn()) redirect('login.php');

$user_id = $_SESSION['user_id'];

// Fetch all notifications with proper joins
$stmt = $pdo->prepare("
    SELECT n.*, 
           u.username, u.avatar, u.nickname,
           p.title as post_title,
           c.comment_text as comment_preview,
           CASE 
               WHEN n.type = 'vote' THEN (SELECT vote_value FROM votes WHERE post_id = n.source_id AND user_id = n.actor_id LIMIT 1)
               ELSE NULL
           END as vote_value
    FROM notifications n
    JOIN users u ON n.actor_id = u.id
    LEFT JOIN posts p ON n.source_id = p.id AND n.type IN ('comment', 'vote')
    LEFT JOIN comments c ON n.source_id = c.id AND n.type = 'reply'
    WHERE n.user_id = ?
    ORDER BY n.created_at DESC
");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();

// Count unread before marking all as read
$unreadCount = 0;
foreach ($notifications as $n) {
    if (!$n['is_read']) $unreadCount++;
}

// Mark all as read when viewing the page
$pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$user_id]);
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h2>
                    <i class="bi bi-bell"></i> Notifications
                    <?php if ($unreadCount > 0): ?>
                        <span class="badge bg-danger ms-2"><?= $unreadCount ?> new</span>
                    <?php endif; ?>
                </h2>
                <?php if (count($notifications) > 0): ?>
                    <span class="badge bg-primary rounded-pill"><?= count($notifications) ?> total</span>
                <?php endif; ?>
            </div>
            
            <?php if (empty($notifications)): ?>
                <div class="empty-state text-center py-5">
                    <i class="bi bi-bell-slash fs-1 text-muted"></i>
                    <p class="mt-3">No notifications yet</p>
                    <p class="text-muted small">When people interact with your posts, you'll see them here</p>
                    <a href="index.php" class="btn-create mt-3">Browse Memes</a>
                </div>
            <?php else: ?>
                <div class="notifications-list">
                    <?php foreach ($notifications as $n):
                        $avatar = getUserAvatar($n, 48);
                        $username = escape($n['nickname'] ?: $n['username']);
                        $time = timeAgo($n['created_at']);
                        $message = '';
                        $link = '#';
                        $icon = '';
                        $iconColor = '';
                        
                        switch ($n['type']) {
                            case 'message':
                                $message = "sent you a message";
                                $link = "messages.php?with=" . $n['actor_id'];
                                $icon = 'bi-envelope-fill';
                                $iconColor = '#0d6efd';
                                break;
                            case 'comment':
                                $message = "commented on your post";
                                $link = "post.php?id=" . $n['source_id'];
                                $icon = 'bi-chat-dots-fill';
                                $iconColor = '#20c997';
                                break;
                            case 'reply':
                                $message = "replied to your comment";
                                $link = "post.php?id=" . $n['source_id'];
                                $icon = 'bi-reply-fill';
                                $iconColor = '#6f42c1';
                                break;
                            case 'vote':
                                $voteValue = $n['vote_value'] ?? 0;
                                if ($voteValue == 1) {
                                    $message = "upvoted your post 👍";
                                    $icon = 'bi-arrow-up-circle-fill';
                                    $iconColor = '#198754';
                                } elseif ($voteValue == -1) {
                                    $message = "downvoted your post 👎";
                                    $icon = 'bi-arrow-down-circle-fill';
                                    $iconColor = '#dc3545';
                                } else {
                                    $message = "voted on your post";
                                    $icon = 'bi-hand-thumbs-up-fill';
                                    $iconColor = '#ffc107';
                                }
                                $link = "post.php?id=" . $n['source_id'];
                                break;
                            case 'follow':
                                $message = "started following you";
                                $link = "profile.php?id=" . $n['actor_id'];
                                $icon = 'bi-person-plus-fill';
                                $iconColor = '#0dcaf0';
                                break;
                            default:
                                $message = "interacted with you";
                                $link = "#";
                                $icon = 'bi-bell-fill';
                                $iconColor = '#6c757d';
                        }
                    ?>
                        <div class="notification-item <?= !$n['is_read'] ? 'unread' : '' ?>" data-id="<?= $n['id'] ?>">
                            <div class="d-flex align-items-start gap-3">
                                <div class="notification-icon" style="background: <?= $iconColor ?>20; border-radius: 50%; width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi <?= $icon ?>" style="color: <?= $iconColor ?>; font-size: 1.4rem;"></i>
                                </div>
                                <img src="<?= $avatar ?>" class="notification-avatar" alt="Avatar">
                                <div class="notification-content flex-grow-1">
                                    <a href="<?= $link ?>" class="notification-link">
                                        <div class="notification-message">
                                            <strong><?= $username ?></strong> <?= $message ?>
                                        </div>
                                        <div class="notification-time">
                                            <i class="bi bi-clock"></i> <?= $time ?>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.notifications-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
.notification-item {
    background: var(--bg-card);
    border-radius: 20px;
    padding: 1.25rem;
    border: 1px solid var(--border-color);
    transition: all 0.2s;
}
.notification-item:hover {
    transform: translateX(4px);
    border-color: var(--primary);
    box-shadow: var(--shadow-sm);
}
.notification-item.unread {
    background: linear-gradient(135deg, rgba(255,107,107,0.05), transparent);
    border-left: 3px solid var(--primary);
}
.notification-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--primary);
}
.notification-content {
    flex: 1;
}
.notification-message {
    color: var(--text-primary);
    font-size: 0.95rem;
    line-height: 1.4;
    margin-bottom: 0.25rem;
}
.notification-message strong {
    color: var(--primary);
}
.notification-time {
    font-size: 0.7rem;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 0.25rem;
}
.notification-icon {
    flex-shrink: 0;
}
.notification-link {
    text-decoration: none;
    color: inherit;
    display: block;
}
.notification-link:hover {
    color: inherit;
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
    transition: all 0.2s;
}
.btn-create:hover {
    transform: translateY(-2px);
    color: white;
}
@media (max-width: 768px) {
    .notification-item {
        padding: 1rem;
    }
    .notification-avatar {
        width: 40px;
        height: 40px;
    }
    .notification-icon {
        width: 40px;
        height: 40px;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>