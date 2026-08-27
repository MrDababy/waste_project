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

class AuthService
{
    /**
     * @var SessionManager
     */
    private SessionManager $session;

    public function __construct()
    {
        $this->session = new SessionManager();
        $this->session->start();
    }

    /**
     * Login user
     */
    public function login(string $username, string $password, bool $remember = false): array
    {
        // Find user
        $user = User::findByUsername($username);
        
        if (!$user || !password_verify($password, $user->password_hash)) {
            return [
                'success' => false,
                'message' => 'Invalid username or password.'
            ];
        }

        // Check if user is active
        if (!$user->is_active) {
            return [
                'success' => false,
                'message' => 'Your account has been deactivated. Please contact an administrator.'
            ];
        }

        // Set session
        $this->session->setUser($user->id, $user->role, [
            'username' => $user->username,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name
        ]);

        // Update last login
        $user->last_login = date('Y-m-d H:i:s');
        $user->save();

        // Log action
        $this->logAction($user->id, 'login', 'User logged in');

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
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'role' => 'collector'
        ]);

        if ($user->save()) {
            // Log action
            $this->logAction($user->id, 'register', 'User registered');
            
            return [
                'success' => true,
                'message' => 'Registration successful.',
                'user_id' => $user->id
            ];
        }

        return [
            'success' => false,
            'message' => 'Registration failed. Please try again.'
        ];
    }

    /**
     * Logout user
     */
    public function logout(): void
    {
        $userId = $this->session->getUserId();
        if ($userId) {
            $this->logAction($userId, 'logout', 'User logged out');
        }
        $this->session->destroy();
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated(): bool
    {
        return $this->session->isAuthenticated();
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
     * Log action to audit
     */
    private function logAction(int $userId, string $action, string $description): void
    {
        $sql = "INSERT INTO audit_logs (user_id, action, target_table, target_id, description, ip_address, user_agent) 
                VALUES (?, ?, 'users', ?, ?, ?, ?)";
        
        Database::execute($sql, [
            $userId,
            $action,
            $userId,
            $description,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }
}