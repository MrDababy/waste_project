<?php
/**
 * Application Entry Point
 * 
 * Bootstraps and runs the application.
 */

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Load environment variables from .env file
if (file_exists(__DIR__ . '/../.env')) {
    $envFile = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envFile as $line) {
        if (strpos($line, '#') === 0) {
            continue;
        }
        list($key, $value) = explode('=', $line, 2);
        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

// Register autoloader
require_once __DIR__ . '/../app/Core/Autoloader.php';

// Add namespace
\App\Core\Autoloader::addNamespace('App', __DIR__ . '/../app');
\App\Core\Autoloader::register();

// Initialize application
$app = \App\Core\Application::getInstance(__DIR__ . '/..');
$app->init();

// Register services
$app->register('encryption', function() {
    return new \App\Services\EncryptionService();
});

// Load routes
require_once __DIR__ . '/../routes/web.php';

// Run application
$app->run();