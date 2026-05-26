<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$current_user = getCurrentUser($pdo);

if ($current_user && !empty($current_user['banned'])) {
    session_destroy();
    redirect('login.php?banned=1');
}

logVisit($pdo, basename($_SERVER['PHP_SELF']));

$unread_count = 0;
$notifications = [];
if ($current_user) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$current_user['id']]);
        $unread_count = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("
            SELECT n.*, u.username, u.avatar, u.nickname
            FROM notifications n
            JOIN users u ON n.actor_id = u.id
            WHERE n.user_id = ?
            ORDER BY n.created_at DESC
            LIMIT 5
        ");
        $stmt->execute([$current_user['id']]);
        $notifications = $stmt->fetchAll();
    } catch (PDOException $e) {
        $unread_count = 0;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>MemeVerse - Share the Laughter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>assets/css/style.css">
    
    <script>
        (function() {
            const savedTheme = localStorage.getItem('memeverse_theme');
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark');
                document.body.classList.add('dark');
            }
        })();
    </script>
</head>
<body>
    <script>
    // Define global siteBase variable - MUST be before any other scripts
    window.siteBase = '<?= SITE_URL ?>';
    console.log('siteBase initialized:', window.siteBase);
</script>
    <nav class="navbar">
        <div class="container">
            <button class="hamburger-menu" id="hamburgerMenu">☰</button>
            <a class="navbar-brand" href="<?= SITE_URL ?>">🎭 <span>MemeVerse</span></a>
            
            <div class="search-wrapper">
                <div class="search-input-container">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" id="navbar-search-input" class="navbar-search-input" placeholder="Search memes or users..." autocomplete="off">
                    <button id="navbar-clear-search" class="navbar-clear-search" style="display: none;">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div id="navbar-search-results" class="navbar-search-results"></div>
            </div>
            
            <div class="nav-right">
                <?php if ($current_user): ?>
                <div class="dropdown notification-dropdown">
                    <button class="nav-icon notification-icon dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-bell"></i>
                        <span id="unread-notification-count" class="notification-badge"><?= $unread_count > 0 ? $unread_count : '' ?></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end notification-menu">
                        <div class="notification-header">
                            <h6>Notifications</h6>
                            <?php if ($unread_count > 0): ?>
                            <div class="notification-actions">
                                <span class="unread-badge"><?= $unread_count ?> new</span>
                                <button type="button" class="btn-mark-read">Mark all read</button>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="notification-list">
                            <?php if (empty($notifications)): ?>
                                <div class="notification-empty">
                                    <i class="bi bi-bell-slash"></i>
                                    <p>No notifications yet</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($notifications as $n): 
                                    $avatar = getUserAvatar($n, 36);
                                    $username = escape($n['nickname'] ?: $n['username']);
                                    $message = '';
                                    $link = '#';
                                    switch ($n['type']) {
                                        case 'comment': $message = "commented on your post"; $link = "post.php?id=" . $n['source_id']; break;
                                        case 'reply': $message = "replied to your comment"; $link = "post.php?id=" . $n['source_id']; break;
                                        case 'vote': $message = "voted on your post"; $link = "post.php?id=" . $n['source_id']; break;
                                        case 'follow': $message = "started following you"; $link = "profile.php?id=" . $n['actor_id']; break;
                                        case 'message': $message = "sent you a message"; $link = "messages.php?with=" . $n['actor_id']; break;
                                    }
                                ?>
                                    <a href="<?= SITE_URL . $link ?>" class="notification-item <?= !$n['is_read'] ? 'unread' : '' ?>">
                                        <img src="<?= $avatar ?>" class="notification-avatar-sm">
                                        <div class="notification-content">
                                            <div class="notification-message"><strong><?= $username ?></strong> <?= $message ?></div>
                                            <div class="notification-time"><?= timeAgo($n['created_at']) ?></div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div class="notification-footer">
                            <a href="<?= SITE_URL ?>notifications.php" class="btn-view-all">View all notifications</a>
                        </div>
                    </div>
                </div>
                
                <?php $msg_unread = $current_user ? getUnreadMessageCount($pdo, $current_user['id']) : 0; ?>
                <a href="<?= SITE_URL ?>messages.php" class="nav-icon position-relative">
                    <i class="bi bi-envelope"></i>
                    <span id="unread-message-count" class="notification-badge"><?= $msg_unread > 0 ? $msg_unread : '' ?></span>
                </a>
                <?php endif; ?>
                
                <button class="nav-icon dark-mode-btn" id="darkModeToggle">
                    <i class="bi bi-moon-stars-fill"></i>
                </button>
                
                <?php if ($current_user): ?>
                    <?php if ($current_user && isAdmin($pdo)): ?>
                    <a href="<?= SITE_URL ?>admin/index.php" class="nav-icon"><i class="bi bi-shield-lock"></i></a>
                    <?php endif; ?>
                    <div class="dropdown">
                        <button class="dropdown-toggle" data-bs-toggle="dropdown">
                            <img src="<?= getUserAvatar($current_user, 40) ?>" class="user-avatar" alt="Avatar">
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= SITE_URL ?>profile.php"><i class="bi bi-person"></i> My Profile</a></li>
                            <li><a class="dropdown-item" href="<?= SITE_URL ?>upload.php"><i class="bi bi-cloud-upload"></i> Upload Meme</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= SITE_URL ?>logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?= SITE_URL ?>login.php" class="btn-register">Login/Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <div class="mobile-sidebar-overlay" id="mobileSidebarOverlay"></div>
    <div class="mobile-sidebar" id="mobileSidebar">
        <div class="mobile-sidebar-header">
            <h5>Menu</h5>
            <button class="close-sidebar" id="closeSidebar"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="mobile-sidebar-content">
            <?php include __DIR__ . '/sidebar.php'; ?>
        </div>
    </div>
    
    <main class="main-content">
        <div class="container">