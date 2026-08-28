<?php
/**
 * Authentication Service
 * 
 * Handles user authentication logic.
 */

namespace App\Services;

use App\Core\Database;
use App\Core\SessionManager;
use App\Models\User;
use App\Models\PasswordReset;
use App\Models\RememberToken;
use App\Services\AuditService;

class AuthService
{
    /**
     * @var SessionManager
     */
    private SessionManager $session;

    /**
     * @var AuditService
     */
    private AuditService $auditService;

    /**
     * @var RememberMeService
     */
    private RememberMeService $rememberMeService;

    public function __construct()
    {
        $this->session = new SessionManager();
        $this->session->start();
        $this->auditService = new AuditService();
        $this->rememberMeService = new RememberMeService();
    }

    /**
     * Login user
     */
    public function login(string $identifier, string $password, bool $remember = false): array
    {
        // Find user by username or email
        $user = User::findByUsernameOrEmail($identifier);
        
        if (!$user) {
            $this->auditService->log(
                null,
                'login_failed',
                'users',
                0,
                'Failed login attempt for: ' . $identifier,
                ['ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']
            );
            return [
                'success' => false,
                'message' => 'Invalid username or password.'
            ];
        }

        // Check if user is locked
        if ($user->isLocked()) {
            $remaining = $user->getLockoutRemaining();
            return [
                'success' => false,
                'message' => 'Account is locked. Please try again in ' . $remaining . ' minutes.'
            ];
        }

        // Verify password
        if (!$user->verifyPassword($password)) {
            // Increment login attempts
            $user->incrementLoginAttempts();
            
            $this->auditService->log(
                $user->id,
                'login_failed',
                'users',
                $user->id,
                'Failed password attempt for user: ' . $user->username,
                ['ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']
            );
            
            $attemptsLeft = User::MAX_LOGIN_ATTEMPTS - ($user->login_attempts ?? 0);
            return [
                'success' => false,
                'message' => 'Invalid password. ' . $attemptsLeft . ' attempts remaining.'
            ];
        }

        // Check if user is active
        if (!$user->is_active) {
            $this->auditService->log(
                $user->id,
                'login_failed',
                'users',
                $user->id,
                'Attempted login to inactive account: ' . $user->username,
                ['ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']
            );
            return [
                'success' => false,
                'message' => 'Your account has been deactivated. Please contact an administrator.'
            ];
        }

        // Check if user is verified (if email verification is enabled)
        if (!$user->isVerified()) {
            return [
                'success' => false,
                'message' => 'Please verify your email address before logging in. Check your inbox for the verification link.'
            ];
        }

        // Reset login attempts on successful login
        $user->resetLoginAttempts();

        // Set session
        $this->session->setUser($user->id, $user->role, [
            'username' => $user->username,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'is_verified' => $user->isVerified()
        ]);

        // Update last login
        $user->updateLastLogin();

        // Handle remember me
        if ($remember) {
            $this->rememberMeService->createToken($user->id);
        }

        // Log successful login
        $this->auditService->log(
            $user->id,
            'login',
            'users',
            $user->id,
            'User logged in: ' . $user->username,
            ['ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']
        );

        return [
            'success' => true,
            'message' => 'Login successful.',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'role' => $user->role,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name
            ]
        ];
    }

    /**
     * Logout user
     */
    public function logout(): void
    {
        $userId = $this->session->getUserId();
        
        if ($userId) {
            // Delete remember token
            $this->rememberMeService->deleteToken($userId);
            
            // Log logout
            $this->auditService->log(
                $userId,
                'logout',
                'users',
                $userId,
                'User logged out',
                ['ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']
            );
        }
        
        // Destroy session
        $this->session->destroy();
    }

    /**
     * Register new user
     */
    public function register(array $data): array
    {
        // Check if username exists
        if (User::findByUsername($data['username'])) {
            return [
                'success' => false,
                'message' => 'Username already taken.'
            ];
        }

        // Check if email exists
        if (User::findByEmail($data['email'])) {
            return [
                'success' => false,
                'message' => 'Email already registered.'
            ];
        }

        // Create user
        $user = new User([
            'username' => $data['username'],
            'email' => $data['email'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'role' => 'collector',
            'is_active' => true,
            'is_verified' => false // Require email verification
        ]);
        
        $user->setPassword($data['password']);

        if ($user->save()) {
            // Log registration
            $this->auditService->log(
                $user->id,
                'register',
                'users',
                $user->id,
                'User registered: ' . $user->username,
                ['ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']
            );

            // Send verification email (in production)
            // $this->sendVerificationEmail($user);

            return [
                'success' => true,
                'message' => 'Registration successful. Please verify your email.',
                'user_id' => $user->id
            ];
        }

        return [
            'success' => false,
            'message' => 'Registration failed. Please try again.'
        ];
    }

    /**
     * Send password reset link
     */
    public function sendPasswordResetLink(string $email): array
    {
        $user = User::findByEmail($email);
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'No account found with this email address.'
            ];
        }

        // Create reset token
        $reset = PasswordReset::createForUser($user->id);

        // In production, send email with reset link
        // For now, just return success
        $this->auditService->log(
            $user->id,
            'password_reset_request',
            'users',
            $user->id,
            'Password reset requested for: ' . $user->username,
            ['ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']
        );

        return [
            'success' => true,
            'message' => 'Password reset link sent to your email.',
            'token' => $reset->token // Only for development
        ];
    }

    /**
     * Validate reset token
     */
    public function validateResetToken(string $token): bool
    {
        $reset = PasswordReset::validate($token);
        return $reset !== null;
    }

    /**
     * Reset password
     */
    public function resetPassword(string $token, string $newPassword): array
    {
        $reset = PasswordReset::validate($token);
        
        if (!$reset) {
            return [
                'success' => false,
                'message' => 'Invalid or expired reset token.'
            ];
        }

        $user = $reset->getUser();
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found.'
            ];
        }

        // Update password
        $user->setPassword($newPassword);
        $user->save();

        // Delete reset token
        $reset->delete();

        // Log password change
        $this->auditService->log(
            $user->id,
            'password_reset',
            'users',
            $user->id,
            'Password reset completed for: ' . $user->username,
            ['ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']
        );

        return [
            'success' => true,
            'message' => 'Password reset successfully.'
        ];
    }

    /**
     * Change password
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): array
    {
        $user = User::find($userId);
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found.'
            ];
        }

        // Verify current password
        if (!$user->verifyPassword($currentPassword)) {
            return [
                'success' => false,
                'message' => 'Current password is incorrect.'
            ];
        }

        // Update password
        $user->setPassword($newPassword);
        $user->save();

        // Log password change
        $this->auditService->log(
            $user->id,
            'password_change',
            'users',
            $user->id,
            'Password changed for: ' . $user->username,
            ['ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']
        );

        return [
            'success' => true,
            'message' => 'Password changed successfully.'
        ];
    }

    /**
     * Verify email
     */
    public function verifyEmail(string $token): array
    {
        // Implementation would check verification token
        // For now, mark user as verified
        $sql = "SELECT user_id FROM email_verifications WHERE token = ? AND expires_at > NOW()";
        $result = Database::fetch($sql, [$token]);
        
        if (!$result) {
            return [
                'success' => false,
                'message' => 'Invalid or expired verification token.'
            ];
        }

        $user = User::find($result['user_id']);
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found.'
            ];
        }

        $user->markAsVerified();
        
        // Delete verification token
        $sql = "DELETE FROM email_verifications WHERE token = ?";
        Database::execute($sql, [$token]);

        $this->auditService->log(
            $user->id,
            'email_verified',
            'users',
            $user->id,
            'Email verified for: ' . $user->username,
            ['ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']
        );

        return [
            'success' => true,
            'message' => 'Email verified successfully.'
        ];
    }

    /**
     * Resend verification email
     */
    public function resendVerificationEmail(int $userId): array
    {
        $user = User::find($userId);
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found.'
            ];
        }

        if ($user->isVerified()) {
            return [
                'success' => false,
                'message' => 'Email already verified.'
            ];
        }

        // In production, send verification email
        // For now, just return success

        $this->auditService->log(
            $user->id,
            'verification_resend',
            'users',
            $user->id,
            'Verification email resent for: ' . $user->username,
            ['ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']
        );

        return [
            'success' => true,
            'message' => 'Verification email resent.'
        ];
    }

    /**
     * Get current user
     */
    public function getCurrentUser(): ?User
    {
        $userId = $this->session->getUserId();
        if (!$userId) {
            return null;
        }
        return User::find($userId);
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated(): bool
    {
        return $this->session->isAuthenticated();
    }

    /**
     * Check if session is expired
     */
    public function isSessionExpired(): bool
    {
        return $this->session->isExpired();
    }

    /**
     * Update user activity
     */
    public function updateActivity(): void
    {
        $user = $this->getCurrentUser();
        if ($user) {
            $user->updateActivity();
        }
        $this->session->updateActivity();
    }

    /**
     * Get user by remember token
     */
    public function getUserByRememberToken(string $token): ?User
    {
        return User::findByRememberToken($token);
    }
}