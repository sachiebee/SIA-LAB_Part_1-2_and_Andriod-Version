<?php require_once 'includes/header.php'; ?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="profile-header text-center mb-4">
                <div style="font-size: 4rem;">🔒</div>
                <h1>Privacy Policy</h1>
                <p class="text-muted">Your privacy matters to us</p>
            </div>
            <div class="post-card p-4 mb-4">
                <h3><i class="bi bi-database"></i> Information We Collect</h3>
                <p>When you create an account, we collect:</p>
                <ul><li><strong>Username and email address</strong> -- to identify you</li><li><strong>Hashed password</strong> -- never stored in plain text</li><li><strong>Optional profile information</strong> (bio, avatar, nickname)</li></ul>
                <p class="text-muted">Last updated: <?= date('F j, Y') ?></p>
                <a href="index.php" class="btn-primary-custom">Back to MemeVerse</a>
            </div>
        </div>
    </div>
</div>
<style>.btn-primary-custom { background: linear-gradient(135deg, var(--primary), #ff8e8e); color: white; padding: 0.6rem 1.5rem; border-radius: 60px; text-decoration: none; }</style>
<?php require_once 'includes/footer.php'; ?>