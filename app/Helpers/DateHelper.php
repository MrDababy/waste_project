<?php
/**
 * Date Helper
 * 
 * Provides date utility functions.
 */

namespace App\Helpers;

use DateTime;
use DateTimeZone;

class DateHelper
{
    /**
     * Format a date for display
     * 
     * @param string $date Date string
     * @param string $format Output format
     * @return string
     */
    public static function format(string $date, string $format = 'd M Y'): string
    {
        $dt = new DateTime($date);
        return $dt->format($format);
    }

    /**
     * Format a datetime for display
     * 
     * @param string $datetime Datetime string
     * @param string $format Output format
     * @return string
     */
    public static function formatDatetime(string $datetime, string $format = 'd M Y H:i:s'): string
    {
        $dt = new DateTime($datetime);
        return $dt->format($format);
    }

    /**
     * Format for database
     * 
     * @param string $date Date string
     * @return string
     */
    public static function formatDb(string $date): string
    {
        $dt = new DateTime($date);
        return $dt->format('Y-m-d');
    }

    /**
     * Format datetime for database
     * 
     * @param string $datetime Datetime string
     * @return string
     */
    public static function formatDbDatetime(string $datetime): string
    {
        $dt = new DateTime($datetime);
        return $dt->format('Y-m-d H:i:s');
    }

    /**
     * Get current date
     * 
     * @param string $format Output format
     * @return string
     */
    public static function now(string $format = 'Y-m-d H:i:s'): string
    {
        $dt = new DateTime();
        return $dt->format($format);
    }

    /**
     * Get current date for database
     * 
     * @return string
     */
    public static function nowDb(): string
    {
        return self::now('Y-m-d H:i:s');
    }

    /**
     * Get start of month
     * 
     * @param int $month Month (1-12)
     * @param int $year Year
     * @return DateTime
     */
    public static function startOfMonth(int $month, int $year): DateTime
    {
        return new DateTime("{$year}-{$month}-01 00:00:00");
    }

    /**
     * Get end of month
     * 
     * @param int $month Month (1-12)
     * @param int $year Year
     * @return DateTime
     */
    public static function endOfMonth(int $month, int $year): DateTime
    {
        return new DateTime("{$year}-{$month}-01 23:59:59");
    }

    /**
     * Get start of year
     * 
     * @param int $year Year
     * @return DateTime
     */
    public static function startOfYear(int $year): DateTime
    {
        return new DateTime("{$year}-01-01 00:00:00");
    }

    /**
     * Get end of year
     * 
     * @param int $year Year
     * @return DateTime
     */
    public static function endOfYear(int $year): DateTime
    {
        return new DateTime("{$year}-12-31 23:59:59");
    }

    /**
     * Get month name
     * 
     * @param int $month Month number (1-12)
     * @param bool $short Use short name
     * @return string
     */
    public static function getMonthName(int $month, bool $short = false): string
    {
        $months = [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December'
        ];
        
        $name = $months[$month] ?? '';
        return $short ? substr($name, 0, 3) : $name;
    }

    /**
     * Get months between dates
     * 
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array
     */
    public static function getMonthsBetween(string $startDate, string $endDate): array
    {
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        
        $months = [];
        $current = clone $start;
        
        while ($current <= $end) {
            $months[] = $current->format('Y-m');
            $current->modify('+1 month');
        }
        
        return $months;
    }

    /**
     * Get years between dates
     * 
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array
     */
    public static function getYearsBetween(string $startDate, string $endDate): array
    {
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        
        $years = [];
        $current = clone $start;
        $current->modify('first day of January');
        
        while ($current <= $end) {
            $years[] = (int)$current->format('Y');
            $current->modify('+1 year');
        }
        
        return $years;
    }

    /**
     * Calculate age from birthdate
     * 
     * @param string $birthdate Birthdate
     * @return int
     */
    public static function calculateAge(string $birthdate): int
    {
        $birth = new DateTime($birthdate);
        $today = new DateTime();
        $diff = $today->diff($birth);
        return $diff->y;
    }

    /**
     * Check if date is in the past
     * 
     * @param string $date Date to check
     * @return bool
     */
    public static function isPast(string $date): bool
    {
        $dt = new DateTime($date);
        $now = new DateTime();
        return $dt < $now;
    }

    /**
     * Check if date is in the future
     * 
     * @param string $date Date to check
     * @return bool
     */
    public static function isFuture(string $date): bool
    {
        $dt = new DateTime($date);
        $now = new DateTime();
        return $dt > $now;
    }

    /**
     * Get difference between dates
     * 
     * @param string $startDate Start date
     * @param string $endDate End date
     * @param string $unit Unit (days, months, years)
     * @return int
     */
    public static function difference(string $startDate, string $endDate, string $unit = 'days'): int
    {
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $diff = $start->diff($end);
        
        switch ($unit) {
            case 'days':
                return (int)$diff->days;
            case 'months':
                return (int)(($diff->y * 12) + $diff->m);
            case 'years':
                return (int)$diff->y;
            default:
                return (int)$diff->days;
        }
    }

    /**
     * Convert timezone
     * 
     * @param string $datetime Datetime string
     * @param string $fromTimezone Original timezone
     * @param string $toTimezone Target timezone
     * @param string $format Output format
     * @return string
     */
    public static function convertTimezone(
        string $datetime,
        string $fromTimezone,
        string $toTimezone,
        string $format = 'Y-m-d H:i:s'
    ): string {
        $dt = new DateTime($datetime, new DateTimeZone($fromTimezone));
        $dt->setTimezone(new DateTimeZone($toTimezone));
        return $dt->format($format);
    }

    /**
     * Get working days between dates
     * 
     * @param string $startDate Start date
     * @param string $endDate End date
     * @param array $holidays Holiday dates
     * @return int
     */
    public static function getWorkingDays(string $startDate, string $endDate, array $holidays = []): int
    {
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $end->modify('+1 day'); // Include end date
        
        $workingDays = 0;
        $current = clone $start;
        
        while ($current < $end) {
            $dayOfWeek = $current->format('N');
            $dateStr = $current->format('Y-m-d');
            
            if ($dayOfWeek < 6 && !in_array($dateStr, $holidays)) {
                $workingDays++;
            }
            
            $current->modify('+1 day');
        }
        
        return $workingDays;
    }
}