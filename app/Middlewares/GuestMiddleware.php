<?php
/**
 * Guest Middleware
 * 
 * Redirects authenticated users away from guest-only pages.
 */

namespace App\Middleware;

use App\Core\Application;
use App\Core\SessionManager;

class GuestMiddleware
{
    /**
     * @var SessionManager
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
        if ($this->session->isAuthenticated()) {
            $role = $this->session->getUserRole();
            if ($role === 'admin') {
                header('Location: /admin/dashboard');
            } else {
                header('Location: /user/dashboard');
            }
            exit;
        }
    }
}