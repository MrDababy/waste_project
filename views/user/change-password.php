<div class="container" style="padding-top: 100px; padding-bottom: 60px; max-width: 600px;">
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-key"></i> Change Password</h2>
            <p>Update your password to keep your account secure</p>
        </div>
        
        <form action="/user/profile/change-password" method="POST" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <div class="input-group">
                    <span class="input-icon"><i class="fas fa-lock"></i></span>
                    <input type="password" id="current_password" name="current_password" 
                           placeholder="Enter your current password" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="new_password">New Password</label>
                <div class="input-group">
                    <span class="input-icon"><i class="fas fa-lock"></i></span>
                    <input type="password" id="new_password" name="new_password" 
                           placeholder="Enter new password" required>
                </div>
                <div class="password-hint">
                    <small>Must be at least 8 characters with uppercase, lowercase, number, and special character</small>
                </div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <div class="input-group">
                    <span class="input-icon"><i class="fas fa-lock"></i></span>
                    <input type="password" id="confirm_password" name="confirm_password" 
                           placeholder="Confirm new password" required>
                </div>
            </div>
            
            <div class="form-actions">
                <a href="/user/profile" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Change Password
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.card {
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-xl);
    padding: 40px;
}

.card-header {
    margin-bottom: 30px;
    text-align: center;
}

.card-header h2 {
    font-size: 28px;
    margin-bottom: 8px;
}

.card-header p {
    color: var(--text-secondary);
}

.form-actions {
    display: flex;
    gap: 16px;
    margin-top: 24px;
}

.form-actions .btn {
    flex: 1;
    justify-content: center;
}

@media (max-width: 480px) {
    .card {
        padding: 24px;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>