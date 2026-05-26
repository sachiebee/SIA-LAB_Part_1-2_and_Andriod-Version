<?php require_once 'includes/header.php'; ?>
<?php if (isLoggedIn()) redirect('index.php'); ?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">🎭 <span>MemeVerse</span></div>
        <h3>Welcome Back!</h3>
        <p class="auth-subtitle">Login to continue the fun 🚀</p>
        
        <div id="errorAlert" class="alert alert-danger d-none"></div>
        
        <form id="login-form">
            <div class="form-floating mb-3">
                <input type="text" id="login" name="login" class="form-control" placeholder="Username or Email" required>
                <label><i class="bi bi-person"></i> Username or Email</label>
            </div>
            
            <div class="form-floating password-wrapper mb-3">
                <input type="password" id="loginPassword" name="password" class="form-control" placeholder="Password" required>
                <label><i class="bi bi-lock"></i> Password</label>
                <i class="bi bi-eye-slash password-toggle" id="togglePassword"></i>
            </div>
            <div class="text-center mt-2">
                <a href="forgot_password.php">Forgot password?</a>
            </div>
            <button type="submit" class="btn-auth" id="loginBtn">
                <i class="bi bi-box-arrow-in-right"></i> Login
            </button>
        </form>
        
        <div class="auth-link">
            Don't have an account? <a href="register.php">Create one</a>
        </div>
    </div>
</div>

<style>
.auth-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: calc(100vh - 200px);
    padding: 2rem;
}
.auth-card {
    background: var(--bg-card);
    border-radius: 32px;
    padding: 2.5rem;
    max-width: 450px;
    width: 100%;
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-xl);
}
.auth-logo {
    text-align: center;
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 1rem;
}
.auth-logo span {
    background: linear-gradient(135deg, var(--primary), #ff8e8e);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}
.auth-card h3 {
    text-align: center;
    font-weight: 700;
    margin-bottom: 0.5rem;
    font-size: 1.75rem;
}
.auth-subtitle {
    text-align: center;
    color: var(--text-muted);
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
}
.form-floating {
    margin-bottom: 1rem;
}
.form-floating label {
    color: var(--text-muted);
    font-weight: 500;
}
.form-control {
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    padding: 1rem 0.75rem;
    height: auto;
    color: var(--text-primary);
}
.form-control:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(255,107,107,0.1);
    outline: none;
}
.password-wrapper {
    position: relative;
}
.password-toggle {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: var(--text-muted);
    z-index: 10;
    font-size: 1.1rem;
}
.password-toggle:hover {
    color: var(--primary);
}
.btn-auth {
    background: linear-gradient(135deg, var(--primary), #ff8e8e);
    border: none;
    padding: 0.875rem;
    border-radius: 60px;
    font-weight: 600;
    font-size: 1rem;
    width: 100%;
    margin-top: 1rem;
    transition: all 0.2s;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}
.btn-auth:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}
.auth-link {
    text-align: center;
    margin-top: 1.5rem;
    color: var(--text-muted);
}
.auth-link a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
}
@media (max-width: 768px) {
    .auth-card { padding: 1.5rem; margin: 1rem; }
    .auth-logo { font-size: 2rem; }
    .auth-card h3 { font-size: 1.5rem; }
}
</style>

<script>
document.getElementById('login-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new URLSearchParams(new FormData(this));
    const alertDiv = document.getElementById('errorAlert');
    const loginBtn = document.getElementById('loginBtn');
    
    alertDiv.classList.add('d-none');
    loginBtn.disabled = true;
    loginBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Logging in...';
    
    try {
        const response = await fetch('<?= SITE_URL ?>api/login.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (data.success) {
            window.location.href = '<?= SITE_URL ?>index.php';
        } else {
            alertDiv.classList.remove('d-none');
            alertDiv.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> ' + (data.error || 'Login failed');
            loginBtn.disabled = false;
            loginBtn.innerHTML = '<i class="bi bi-box-arrow-in-right"></i> Login';
        }
    } catch (error) {
        alertDiv.classList.remove('d-none');
        alertDiv.innerHTML = '<i class="bi bi-wifi-off"></i> Network error. Please try again.';
        loginBtn.disabled = false;
        loginBtn.innerHTML = '<i class="bi bi-box-arrow-in-right"></i> Login';
    }
});

document.getElementById('togglePassword').addEventListener('click', function() {
    const password = document.querySelector('#login-form input[name="password"]');
    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);
    this.classList.toggle('bi-eye');
    this.classList.toggle('bi-eye-slash');
});
</script>

<?php require_once 'includes/footer.php'; ?>