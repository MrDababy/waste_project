<?php
/**
 * Remember Token Model
 * 
 * Handles "remember me" functionality tokens.
 */

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class RememberToken extends Model
{
    /**
     * @var string Table name
     */
    protected static string $table = 'remember_tokens';

    /**
     * @var array Fillable columns
     */
    protected static array $fillable = [
        'user_id',
        'token',
        'expires_at'
    ];

    /**
     * @var int Token lifetime in days
     */
    public const TOKEN_LIFETIME = 30;

    /**
     * Create a remember token for user
     */
    public static function createForUser(int $userId): self
    {
        // Delete old tokens
        self::deleteOldTokens($userId);
        
        // Generate token
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . self::TOKEN_LIFETIME . ' days'));
        
        $rememberToken = new self([
            'user_id' => $userId,
            'token' => $token,
            'expires_at' => $expiresAt
        ]);
        $rememberToken->save();
        
        return $rememberToken;
    }

    /**
     * Delete old tokens for user
     */
    public static function deleteOldTokens(int $userId): void
    {
        $sql = "DELETE FROM remember_tokens WHERE user_id = ?";
        Database::execute($sql, [$userId]);
    }

    /**
     * Clean expired tokens
     */
    public static function cleanExpired(): void
    {
        $sql = "DELETE FROM remember_tokens WHERE expires_at < NOW()";
        Database::execute($sql, []);
    }

    /**
     * Validate token
     */
    public static function validate(string $token): ?self
    {
        $sql = "SELECT * FROM remember_tokens 
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
        $sql = "DELETE FROM remember_tokens WHERE id = ?";
        Database::execute($sql, [$this->id]);
        return true;
    }
}