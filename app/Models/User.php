<?php
/**
 * User Model
 * 
 * Represents system users with authentication capabilities.
 */

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class User extends Model
{
    /**
     * @var string Table name
     */
    protected static string $table = 'users';

    /**
     * @var array Fillable columns
     */
    protected static array $fillable = [
        'username',
        'email',
        'password_hash',
        'first_name',
        'last_name',
        'role',
        'is_active',
        'is_verified',
        'last_login',
        'last_activity',
        'login_attempts',
        'locked_until'
    ];

    /**
     * @var array Hidden columns
     */
    protected static array $hidden = [
        'password_hash'
    ];

    /**
     * @var int Maximum login attempts before lockout
     */
    public const MAX_LOGIN_ATTEMPTS = 5;

    /**
     * @var int Lockout duration in minutes
     */
    public const LOCKOUT_DURATION = 15;

    /**
     * Find user by username
     */
    public static function findByUsername(string $username): ?self
    {
        $sql = "SELECT * FROM users WHERE username = ? LIMIT 1";
        $result = Database::fetch($sql, [$username]);
        
        if (!$result) {
            return null;
        }
        
        return new static($result);
    }

    /**
     * Find user by email
     */
    public static function findByEmail(string $email): ?self
    {
        $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
        $result = Database::fetch($sql, [$email]);
        
        if (!$result) {
            return null;
        }
        
        return new static($result);
    }

    /**
     * Find user by username or email
     */
    public static function findByUsernameOrEmail(string $identifier): ?self
    {
        $sql = "SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1";
        $result = Database::fetch($sql, [$identifier, $identifier]);
        
        if (!$result) {
            return null;
        }
        
        return new static($result);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is collector
     */
    public function isCollector(): bool
    {
        return $this->role === 'collector';
    }

    /**
     * Get full name
     */
    public function getFullName(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Get user's waste records
     */
    public function getWasteRecords(array $filters = []): array
    {
        $sql = "SELECT * FROM plastic_wastes WHERE collector_id = ?";
        $params = [$this->id];

        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['from_date'])) {
            $sql .= " AND collection_date >= ?";
            $params[] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $sql .= " AND collection_date <= ?";
            $params[] = $filters['to_date'];
        }

        $sql .= " ORDER BY created_at DESC";
        
        return Database::fetchAll($sql, $params);
    }

    /**
     * Get total collected by user
     */
    public function getTotalCollected(): float
    {
        $sql = "SELECT SUM(amount) as total 
                FROM plastic_wastes 
                WHERE collector_id = ? AND status = 'approved'";
        
        $result = Database::fetch($sql, [$this->id]);
        return (float)($result['total'] ?? 0);
    }

    /**
     * Get collection count
     */
    public function getCollectionCount(): int
    {
        $sql = "SELECT COUNT(*) as count 
                FROM plastic_wastes 
                WHERE collector_id = ? AND status = 'approved'";
        
        $result = Database::fetch($sql, [$this->id]);
        return (int)($result['count'] ?? 0);
    }

    /**
     * Verify password
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password_hash);
    }

    /**
     * Set password hash
     */
    public function setPassword(string $password): void
    {
        $this->password_hash = password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Check if user is locked out
     */
    public function isLocked(): bool
    {
        if ($this->locked_until === null) {
            return false;
        }
        
        $lockedUntil = strtotime($this->locked_until);
        return time() < $lockedUntil;
    }

    /**
     * Get remaining lockout time in minutes
     */
    public function getLockoutRemaining(): int
    {
        if ($this->locked_until === null) {
            return 0;
        }
        
        $lockedUntil = strtotime($this->locked_until);
        $remaining = ceil(($lockedUntil - time()) / 60);
        return max(0, $remaining);
    }

    /**
     * Increment login attempts
     */
    public function incrementLoginAttempts(): void
    {
        $this->login_attempts = ($this->login_attempts ?? 0) + 1;
        
        if ($this->login_attempts >= self::MAX_LOGIN_ATTEMPTS) {
            $this->locked_until = date('Y-m-d H:i:s', strtotime('+' . self::LOCKOUT_DURATION . ' minutes'));
            $this->login_attempts = 0;
        }
        
        $this->save();
    }

    /**
     * Reset login attempts
     */
    public function resetLoginAttempts(): void
    {
        $this->login_attempts = 0;
        $this->locked_until = null;
        $this->save();
    }

    /**
     * Update last activity
     */
    public function updateActivity(): void
    {
        $this->last_activity = date('Y-m-d H:i:s');
        $this->save();
    }

    /**
     * Update last login
     */
    public function updateLastLogin(): void
    {
        $this->last_login = date('Y-m-d H:i:s');
        $this->save();
    }

    /**
     * Check if user is verified
     */
    public function isVerified(): bool
    {
        return (bool)$this->is_verified;
    }

    /**
     * Mark as verified
     */
    public function markAsVerified(): void
    {
        $this->is_verified = true;
        $this->save();
    }

    /**
     * Get user by remember token
     */
    public static function findByRememberToken(string $token): ?self
    {
        $sql = "SELECT u.* FROM users u
                JOIN remember_tokens rt ON u.id = rt.user_id
                WHERE rt.token = ? AND rt.expires_at > NOW()";
        
        $result = Database::fetch($sql, [$token]);
        
        if (!$result) {
            return null;
        }
        
        return new static($result);
    }
}