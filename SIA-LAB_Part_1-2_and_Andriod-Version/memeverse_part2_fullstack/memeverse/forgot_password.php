<?php require_once 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="auth-card">
            <div class="auth-logo">🎭 <span>MemeVerse</span></div>
            <h3>Reset Password</h3>
            <p class="auth-subtitle">Enter your email and we'll send you a reset link.</p>
            <div id="message" class="alert d-none"></div>
            <form id="forgot-form">
                <div class="form-floating mb-3">
                    <input type="email" id="email" name="email" class="form-control" placeholder="Email" required>
                    <label><i class="bi bi-envelope"></i> Email</label>
                </div>
                <button type="submit" class="btn-auth w-100">Send Reset Link</button>
            </form>
            <div class="auth-link mt-3">
                <a href="login.php">Back to Login</a>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('forgot-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const email = document.getElementById('email').value;
    const messageDiv = document.getElementById('message');
    messageDiv.classList.add('d-none');
    
    try {
        const res = await fetch('api/forgot_password.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({email})
        });
        const data = await res.json();
        messageDiv.classList.remove('d-none');
        if (data.success) {
            messageDiv.className = 'alert alert-success';
            messageDiv.innerHTML = 'If the email exists, we\'ve sent a reset link.';
        } else {
            messageDiv.className = 'alert alert-danger';
            messageDiv.innerHTML = data.error || 'Something went wrong.';
        }
    } catch (err) {
        messageDiv.classList.remove('d-none');
        messageDiv.className = 'alert alert-danger';
        messageDiv.innerHTML = 'Network error.';
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>