<?php
/**
 * Main Application Configuration
 * 
 * Loads environment variables and returns configuration array.
 */

return [
    'app' => [
        'name' => getenv('APP_NAME') ?: 'Plastic Waste Monitoring System',
        'environment' => getenv('APP_ENV') ?: 'development',
        'debug' => filter_var(getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOLEAN),
        'url' => getenv('APP_URL') ?: 'http://localhost',
        'timezone' => getenv('APP_TIMEZONE') ?: 'Africa/Dar_es_Salaam',
    ],
    'paths' => [
        'root' => dirname(__DIR__),
        'public' => dirname(__DIR__) . '/public',
        'views' => dirname(__DIR__) . '/views',
        'storage' => dirname(__DIR__) . '/storage',
        'uploads' => dirname(__DIR__) . '/storage/uploads',
        'logs' => dirname(__DIR__) . '/storage/logs',
        'exports' => dirname(__DIR__) . '/storage/exports',
    ],
    'session' => [
        'lifetime' => (int)(getenv('SESSION_LIFETIME') ?: 3600),
        'path' => '/',
        'domain' => getenv('SESSION_DOMAIN') ?: '',
        'secure' => filter_var(getenv('SESSION_SECURE') ?: false, FILTER_VALIDATE_BOOLEAN),
        'httponly' => true,
        'same_site' => 'Strict',
    ],
    'upload' => [
        'max_size' => (int)(getenv('MAX_UPLOAD_SIZE') ?: 5242880),
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'allowed_mime_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp'
        ],
    ],
];