<?php
/**
 * Base Controller
 * 
 * Provides common controller functionality including view rendering,
 * JSON responses, and request handling.
 */

namespace App\Core;

use App\Core\Config;
use App\Core\SessionManager;

abstract class Controller
{
    /**
     * @var SessionManager Session manager instance
     */
    protected SessionManager $session;

    /**
     * @var string View directory
     */
    protected string $viewDir;

    /**
     * @var array View data
     */
    protected array $viewData = [];

    /**
     * @var string Layout template
     */
    protected string $layout = 'public';

    /**
     * Initialize controller
     */
    public function __construct()
    {
        $this->session = new SessionManager();
        $this->session->start();
        $this->viewDir = Config::get('paths.views', dirname(__DIR__, 2) . '/views');
    }

    /**
     * Set the layout template
     * 
     * @param string $layout Layout name (without .php extension)
     */
    protected function setLayout(string $layout): void
    {
        $this->layout = $layout;
    }

    /**
     * Render a view
     * 
     * @param string $view View name (without .php extension)
     * @param array $data Data to pass to view
     * @param string|null $layout Layout to use (null to not use layout)
     * @return string Rendered HTML
     */
    protected function render(string $view, array $data = [], ?string $layout = null): string
    {
        // Merge view data
        $viewData = array_merge($this->viewData, $data);
        
        // Extract data for view
        extract($viewData);

        // Start output buffering
        ob_start();

        // Include view
        $viewFile = $this->viewDir . '/' . $view . '.php';
        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: {$viewFile}");
        }
        include $viewFile;

        $content = ob_get_clean();

        // If layout is specified and not null, render layout
        if ($layout !== null) {
            $layoutToUse = $layout ?? $this->layout;
            $layoutFile = $this->viewDir . '/layouts/' . $layoutToUse . '.php';
            
            if (!file_exists($layoutFile)) {
                throw new \RuntimeException("Layout not found: {$layoutFile}");
            }

            ob_start();
            include $layoutFile;
            return ob_get_clean();
        }

        return $content;
    }

    /**
     * Send JSON response
     * 
     * @param array $data Data to encode as JSON
     * @param int $statusCode HTTP status code
     * @param array $headers Additional headers
     */
    protected function json(array $data, int $statusCode = 200, array $headers = []): void
    {
        header_remove();
        
        foreach ($headers as $key => $value) {
            header("{$key}: {$value}");
        }
        
        header('Content-Type: application/json');
        http_response_code($statusCode);
        
        echo json_encode($data);
        exit;
    }

    /**
     * Send success JSON response
     * 
     * @param array $data Additional data
     * @param string $message Success message
     */
    protected function jsonSuccess(array $data = [], string $message = 'Success'): void
    {
        $this->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }

    /**
     * Send error JSON response
     * 
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     * @param array $errors Validation errors
     */
    protected function jsonError(string $message, int $statusCode = 400, array $errors = []): void
    {
        $this->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $statusCode);
    }

    /**
     * Redirect to a URL
     * 
     * @param string $url URL to redirect to
     * @param int $statusCode HTTP status code
     */
    protected function redirect(string $url, int $statusCode = 302): void
    {
        http_response_code($statusCode);
        header("Location: {$url}");
        exit;
    }

    /**
     * Redirect to a named route
     * 
     * @param string $route Route name
     * @param array $params Route parameters
     */
    protected function redirectRoute(string $route, array $params = []): void
    {
        // This will be implemented with router integration
        // For now, just redirect to the route path
        $this->redirect($route);
    }

    /**
     * Get a request parameter
     * 
     * @param string $key Parameter name
     * @param mixed $default Default value
     * @return mixed
     */
    protected function getParam(string $key, $default = null)
    {
        return $_GET[$key] ?? $_POST[$key] ?? $default;
    }

    /**
     * Get all GET parameters
     */
    protected function getGetParams(): array
    {
        return $_GET;
    }

    /**
     * Get all POST parameters
     */
    protected function getPostParams(): array
    {
        return $_POST;
    }

    /**
     * Get all request parameters
     */
    protected function getRequestParams(): array
    {
        return array_merge($_GET, $_POST);
    }

    /**
     * Check if request is POST
     */
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Check if request is GET
     */
    protected function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
     * Check if request is AJAX
     */
    protected function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Add flash message
     * 
     * @param string $type Message type (success, error, warning, info)
     * @param string $message Message content
     */
    protected function flash(string $type, string $message): void
    {
        $this->session->set('flash_' . $type, $message);
    }

    /**
     * Get flash messages
     * 
     * @return array
     */
    protected function getFlashMessages(): array
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
     * Require authentication
     * 
     * @throws \RuntimeException If not authenticated
     */
    protected function requireAuth(): void
    {
        if (!$this->session->isAuthenticated()) {
            $this->redirect('/login');
            exit;
        }
    }

    /**
     * Require admin role
     * 
     * @throws \RuntimeException If not admin
     */
    protected function requireAdmin(): void
    {
        $this->requireAuth();
        
        if ($this->session->getUserRole() !== 'admin') {
            $this->jsonError('Access denied. Admin role required.', 403);
            exit;
        }
    }

    /**
     * Get current user ID
     * 
     * @return int|null
     */
    protected function getCurrentUserId(): ?int
    {
        return $this->session->getUserId();
    }

    /**
     * Get current user role
     * 
     * @return string|null
     */
    protected function getCurrentUserRole(): ?string
    {
        return $this->session->getUserRole();
    }

    /**
     * Generate CSRF token
     */
    protected function csrfToken(): string
    {
        return $this->session->generateCsrfToken();
    }

    /**
     * Validate CSRF token
     * 
     * @param string|null $token Token to validate
     * @return bool
     */
    protected function validateCsrf(?string $token = null): bool
    {
        if ($token === null) {
            $token = $this->getParam('csrf_token');
        }
        
        if ($token === null) {
            return false;
        }
        
        return $this->session->validateCsrfToken($token);
    }
}