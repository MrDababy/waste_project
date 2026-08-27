<?php
/**
 * Admin Middleware
 * 
 * Ensures user has admin role before accessing routes.
 */

namespace App\Middleware;

use App\Core\Application;
use App\Core\SessionManager;

class AdminMiddleware
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

        if ($this->session->getUserRole() !== 'admin') {
            http_response_code(403);
            echo 'Access denied. Admin role required.';
            exit;
        }
    }
}