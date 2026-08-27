<?php
/**
 * Router
 * 
 * Handles URL routing and dispatch to controllers.
 */

namespace App\Core;

use App\Core\Config;

class Router
{
    /**
     * @var array Registered routes
     */
    private array $routes = [];

    /**
     * @var array Route groups
     */
    private array $groups = [];

    /**
     * @var string Current route prefix
     */
    private string $prefix = '';

    /**
     * @var array Current route middleware
     */
    private array $middleware = [];

    /**
     * Register a GET route
     * 
     * @param string $path Route path
     * @param string|array $handler Controller and method
     * @param array $options Route options
     */
    public function get(string $path, $handler, array $options = []): void
    {
        $this->addRoute('GET', $path, $handler, $options);
    }

    /**
     * Register a POST route
     * 
     * @param string $path Route path
     * @param string|array $handler Controller and method
     * @param array $options Route options
     */
    public function post(string $path, $handler, array $options = []): void
    {
        $this->addRoute('POST', $path, $handler, $options);
    }

    /**
     * Register a PUT route
     * 
     * @param string $path Route path
     * @param string|array $handler Controller and method
     * @param array $options Route options
     */
    public function put(string $path, $handler, array $options = []): void
    {
        $this->addRoute('PUT', $path, $handler, $options);
    }

    /**
     * Register a DELETE route
     * 
     * @param string $path Route path
     * @param string|array $handler Controller and method
     * @param array $options Route options
     */
    public function delete(string $path, $handler, array $options = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $options);
    }

    /**
     * Register a route for multiple methods
     * 
     * @param array $methods HTTP methods
     * @param string $path Route path
     * @param string|array $handler Controller and method
     * @param array $options Route options
     */
    public function match(array $methods, string $path, $handler, array $options = []): void
    {
        foreach ($methods as $method) {
            $this->addRoute($method, $path, $handler, $options);
        }
    }

    /**
     * Add a route
     * 
     * @param string $method HTTP method
     * @param string $path Route path
     * @param string|array $handler Controller and method
     * @param array $options Route options
     */
    private function addRoute(string $method, string $path, $handler, array $options = []): void
    {
        // Apply prefix
        $path = $this->prefix . $path;
        
        // Parse handler
        if (is_string($handler) && strpos($handler, '@') !== false) {
            $handler = explode('@', $handler);
        }
        
        if (!is_array($handler) || count($handler) !== 2) {
            throw new \InvalidArgumentException('Invalid route handler format. Use Controller@method or [Controller::class, \'method\']');
        }

        // Merge middleware
        $options['middleware'] = array_merge(
            $this->middleware,
            $options['middleware'] ?? []
        );

        $this->routes[$method][$path] = [
            'handler' => $handler,
            'options' => $options,
            'path' => $path,
            'method' => $method
        ];
    }

    /**
     * Create a route group
     * 
     * @param string $prefix Route prefix
     * @param callable $callback Group callback
     * @param array $middleware Middleware for group
     */
    public function group(string $prefix, callable $callback, array $middleware = []): void
    {
        $previousPrefix = $this->prefix;
        $previousMiddleware = $this->middleware;
        
        $this->prefix = $previousPrefix . $prefix;
        $this->middleware = array_merge($this->middleware, $middleware);
        
        $callback($this);
        
        $this->prefix = $previousPrefix;
        $this->middleware = $previousMiddleware;
    }

    /**
     * Dispatch the request
     * 
     * @param string $method HTTP method
     * @param string $uri Request URI
     * @return mixed
     */
    public function dispatch(string $method, string $uri)
    {
        // Remove query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        // Normalize URI
        $uri = '/' . ltrim($uri, '/');

        // Check for exact match
        if (isset($this->routes[$method][$uri])) {
            return $this->executeRoute($this->routes[$method][$uri]);
        }

        // Check for route with parameters
        foreach ($this->routes[$method] as $routePath => $route) {
            $pattern = $this->convertPathToRegex($routePath);
            
            if (preg_match($pattern, $uri, $matches)) {
                // Extract parameters
                $params = [];
                foreach ($matches as $key => $value) {
                    if (!is_int($key)) {
                        $params[$key] = $value;
                    }
                }
                return $this->executeRoute($route, $params);
            }
        }

        // No route found
        http_response_code(404);
        return "404 - Page not found";
    }

    /**
     * Execute a route
     * 
     * @param array $route Route configuration
     * @param array $params Route parameters
     * @return mixed
     */
    private function executeRoute(array $route, array $params = [])
    {
        $handler = $route['handler'];
        $options = $route['options'];
        
        // Apply middleware
        if (!empty($options['middleware'])) {
            $this->executeMiddleware($options['middleware']);
        }
        
        // Instantiate controller
        $controllerClass = $handler[0];
        $method = $handler[1];
        
        if (!class_exists($controllerClass)) {
            throw new \RuntimeException("Controller class not found: {$controllerClass}");
        }
        
        $controller = new $controllerClass();
        
        if (!method_exists($controller, $method)) {
            throw new \RuntimeException("Method not found: {$method} in {$controllerClass}");
        }
        
        // Execute controller method
        return $controller->$method($params);
    }

    /**
     * Execute middleware
     * 
     * @param array $middleware Middleware list
     */
    private function executeMiddleware(array $middleware): void
    {
        foreach ($middleware as $mw) {
            if (is_string($mw)) {
                if (!class_exists($mw)) {
                    throw new \RuntimeException("Middleware class not found: {$mw}");
                }
                $instance = new $mw();
                if (method_exists($instance, 'handle')) {
                    $instance->handle();
                }
            } elseif (is_callable($mw)) {
                $mw();
            }
        }
    }

    /**
     * Convert a path with parameters to a regex pattern
     * 
     * @param string $path Route path
     * @return string
     */
    private function convertPathToRegex(string $path): string
    {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    /**
     * Get all registered routes
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Get routes by method
     * 
     * @param string $method HTTP method
     * @return array
     */
    public function getRoutesByMethod(string $method): array
    {
        return $this->routes[$method] ?? [];
    }

    /**
     * Generate a URL for a route
     * 
     * @param string $route Route path
     * @param array $params Route parameters
     * @return string
     */
    public function url(string $route, array $params = []): string
    {
        $url = $route;
        
        foreach ($params as $key => $value) {
            $url = str_replace('{' . $key . '}', $value, $url);
        }
        
        return $url;
    }
}