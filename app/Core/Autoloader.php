<?php
/**
 * PSR-4 Autoloader
 * 
 * Registers and handles autoloading of classes following PSR-4 standards.
 */

namespace App\Core;

class Autoloader
{
    /**
     * @var array Registered namespace prefixes and their base directories
     */
    private static array $prefixes = [];

    /**
     * Register the autoloader with spl_autoload_register
     */
    public static function register(): void
    {
        spl_autoload_register([self::class, 'loadClass']);
    }

    /**
     * Add a namespace prefix with base directory
     * 
     * @param string $prefix Namespace prefix (e.g., 'App\\')
     * @param string $baseDir Base directory for the namespace
     * @param bool $prepend Prepend to autoloader stack
     */
    public static function addNamespace(string $prefix, string $baseDir, bool $prepend = false): void
    {
        $prefix = rtrim($prefix, '\\') . '\\';
        $baseDir = rtrim($baseDir, '/') . '/';

        if ($prepend) {
            self::$prefixes = [$prefix => $baseDir] + self::$prefixes;
        } else {
            self::$prefixes[$prefix] = $baseDir;
        }
    }

    /**
     * Load a class
     * 
     * @param string $class Fully qualified class name
     * @return bool True if class loaded, false otherwise
     */
    public static function loadClass(string $class): bool
    {
        $prefix = $class;

        while (($pos = strrpos($prefix, '\\')) !== false) {
            $prefix = substr($class, 0, $pos + 1);
            $relativeClass = substr($class, $pos + 1);

            if (isset(self::$prefixes[$prefix])) {
                $file = self::$prefixes[$prefix] . str_replace('\\', '/', $relativeClass) . '.php';
                
                if (file_exists($file)) {
                    require $file;
                    return true;
                }
            }

            $prefix = rtrim($prefix, '\\');
        }

        return false;
    }

    /**
     * Get all registered prefixes
     */
    public static function getPrefixes(): array
    {
        return self::$prefixes;
    }
}