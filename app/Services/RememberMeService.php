<?php
/**
 * Remember Me Service
 * 
 * Handles "remember me" functionality with secure tokens.
 */

namespace App\Services;

use App\Models\RememberToken;
use App\Models\User;
use App\Core\Database;

class RememberMeService
{
    /**
     * Create remember token for user
     */
    public function createToken(int $userId): string
    {
        $token = RememberToken::createForUser($userId);
        return $token->token;
    }

    /**
     * Validate remember token
     */
    public function validateToken(string $token): ?User
    {
        $rememberToken = RememberToken::validate($token);
        
        if (!$rememberToken) {
            return null;
        }
        
        return User::find($rememberToken->user_id);
    }

    /**
     * Delete remember token for user
     */
    public function deleteToken(int $userId): void
    {
        RememberToken::deleteOldTokens($userId);
    }

    /**
     * Clean expired tokens
     */
    public function cleanExpired(): void
    {
        RememberToken::cleanExpired();
    }

    /**
     * Attempt automatic login with remember token
     */
    public function attemptAutoLogin(string $token): ?User
    {
        // Clean expired tokens first
        $this->cleanExpired();
        
        $user = $this->validateToken($token);
        
        if ($user && $user->is_active && $user->isVerified()) {
            // Update last login
            $user->updateLastLogin();
            
            // Log auto login
            $auditService = new AuditService();
            $auditService->log(
                $user->id,
                'auto_login',
                'users',
                $user->id,
                'Auto login via remember me token',
                ['ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']
            );
            
            return $user;
        }
        
        return null;
    }

    /**
     * Generate secure remember token
     */
    private function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}