<?php
/**
 * Application
 * 
 * Main application bootstrap and dependency container.
 */

namespace App\Core;

use App\Core\Config;
use App\Core\Router;
use App\Core\Database;
use App\Core\SessionManager;

class Application
{
    /**
     * @var Application Application instance (Singleton)
     */
    private static ?Application $instance = null;

    /**
     * @var Router Router instance
     */
    private Router $router;

    /**
     * @var SessionManager Session manager instance
     */
    private SessionManager $session;

    /**
     * @var string Application root directory
     */
    private string $rootDir;

    /**
     * @var bool Whether application is running
     */
    private bool $isRunning = false;

    /**
     * @var array Registered services
     */
    private array $services = [];

    /**
     * Private constructor (Singleton)
     */
    private function __construct(string $rootDir)
    {
        $this->rootDir = $rootDir;
        $this->router = new Router();
        $this->session = new SessionManager();
    }

    /**
     * Get application instance
     * 
     * @param string|null $rootDir Application root directory
     * @return Application
     */
    public static function getInstance(?string $rootDir = null): Application
    {
        if (self::$instance === null) {
            if ($rootDir === null) {
                throw new \RuntimeException('Root directory required for initializing application.');
            }
            self::$instance = new self($rootDir);
        }
        return self::$instance;
    }

    /**
     * Initialize the application
     */
    public function init(): void
    {
        // Load configuration
        Config::load($this->rootDir . '/config');
        
        // Set timezone
        $timezone = Config::get('app.timezone', 'Africa/Dar_es_Salaam');
        date_default_timezone_set($timezone);
        
        // Set error reporting
        if (Config::get('app.debug', false)) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }
        
        // Start session
        $this->session->start();
        
        // Initialize database connection
        Database::getConnection();
    }

    /**
     * Register a service
     * 
     * @param string $name Service name
     * @param callable $factory Service factory
     */
    public function register(string $name, callable $factory): void
    {
        $this->services[$name] = $factory;
    }

    /**
     * Get a service
     * 
     * @param string $name Service name
     * @return mixed
     */
    public function get(string $name)
    {
        if (!isset($this->services[$name])) {
            throw new \RuntimeException("Service not found: {$name}");
        }
        return $this->services[$name]();
    }

    /**
     * Get the router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }

    /**
     * Get the session manager
     */
    public function getSession(): SessionManager
    {
        return $this->session;
    }

    /**
     * Get the root directory
     */
    public function getRootDir(): string
    {
        return $this->rootDir;
    }

    /**
     * Run the application
     */
    public function run(): void
    {
        if ($this->isRunning) {
            return;
        }

        $this->isRunning = true;

        // Get request method and URI
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // Dispatch the request
        $response = $this->router->dispatch($method, $uri);
        
        // If response is not null, output it
        if ($response !== null) {
            echo $response;
        }
    }

    /**
     * Check if application is running
     */
    public function isRunning(): bool
    {
        return $this->isRunning;
    }

    /**
     * Shutdown the application
     */
    public function shutdown(): void
    {
        $this->isRunning = false;
        
        // Close database connection
        Database::close();
        
        // Write session data
        session_write_close();
    }
}