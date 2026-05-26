<?php
require_once 'includes/header.php';
$token = $_GET['token'] ?? '';

if (empty($token)) {
    redirect('index.php');
}

$stmt = $pdo->prepare("
    SELECT pr.id, pr.user_id, u.username
    FROM password_resets pr
    JOIN users u ON pr.user_id = u.id
    WHERE pr.token = ? AND pr.used = 0 AND pr.expires_at > NOW()
");
$stmt->execute([$token]);
$reset = $stmt->fetch();

if (!$reset) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Invalid or expired reset link.</div><a href='forgot_password.php' class='btn btn-primary'>Request new link</a></div>";
    require_once 'includes/footer.php';
    exit;
}
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="auth-card">
            <div class="auth-logo">🎭 <span>MemeVerse</span></div>
            <h3>Set New Password</h3>
            <p class="auth-subtitle">Create a new password for <?= escape($reset['username']) ?></p>
            <div id="message" class="alert d-none"></div>
            <form id="reset-form">
                <input type="hidden" id="token" value="<?= htmlspecialchars($token) ?>">
                <div class="form-floating mb-3">
                    <input type="password" id="password" class="form-control" placeholder="New Password" required>
                    <label><i class="bi bi-lock"></i> New Password</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="password" id="confirm" class="form-control" placeholder="Confirm Password" required>
                    <label><i class="bi bi-check-circle"></i> Confirm Password</label>
                </div>
                <button type="submit" class="btn-auth w-100">Reset Password</button>
            </form>
            <div class="auth-link mt-3">
                <a href="login.php">Back to Login</a>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('reset-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('confirm').value;
    const token = document.getElementById('token').value;
    const messageDiv = document.getElementById('message');
    
    if (password !== confirm) {
        messageDiv.classList.remove('d-none');
        messageDiv.className = 'alert alert-danger';
        messageDiv.innerHTML = 'Passwords do not match.';
        return;
    }
    if (password.length < 6) {
        messageDiv.classList.remove('d-none');
        messageDiv.className = 'alert alert-danger';
        messageDiv.innerHTML = 'Password must be at least 6 characters.';
        return;
    }
    
    try {
        const res = await fetch('api/reset_password.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({token, password})
        });
        const data = await res.json();
        if (data.success) {
            messageDiv.classList.remove('d-none');
            messageDiv.className = 'alert alert-success';
            messageDiv.innerHTML = 'Password reset successfully! Redirecting...';
            setTimeout(() => window.location.href = 'login.php', 2000);
        } else {
            messageDiv.classList.remove('d-none');
            messageDiv.className = 'alert alert-danger';
            messageDiv.innerHTML = data.error || 'Reset failed.';
        }
    } catch (err) {
        messageDiv.classList.remove('d-none');
        messageDiv.className = 'alert alert-danger';
        messageDiv.innerHTML = 'Network error.';
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>