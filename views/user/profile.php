<div class="container" style="padding-top: 100px; padding-bottom: 60px;">
    <div class="profile-header">
        <div class="profile-avatar">
            <i class="fas fa-user-circle"></i>
        </div>
        <div class="profile-info">
            <h1><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h1>
            <p class="profile-username">@<?= htmlspecialchars($user['username']) ?></p>
            <p class="profile-email"><i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email']) ?></p>
            <span class="profile-role badge <?= $user['role'] === 'admin' ? 'badge-admin' : 'badge-collector' ?>">
                <?= ucfirst($user['role']) ?>
            </span>
        </div>
        <div class="profile-actions">
            <a href="/user/profile/edit" class="btn btn-secondary">
                <i class="fas fa-edit"></i> Edit Profile
            </a>
            <a href="/user/profile/change-password" class="btn btn-outline">
                <i class="fas fa-key"></i> Change Password
            </a>
        </div>
    </div>

    <div class="profile-stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-weight-hanging"></i></div>
            <div class="stat-value"><?= number_format($stats['total_collected'], 2) ?> kg</div>
            <div class="stat-label">Total Collected</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
            <div class="stat-value"><?= $stats['collection_count'] ?></div>
            <div class="stat-label">Approved Records</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-value"><?= $stats['pending_count'] ?></div>
            <div class="stat-label">Pending Records</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-value"><?= $user['is_verified'] ? 'Yes' : 'No' ?></div>
            <div class="stat-label">Email Verified</div>
        </div>
    </div>

    <?php if (!$user['is_verified']): ?>
        <div class="alert alert-warning" style="margin-top: 30px;">
            <i class="fas fa-exclamation-triangle"></i>
            Your email address is not verified. 
            <form action="/resend-verification" method="POST" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?= $this->csrfToken() ?>">
                <button type="submit" class="btn btn-link">Resend verification email</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<style>
.profile-header {
    display: flex;
    align-items: center;
    gap: 30px;
    padding: 30px;
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
    margin-bottom: 30px;
}

.profile-avatar {
    font-size: 80px;
    color: var(--primary-light);
}

.profile-info h1 {
    font-size: 28px;
    margin-bottom: 4px;
}

.profile-username {
    color: var(--text-secondary);
    margin-bottom: 4px;
}

.profile-email {
    color: var(--text-secondary);
    font-size: 14px;
}

.profile-role {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 50px;
    font-size: 13px;
    font-weight: 600;
    margin-top: 8px;
}

.badge-admin {
    background: #2196F3;
    color: white;
}

.badge-collector {
    background: #0A8E5A;
    color: white;
}

.profile-actions {
    margin-left: auto;
    display: flex;
    gap: 12px;
}

.profile-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}

.profile-stats-grid .stat-card {
    background: white;
    padding: 20px;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    text-align: center;
}

.profile-stats-grid .stat-card .stat-icon {
    font-size: 28px;
    color: var(--primary-light);
    margin-bottom: 8px;
}

.profile-stats-grid .stat-card .stat-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--text-primary);
}

.profile-stats-grid .stat-card .stat-label {
    color: var(--text-secondary);
    font-size: 14px;
}

.alert {
    padding: 16px 20px;
    border-radius: var(--radius);
    border-left: 4px solid #F59E0B;
    background: #FFFBEB;
}

.alert-warning {
    border-color: #F59E0B;
    background: #FFFBEB;
}

@media (max-width: 768px) {
    .profile-header {
        flex-direction: column;
        text-align: center;
    }
    
    .profile-actions {
        margin-left: 0;
        flex-direction: column;
        width: 100%;
    }
    
    .profile-actions .btn {
        width: 100%;
        justify-content: center;
    }
    
    .profile-stats-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 480px) {
    .profile-stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>