<?php
/**
 * Session Service
 * 
 * Handles session management and security.
 */

namespace App\Services;

use App\Core\SessionManager;
use App\Core\Config;

class SessionService
{
    /**
     * @var SessionManager
     */
    private SessionManager $session;

    /**
     * @var int Session lifetime in seconds
     */
    private int $lifetime;

    public function __construct()
    {
        $this->session = new SessionManager();
        $this->session->start();
        $this->lifetime = Config::get('session.lifetime', 3600);
    }

    /**
     * Check if session is valid
     */
    public function isValid(): bool
    {
        if (!$this->session->isAuthenticated()) {
            return false;
        }

        // Check if session has expired
        if ($this->isExpired()) {
            $this->end();
            return false;
        }

        return true;
    }

    /**
     * Check if session is expired
     */
    public function isExpired(): bool
    {
        $lastActivity = $this->session->get('last_activity', time());
        return (time() - $lastActivity) > $this->lifetime;
    }

    /**
     * Update session activity
     */
    public function updateActivity(): void
    {
        $this->session->set('last_activity', time());
    }

    /**
     * End session
     */
    public function end(): void
    {
        $this->session->destroy();
    }

    /**
     * Get remaining session time in seconds
     */
    public function getRemainingTime(): int
    {
        $lastActivity = $this->session->get('last_activity', time());
        $elapsed = time() - $lastActivity;
        return max(0, $this->lifetime - $elapsed);
    }

    /**
     * Get session status
     */
    public function getStatus(): string
    {
        return $this->session->getStatus();
    }

    /**
     * Get session ID
     */
    public function getId(): string
    {
        return session_id();
    }

    /**
     * Regenerate session ID
     */
    public function regenerate(): bool
    {
        return $this->session->regenerate();
    }

    /**
     * Set flash message
     */
    public function flash(string $type, string $message): void
    {
        $this->session->set('flash_' . $type, $message);
    }

    /**
     * Get flash messages
     */
    public function getFlashMessages(): array
    {
        $messages = [];
        $types = ['success', 'error', 'warning', 'info'];
        
        foreach ($types as $type) {
            $key = 'flash_' . $type;
            if ($this->session->has($key)) {
                $messages[$type] = $this->session->get($key);
                $this->session->remove($key);
            }
        }
        
        return $messages;
    }

    /**
     * Check if session is secure (HTTPS)
     */
    public function isSecure(): bool
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    }

    /**
     * Get session data
     */
    public function getData(): array
    {
        return $this->session->getAll();
    }

    /**
     * Check if user has specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->session->getUserRole() === $role;
    }

    /**
     * Get user data from session
     */
    public function getUserData(): array
    {
        return $this->session->get('user_data', []);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is collector
     */
    public function isCollector(): bool
    {
        return $this->hasRole('collector');
    }
}