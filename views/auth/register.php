<div class="auth-page">
    <div class="container">
        <div class="auth-card">
            <div class="auth-header">
                <a href="/" class="auth-logo">
                    <i class="fas fa-recycle"></i>
                    <span>Plastic<span>Watch</span></span>
                </a>
                <h2>Create Account</h2>
                <p>Join the environmental monitoring initiative</p>
            </div>
            
            <form action="/register" method="POST" class="auth-form" id="registerForm">
                <input type="hidden" name="csrf_token" value="<?= $this->csrfToken() ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <div class="input-group">
                            <span class="input-icon"><i class="fas fa-user"></i></span>
                            <input type="text" id="first_name" name="first_name" 
                                   placeholder="First name" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <div class="input-group">
                            <span class="input-icon"><i class="fas fa-user"></i></span>
                            <input type="text" id="last_name" name="last_name" 
                                   placeholder="Last name" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-group">
                        <span class="input-icon"><i class="fas fa-user-tag"></i></span>
                        <input type="text" id="username" name="username" 
                               placeholder="Choose a username" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-group">
                        <span class="input-icon"><i class="fas fa-envelope"></i></span>
                        <input type="email" id="email" name="email" 
                               placeholder="Enter your email" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-group">
                        <span class="input-icon"><i class="fas fa-lock"></i></span>
                        <input type="password" id="password" name="password" 
                               placeholder="Create a password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-hint">
                        <small>Must be at least 8 characters with uppercase, lowercase, number, and special character</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password_confirm">Confirm Password</label>
                    <div class="input-group">
                        <span class="input-icon"><i class="fas fa-lock"></i></span>
                        <input type="password" id="password_confirm" name="password_confirm" 
                               placeholder="Confirm your password" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="terms" value="1" required>
                        <span class="checkmark"></span>
                        I agree to the <a href="#" style="color: var(--primary);">Terms of Service</a> and <a href="#" style="color: var(--primary);">Privacy Policy</a>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Already have an account? <a href="/login">Login here</a></p>
            </div>
        </div>
    </div>
</div>

<style>
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.password-hint small {
    color: var(--text-light);
    font-size: 12px;
    margin-top: 4px;
    display: block;
}

@media (max-width: 480px) {
    .form-row {
        grid-template-columns: 1fr;
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