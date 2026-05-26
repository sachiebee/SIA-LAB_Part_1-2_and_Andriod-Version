<?php
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$current_slug = $_GET['slug'] ?? '';
?>
<div class="sidebar-card">
    <div class="sidebar-title"><i class="bi bi-compass"></i> EXPLORE</div>
    <a href="<?= SITE_URL ?>" class="category-link <?= empty($current_slug) ? 'active' : '' ?>">
        <span class="emoji">🏠</span> All Memes
    </a>
    <div class="category-divider"></div>
    <a href="<?= SITE_URL ?>trending.php" class="category-link"><span class="emoji">🔥</span> Trending</a>
    <a href="<?= SITE_URL ?>latest.php" class="category-link"><span class="emoji">🕒</span> Latest</a>
    <div class="category-divider"></div>
    <div class="category-list">
        <?php foreach ($categories as $cat): ?>
            <a href="<?= SITE_URL ?>category.php?slug=<?= $cat['slug'] ?>"
               class="category-link <?= $current_slug === $cat['slug'] ? 'active' : '' ?>">
                <span class="emoji"><?= getCategoryEmoji($cat['slug']) ?></span>
                <?= escape($cat['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<div class="sidebar-card">
    <div class="sidebar-title"><i class="bi bi-stars"></i> QUICK LINKS</div>
    <div class="category-list">
        <?php if (isLoggedIn()): ?>
            <a href="<?= SITE_URL ?>upload.php" class="category-link"><span class="emoji">📤</span> Upload Meme</a>
            <a href="<?= SITE_URL ?>profile.php" class="category-link"><span class="emoji">👤</span> My Profile</a>
        <?php else: ?>
            <a href="<?= SITE_URL ?>login.php" class="category-link"><span class="emoji">🔑</span> Login</a>
            <a href="<?= SITE_URL ?>register.php" class="category-link"><span class="emoji">📝</span> Register</a>
        <?php endif; ?>
    </div>
</div>

<style>
.category-divider {
    height: 1px;
    background: var(--border-color);
    margin: 0.5rem 0;
}
</style>