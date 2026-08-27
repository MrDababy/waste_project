<div class="auth-page">
    <div class="container">
        <div class="auth-card">
            <div class="auth-header">
                <a href="/" class="auth-logo">
                    <i class="fas fa-recycle"></i>
                    <span>Plastic<span>Watch</span></span>
                </a>
                <h2>Welcome Back</h2>
                <p>Login to your account to continue</p>
            </div>
            
            <form action="/login" method="POST" class="auth-form" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?= $this->csrfToken() ?>">
                
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <div class="input-group">
                        <span class="input-icon"><i class="fas fa-user"></i></span>
                        <input type="text" id="username" name="username" 
                               placeholder="Enter your username or email" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-group">
                        <span class="input-icon"><i class="fas fa-lock"></i></span>
                        <input type="password" id="password" name="password" 
                               placeholder="Enter your password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" value="1">
                        <span class="checkmark"></span>
                        Remember me
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Don't have an account? <a href="/register">Register here</a></p>
            </div>
        </div>
    </div>
</div>

<style>
.auth-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bg-light);
    padding: 100px 20px;
}

.auth-card {
    background: white;
    border-radius: var(--radius-lg);
    padding: 48px 40px;
    max-width: 440px;
    width: 100%;
    box-shadow: var(--shadow-xl);
}

.auth-header {
    text-align: center;
    margin-bottom: 32px;
}

.auth-logo {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-size: 28px;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: 16px;
}

.auth-logo i {
    color: var(--primary);
}

.auth-logo span span {
    color: var(--secondary);
}

.auth-header h2 {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 8px;
}

.auth-header p {
    color: var(--text-secondary);
}

.auth-form .form-group {
    margin-bottom: 20px;
}

.auth-form label {
    display: block;
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 6px;
    color: var(--text-primary);
}

.auth-form .input-group {
    position: relative;
    display: flex;
    align-items: center;
}

.auth-form .input-icon {
    position: absolute;
    left: 14px;
    color: var(--text-light);
    font-size: 16px;
}

.auth-form .input-group input {
    width: 100%;
    padding: 12px 14px 12px 44px;
    border: 2px solid #E2E8F0;
    border-radius: var(--radius);
    font-size: 15px;
    transition: var(--transition);
    background: var(--bg-light);
    font-family: var(--font-primary);
}

.auth-form .input-group input:focus {
    outline: none;
    border-color: var(--primary);
    background: white;
    box-shadow: 0 0 0 4px rgba(10, 142, 90, 0.1);
}

.password-toggle {
    position: absolute;
    right: 14px;
    background: none;
    border: none;
    color: var(--text-light);
    cursor: pointer;
    padding: 4px;
}

.password-toggle:hover {
    color: var(--text-primary);
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    font-weight: 400;
    color: var(--text-secondary);
}

.checkbox-label input {
    display: none;
}

.checkmark {
    width: 20px;
    height: 20px;
    border: 2px solid #CBD5E1;
    border-radius: 4px;
    display: inline-block;
    position: relative;
    flex-shrink: 0;
    transition: var(--transition);
}

.checkbox-label input:checked + .checkmark {
    background: var(--primary);
    border-color: var(--primary);
}

.checkbox-label input:checked + .checkmark::after {
    content: '\f00c';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 12px;
}

.btn-block {
    width: 100%;
    justify-content: center;
    padding: 14px;
    font-size: 16px;
}

.auth-footer {
    text-align: center;
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid #E2E8F0;
}

.auth-footer p {
    color: var(--text-secondary);
    font-size: 14px;
}

.auth-footer a {
    color: var(--primary);
    font-weight: 600;
}

.auth-footer a:hover {
    text-decoration: underline;
}

@media (max-width: 480px) {
    .auth-card {
        padding: 32px 20px;
    }
}

function togglePassword() {
    const input = document.getElementById('password');
    const icon = document.querySelector('.password-toggle i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}
</style>