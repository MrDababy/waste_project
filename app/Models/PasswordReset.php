<?php
/**
 * Password Reset Model
 * 
 * Handles password reset tokens.
 */

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class PasswordReset extends Model
{
    /**
     * @var string Table name
     */
    protected static string $table = 'password_resets';

    /**
     * @var array Fillable columns
     */
    protected static array $fillable = [
        'user_id',
        'token',
        'expires_at'
    ];

    /**
     * @var int Token lifetime in minutes
     */
    public const TOKEN_LIFETIME = 60;

    /**
     * Create a password reset token for user
     */
    public static function createForUser(int $userId): self
    {
        // Delete old tokens
        self::deleteOldTokens($userId);
        
        // Generate token
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . self::TOKEN_LIFETIME . ' minutes'));
        
        $reset = new self([
            'user_id' => $userId,
            'token' => $token,
            'expires_at' => $expiresAt
        ]);
        $reset->save();
        
        return $reset;
    }

    /**
     * Delete old tokens for user
     */
    public static function deleteOldTokens(int $userId): void
    {
        $sql = "DELETE FROM password_resets WHERE user_id = ?";
        Database::execute($sql, [$userId]);
    }

    /**
     * Clean expired tokens
     */
    public static function cleanExpired(): void
    {
        $sql = "DELETE FROM password_resets WHERE expires_at < NOW()";
        Database::execute($sql, []);
    }

    /**
     * Validate token
     */
    public static function validate(string $token): ?self
    {
        $sql = "SELECT * FROM password_resets 
                WHERE token = ? AND expires_at > NOW() 
                LIMIT 1";
        
        $result = Database::fetch($sql, [$token]);
        
        if (!$result) {
            return null;
        }
        
        return new self($result);
    }

    /**
     * Delete token
     */
    public function delete(): bool
    {
        $sql = "DELETE FROM password_resets WHERE id = ?";
        Database::execute($sql, [$this->id]);
        return true;
    }

    /**
     * Get user for this reset token
     */
    public function getUser(): ?User
    {
        return User::find($this->user_id);
    }
}