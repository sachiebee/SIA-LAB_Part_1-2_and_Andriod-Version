<?php
require_once 'includes/header.php';
if (isLoggedIn()) redirect('index.php');
?>

<div class="auth-wrapper">
    <div class="auth-card" style="max-width: 500px;">
        <div class="auth-logo">🎭 <span>MemeVerse</span></div>
        <h3>Join the Fun!</h3>
        <p class="auth-subtitle">Create your account and start sharing memes 🎉</p>
        
        <div id="errorAlert" class="alert alert-danger d-none"></div>
        <div id="successAlert" class="alert alert-success d-none"></div>
        
        <form id="register-form">
            <div class="form-floating mb-3">
                <input type="text" id="username" name="username" class="form-control" placeholder="Username" required>
                <label><i class="bi bi-person"></i> Username</label>
                <div class="form-hint">Only letters, numbers, and underscores</div>
            </div>
            
            <div class="form-floating mb-3">
                <input type="email" id="email" name="email" class="form-control" placeholder="Email" required>
                <label><i class="bi bi-envelope"></i> Email</label>
            </div>
            
            <div class="form-floating password-wrapper mb-2">
                <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
                <label><i class="bi bi-lock"></i> Password</label>
                <i class="bi bi-eye-slash password-toggle" id="togglePassword"></i>
            </div>
            <div class="password-strength" id="passwordStrength"></div>
            
            <div class="form-floating password-wrapper mb-3">
                <input type="password" id="confirmPassword" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
                <label><i class="bi bi-check-circle"></i> Confirm Password</label>
                <i class="bi bi-eye-slash password-toggle" id="toggleConfirmPassword"></i>
            </div>
            
            <button type="submit" class="btn-auth" id="registerBtn">
                <i class="bi bi-person-plus"></i> Sign Up
            </button>
        </form>
        
        <div class="auth-link">
            Already have an account? <a href="login.php">Login</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirmPassword');
    const strengthDiv = document.getElementById('passwordStrength');
    const registerForm = document.getElementById('register-form');
    const errorDiv = document.getElementById('errorAlert');
    const successDiv = document.getElementById('successAlert');
    const registerBtn = document.getElementById('registerBtn');
    
    // Password strength meter
    passwordInput.addEventListener('input', function() {
        const val = this.value;
        if (val.length === 0) {
            strengthDiv.innerHTML = '';
            return;
        }
        let strength = '', color = '';
        if (val.length < 6) {
            strength = '⚠️ Weak (minimum 6 characters)';
            color = '#ef476f';
        } else if (val.length < 10) {
            strength = '👍 Medium';
            color = '#ffd166';
        } else {
            strength = '✅ Strong';
            color = '#06d6a0';
        }
        if (val.length >= 8 && /[A-Z]/.test(val) && /[0-9]/.test(val) && /[^a-zA-Z0-9]/.test(val)) {
            strength = '💪 Very Strong';
            color = '#06d6a0';
        }
        strengthDiv.innerHTML = strength;
        strengthDiv.style.color = color;
    });
    
    // Password toggles
    document.getElementById('togglePassword').addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.classList.toggle('bi-eye');
        this.classList.toggle('bi-eye-slash');
    });
    
    document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
        const type = confirmInput.getAttribute('type') === 'password' ? 'text' : 'password';
        confirmInput.setAttribute('type', type);
        this.classList.toggle('bi-eye');
        this.classList.toggle('bi-eye-slash');
    });
    
    // Form submission
    registerForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new URLSearchParams(new FormData(this));
        const username = formData.get('username');
        const email = formData.get('email');
        const password = formData.get('password');
        const confirm = formData.get('confirm_password');
        
        errorDiv.classList.add('d-none');
        successDiv.classList.add('d-none');
        
        if (!/^[a-zA-Z0-9_]+$/.test(username)) {
            errorDiv.classList.remove('d-none');
            errorDiv.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> Username can only contain letters, numbers, and underscores';
            return;
        }
        if (username.length < 3) {
            errorDiv.classList.remove('d-none');
            errorDiv.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> Username must be at least 3 characters';
            return;
        }
        if (!email.includes('@') || !email.includes('.')) {
            errorDiv.classList.remove('d-none');
            errorDiv.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> Please enter a valid email address';
            return;
        }
        if (password.length < 6) {
            errorDiv.classList.remove('d-none');
            errorDiv.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> Password must be at least 6 characters';
            return;
        }
        if (password !== confirm) {
            errorDiv.classList.remove('d-none');
            errorDiv.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> Passwords do not match';
            return;
        }
        
        registerBtn.disabled = true;
        registerBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Creating account...';
        
        try {
            const response = await fetch('<?= SITE_URL ?>api/register.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                successDiv.classList.remove('d-none');
                successDiv.innerHTML = '<i class="bi bi-check-circle-fill"></i> Registration successful! Redirecting to login...';
                setTimeout(() => {
                    window.location.href = '<?= SITE_URL ?>login.php';
                }, 2000);
            } else {
                errorDiv.classList.remove('d-none');
                errorDiv.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> ' + (data.error || 'Registration failed');
                registerBtn.disabled = false;
                registerBtn.innerHTML = '<i class="bi bi-person-plus"></i> Sign Up';
            }
        } catch (error) {
            errorDiv.classList.remove('d-none');
            errorDiv.innerHTML = '<i class="bi bi-wifi-off"></i> Network error. Please try again.';
            registerBtn.disabled = false;
            registerBtn.innerHTML = '<i class="bi bi-person-plus"></i> Sign Up';
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>