<?php
/**
 * User Model
 * 
 * Represents system users.
 */

namespace App\Models;

use App\Core\Model;

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
        'last_login'
    ];

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
}