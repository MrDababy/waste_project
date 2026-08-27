<?php
/**
 * Security Helper
 * 
 * Provides security utility functions.
 */

namespace App\Helpers;

class SecurityHelper
{
    /**
     * Sanitize input string
     * 
     * @param string $input Input string
     * @param bool $removeHtml Remove HTML tags
     * @return string
     */
    public static function sanitize(string $input, bool $removeHtml = true): string
    {
        if ($removeHtml) {
            $input = strip_tags($input);
        }
        return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Escape output for HTML
     * 
     * @param string $output Output string
     * @return string
     */
    public static function escapeHtml(string $output): string
    {
        return htmlspecialchars($output, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Escape output for JavaScript
     * 
     * @param string $output Output string
     * @return string
     */
    public static function escapeJs(string $output): string
    {
        return json_encode($output, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    }

    /**
     * Escape output for SQL (use prepared statements instead)
     */
    public static function escapeSql(string $output): string
    {
        // This should not be used for SQL escaping. Use prepared statements.
        return addslashes($output);
    }

    /**
     * Generate a random string
     * 
     * @param int $length String length
     * @param bool $includeSpecial Include special characters
     * @return string
     */
    public static function randomString(int $length = 32, bool $includeSpecial = true): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        if ($includeSpecial) {
            $chars .= '!@#$%^&*()-_=+';
        }
        
        $bytes = random_bytes($length);
        $charsLength = strlen($chars);
        $result = '';
        
        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[ord($bytes[$i]) % $charsLength];
        }
        
        return $result;
    }

    /**
     * Generate a secure token
     * 
     * @return string
     */
    public static function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Validate password strength
     * 
     * @param string $password Password to validate
     * @return bool
     */
    public static function validatePasswordStrength(string $password): bool
    {
        // Minimum 8 characters
        if (strlen($password) < 8) {
            return false;
        }
        
        // At least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }
        
        // At least one lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }
        
        // At least one number
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }
        
        // At least one special character
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            return false;
        }
        
        return true;
    }

    /**
     * Get password strength message
     * 
     * @param string $password Password to check
     * @return string
     */
    public static function getPasswordStrengthMessage(string $password): string
    {
        if (strlen($password) < 8) {
            return 'Password must be at least 8 characters long.';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return 'Password must contain at least one uppercase letter.';
        }
        if (!preg_match('/[a-z]/', $password)) {
            return 'Password must contain at least one lowercase letter.';
        }
        if (!preg_match('/[0-9]/', $password)) {
            return 'Password must contain at least one number.';
        }
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            return 'Password must contain at least one special character.';
        }
        return 'Password is strong.';
    }

    /**
     * Check if IP is in allowed range
     * 
     * @param string $ip IP address
     * @param array $allowedRanges Allowed IP ranges
     * @return bool
     */
    public static function isIpAllowed(string $ip, array $allowedRanges): bool
    {
        foreach ($allowedRanges as $range) {
            if (self::ipInRange($ip, $range)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if IP is in a CIDR range
     * 
     * @param string $ip IP address
     * @param string $range CIDR range
     * @return bool
     */
    public static function ipInRange(string $ip, string $range): bool
    {
        list($subnet, $bits) = explode('/', $range);
        
        if (strpos($ip, ':') !== false) {
            // IPv6
            $ipBin = inet_pton($ip);
            $subnetBin = inet_pton($subnet);
            if ($ipBin === false || $subnetBin === false) {
                return false;
            }
            $mask = str_repeat('1', $bits) . str_repeat('0', 128 - $bits);
            $maskBin = inet_pton(implode(':', array_map(function($part) {
                return str_pad(dechex(hexdec($part)), 4, '0', STR_PAD_LEFT);
            }, str_split($mask, 4))));
            return ($ipBin & $maskBin) === $subnetBin;
        } else {
            // IPv4
            $ipLong = ip2long($ip);
            $subnetLong = ip2long($subnet);
            $mask = ~((1 << (32 - $bits)) - 1);
            return ($ipLong & $mask) === ($subnetLong & $mask);
        }
    }

    /**
     * Get client IP address
     * 
     * @return string
     */
    public static function getClientIp(): string
    {
        $headers = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($headers as $header) {
            if (isset($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                foreach ($ips as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Check for XSS in input
     * 
     * @param string $input Input to check
     * @return bool
     */
    public static function hasXss(string $input): bool
    {
        return preg_match('/<script.*?>.*?<\/script>/i', $input) ||
               preg_match('/on\w+\s*=\s*["\'].*?["\']/i', $input) ||
               preg_match('/<.*?javascript:.*?>/i', $input);
    }
}