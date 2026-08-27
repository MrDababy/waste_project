<?php
/**
 * Session Manager
 * 
 * Secure session management with regeneration, timeout, and CSRF protection.
 */

namespace App\Core;

use App\Core\Config;

class SessionManager
{
    /**
     * @var string Session name
     */
    private string $sessionName;

    /**
     * @var int Session lifetime in seconds
     */
    private int $lifetime;

    /**
     * @var bool Session started flag
     */
    private bool $started = false;

    /**
     * Initialize session management
     */
    public function __construct()
    {
        $this->sessionName = 'plastic_waste_session';
        $this->lifetime = Config::get('session.lifetime', 3600);

        // Configure session settings
        session_name($this->sessionName);
        
        $sessionConfig = Config::get('session', []);
        
        if (isset($sessionConfig['secure']) && $sessionConfig['secure']) {
            ini_set('session.cookie_secure', '1');
        }
        
        if (isset($sessionConfig['httponly'])) {
            ini_set('session.cookie_httponly', $sessionConfig['httponly'] ? '1' : '0');
        }
        
        if (isset($sessionConfig['same_site'])) {
            ini_set('session.cookie_samesite', $sessionConfig['same_site']);
        }
        
        if (isset($sessionConfig['path'])) {
            ini_set('session.cookie_path', $sessionConfig['path']);
        }
        
        if (isset($sessionConfig['domain'])) {
            ini_set('session.cookie_domain', $sessionConfig['domain']);
        }

        // Set garbage collection
        ini_set('session.gc_maxlifetime', $this->lifetime);
        
        // Use secure session ID generation
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
    }

    /**
     * Start the session
     * 
     * @return bool True if session started successfully
     */
    public function start(): bool
    {
        if ($this->started) {
            return true;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
            $this->started = true;
            
            // Regenerate session ID periodically
            $this->regenerateIfNeeded();
            
            return true;
        }

        $this->started = true;
        return true;
    }

    /**
     * Regenerate session ID if needed
     */
    private function regenerateIfNeeded(): void
    {
        if (!isset($_SESSION['regenerated_at'])) {
            $_SESSION['regenerated_at'] = time();
            return;
        }

        // Regenerate every 15 minutes
        if (time() - $_SESSION['regenerated_at'] > 900) {
            $this->regenerate();
            $_SESSION['regenerated_at'] = time();
        }
    }

    /**
     * Regenerate session ID
     * 
     * @param bool $deleteOldSession Delete old session data
     * @return bool
     */
    public function regenerate(bool $deleteOldSession = true): bool
    {
        if (!$this->started) {
            $this->start();
        }

        return session_regenerate_id($deleteOldSession);
    }

    /**
     * Set session value
     * 
     * @param string $key Session key
     * @param mixed $value Value to store
     */
    public function set(string $key, $value): void
    {
        if (!$this->started) {
            $this->start();
        }
        $_SESSION[$key] = $value;
    }

    /**
     * Get session value
     * 
     * @param string $key Session key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        if (!$this->started) {
            $this->start();
        }
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if session key exists
     * 
     * @param string $key Session key
     * @return bool
     */
    public function has(string $key): bool
    {
        if (!$this->started) {
            $this->start();
        }
        return isset($_SESSION[$key]);
    }

    /**
     * Remove session value
     * 
     * @param string $key Session key
     */
    public function remove(string $key): void
    {
        if (!$this->started) {
            $this->start();
        }
        unset($_SESSION[$key]);
    }

    /**
     * Destroy the session
     * 
     * @return bool
     */
    public function destroy(): bool
    {
        if (!$this->started) {
            return true;
        }

        // Clear session data
        $_SESSION = [];
        
        // Delete session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                $this->sessionName,
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // Destroy session
        $result = session_destroy();
        $this->started = false;
        
        return $result;
    }

    /**
     * Check if user is authenticated
     * 
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        if (!$this->started) {
            $this->start();
        }
        
        return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
    }

    /**
     * Get current user ID
     * 
     * @return int|null
     */
    public function getUserId(): ?int
    {
        return $this->get('user_id');
    }

    /**
     * Get current user role
     * 
     * @return string|null
     */
    public function getUserRole(): ?string
    {
        return $this->get('user_role');
    }

    /**
     * Set user authentication
     * 
     * @param int $userId User ID
     * @param string $role User role
     * @param array $additionalData Additional user data
     */
    public function setUser(int $userId, string $role, array $additionalData = []): void
    {
        $this->set('user_id', $userId);
        $this->set('user_role', $role);
        $this->set('user_data', $additionalData);
        $this->set('logged_in_at', time());
        $this->set('last_activity', time());
        
        // Regenerate session ID on login
        $this->regenerate(true);
    }

    /**
     * Clear user authentication
     */
    public function clearUser(): void
    {
        $this->remove('user_id');
        $this->remove('user_role');
        $this->remove('user_data');
        $this->remove('logged_in_at');
        $this->remove('last_activity');
    }

    /**
     * Update last activity timestamp
     */
    public function updateActivity(): void
    {
        $this->set('last_activity', time());
    }

    /**
     * Check if session has expired
     * 
     * @return bool
     */
    public function isExpired(): bool
    {
        $lastActivity = $this->get('last_activity', time());
        return (time() - $lastActivity) > $this->lifetime;
    }

    /**
     * Generate CSRF token
     * 
     * @return string
     */
    public function generateCsrfToken(): string
    {
        if (!$this->started) {
            $this->start();
        }
        
        $token = bin2hex(random_bytes(32));
        $this->set('csrf_token', $token);
        return $token;
    }

    /**
     * Get current CSRF token
     * 
     * @return string|null
     */
    public function getCsrfToken(): ?string
    {
        return $this->get('csrf_token');
    }

    /**
     * Validate CSRF token
     * 
     * @param string $token Token to validate
     * @return bool
     */
    public function validateCsrfToken(string $token): bool
    {
        $storedToken = $this->get('csrf_token');
        if ($storedToken === null) {
            return false;
        }
        return hash_equals($storedToken, $token);
    }

    /**
     * Get session status
     * 
     * @return string
     */
    public function getStatus(): string
    {
        return session_status();
    }

    /**
     * Get all session data
     * 
     * @return array
     */
    public function getAll(): array
    {
        if (!$this->started) {
            $this->start();
        }
        return $_SESSION;
    }
}