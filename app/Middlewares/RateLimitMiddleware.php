<?php
/**
 * Rate Limit Middleware
 * 
 * Limits login attempts to prevent brute force attacks.
 */

namespace App\Middleware;

use App\Core\Database;

class RateLimitMiddleware
{
    /**
     * @var int Maximum attempts per IP
     */
    private const MAX_ATTEMPTS = 10;

    /**
     * @var int Time window in minutes
     */
    private const WINDOW_MINUTES = 15;

    /**
     * Handle the middleware
     */
    public function handle(): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        // Get attempt count
        $sql = "SELECT COUNT(*) as attempts 
                FROM rate_limits 
                WHERE ip_address = ? 
                AND created_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)";
        
        $result = Database::fetch($sql, [$ip, self::WINDOW_MINUTES]);
        $attempts = (int)($result['attempts'] ?? 0);
        
        if ($attempts >= self::MAX_ATTEMPTS) {
            http_response_code(429);
            echo json_encode([
                'success' => false,
                'message' => 'Too many login attempts. Please try again later.',
                'retry_after' => self::WINDOW_MINUTES
            ]);
            exit;
        }
        
        // Log this attempt
        $sql = "INSERT INTO rate_limits (ip_address, created_at) VALUES (?, NOW())";
        Database::execute($sql, [$ip]);
    }

    /**
     * Reset attempts for IP
     */
    public static function resetAttempts(string $ip): void
    {
        $sql = "DELETE FROM rate_limits WHERE ip_address = ?";
        Database::execute($sql, [$ip]);
    }

    /**
     * Clean old attempts
     */
    public static function cleanOldAttempts(): void
    {
        $sql = "DELETE FROM rate_limits 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)";
        Database::execute($sql, []);
    }
}