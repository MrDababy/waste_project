<?php
/**
 * Configuration Manager
 * 
 * Loads and manages configuration from multiple files.
 */

namespace App\Core;

class Config
{
    /**
     * @var array Loaded configuration data
     */
    private static array $config = [];

    /**
     * Load configuration files
     * 
     * @param string $configDir Directory containing config files
     */
    public static function load(string $configDir): void
    {
        $files = glob($configDir . '/*.php');
        
        foreach ($files as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            self::$config[$name] = require $file;
        }
    }

    /**
     * Get configuration value using dot notation
     * 
     * @param string $key Configuration key (e.g., 'app.name')
     * @param mixed $default Default value if key not found
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $segments = explode('.', $key);
        $config = self::$config;

        foreach ($segments as $segment) {
            if (!is_array($config) || !array_key_exists($segment, $config)) {
                return $default;
            }
            $config = $config[$segment];
        }

        return $config;
    }

    /**
     * Set configuration value
     * 
     * @param string $key Configuration key (e.g., 'app.name')
     * @param mixed $value Value to set
     */
    public static function set(string $key, $value): void
    {
        $segments = explode('.', $key);
        $config = &self::$config;

        foreach ($segments as $segment) {
            if (!is_array($config)) {
                $config = [];
            }
            if (!isset($config[$segment])) {
                $config[$segment] = [];
            }
            $config = &$config[$segment];
        }

        $config = $value;
    }

    /**
     * Check if configuration key exists
     * 
     * @param string $key Configuration key
     * @return bool
     */
    public static function has(string $key): bool
    {
        return self::get($key) !== null;
    }

    /**
     * Get all configuration
     */
    public static function all(): array
    {
        return self::$config;
    }
}