<?php
/**
 * Authentication Middleware
 * 
 * Ensures user is authenticated before accessing routes.
 */

namespace App\Middleware;

use App\Core\Application;
use App\Core\SessionManager;

class AuthMiddleware
{
    /**
     * @var SessionManager Session manager
     */
    private SessionManager $session;

    public function __construct()
    {
        $this->session = Application::getInstance()->getSession();
    }

    /**
     * Handle the middleware
     */
    public function handle(): void
    {
        if (!$this->session->isAuthenticated()) {
            header('Location: /login');
            exit;
        }
    }
}