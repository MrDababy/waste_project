<?php
/**
 * Statistics Service
 * 
 * Calculates statistics from approved waste records.
 */

namespace App\Services;

use App\Core\Database;

class StatisticsService
{
    /**
     * Get overview statistics
     */
    public function getOverviewStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_records,
                    SUM(amount) as total_amount,
                    COUNT(DISTINCT location_id) as total_locations,
                    COUNT(DISTINCT collector_id) as total_collectors
                FROM plastic_wastes
                WHERE status = 'approved'";
        
        $result = Database::fetch($sql);
        
        // Current month
        $sql = "SELECT SUM(amount) as month_amount
                FROM plastic_wastes
                WHERE status = 'approved' 
                AND MONTH(collection_date) = MONTH(CURRENT_DATE())
                AND YEAR(collection_date) = YEAR(CURRENT_DATE())";
        
        $monthResult = Database::fetch($sql);
        
        // Current year
        $sql = "SELECT SUM(amount) as year_amount
                FROM plastic_wastes
                WHERE status = 'approved' 
                AND YEAR(collection_date) = YEAR(CURRENT_DATE())";
        
        $yearResult = Database::fetch($sql);
        
        return [
            'total_records' => (int)($result['total_records'] ?? 0),
            'total_amount' => (float)($result['total_amount'] ?? 0),
            'total_amount_formatted' => number_format($result['total_amount'] ?? 0, 2),
            'total_locations' => (int)($result['total_locations'] ?? 0),
            'total_collectors' => (int)($result['total_collectors'] ?? 0),
            'month_amount' => (float)($monthResult['month_amount'] ?? 0),
            'month_amount_formatted' => number_format($monthResult['month_amount'] ?? 0, 2),
            'year_amount' => (float)($yearResult['year_amount'] ?? 0),
            'year_amount_formatted' => number_format($yearResult['year_amount'] ?? 0, 2)
        ];
    }

    /**
     * Get monthly data for charts
     */
    public function getMonthlyData(): array
    {
        $sql = "SELECT 
                    DATE_FORMAT(collection_date, '%Y-%m') as month,
                    SUM(amount) as total
                FROM plastic_wastes
                WHERE status = 'approved'
                AND collection_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 12 MONTH)
                GROUP BY month
                ORDER BY month ASC";
        
        $results = Database::fetchAll($sql);
        
        $labels = [];
        $data = [];
        
        foreach ($results as $row) {
            $labels[] = date('M Y', strtotime($row['month'] . '-01'));
            $data[] = (float)$row['total'];
        }
        
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    /**
     * Get yearly data for charts
     */
    public function getYearlyData(): array
    {
        $sql = "SELECT 
                    YEAR(collection_date) as year,
                    SUM(amount) as total
                FROM plastic_wastes
                WHERE status = 'approved'
                GROUP BY year
                ORDER BY year ASC";
        
        $results = Database::fetchAll($sql);
        
        $labels = [];
        $data = [];
        
        foreach ($results as $row) {
            $labels[] = $row['year'];
            $data[] = (float)$row['total'];
        }
        
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    /**
     * Get plastic type distribution
     */
    public function getPlasticTypeDistribution(): array
    {
        $sql = "SELECT 
                    p.name as plastic_name,
                    p.color_code,
                    SUM(w.amount) as total
                FROM plastic_wastes w
                JOIN plastic_types p ON w.plastic_type_id = p.id
                WHERE w.status = 'approved'
                GROUP BY w.plastic_type_id
                ORDER BY total DESC";
        
        $results = Database::fetchAll($sql);
        
        $labels = [];
        $data = [];
        $colors = [];
        
        foreach ($results as $row) {
            $labels[] = $row['plastic_name'];
            $data[] = (float)$row['total'];
            $colors[] = $row['color_code'] ?? '#cccccc';
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
            'colors' => $colors
        ];
    }

    /**
     * Get location comparison
     */
    public function getLocationComparison(): array
    {
        $sql = "SELECT 
                    l.name as location_name,
                    SUM(w.amount) as total
                FROM plastic_wastes w
                JOIN locations l ON w.location_id = l.id
                WHERE w.status = 'approved'
                GROUP BY w.location_id
                ORDER BY total DESC
                LIMIT 10";
        
        $results = Database::fetchAll($sql);
        
        $labels = [];
        $data = [];
        
        foreach ($results as $row) {
            $labels[] = $row['location_name'];
            $data[] = (float)$row['total'];
        }
        
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    /**
     * Get growth data (month-over-month)
     */
    public function getGrowthData(): array
    {
        $sql = "SELECT 
                    DATE_FORMAT(collection_date, '%Y-%m') as month,
                    SUM(amount) as total
                FROM plastic_wastes
                WHERE status = 'approved'
                AND collection_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
                GROUP BY month
                ORDER BY month ASC";
        
        $results = Database::fetchAll($sql);
        
        $labels = [];
        $data = [];
        $growth = [];
        
        $previous = null;
        
        foreach ($results as $index => $row) {
            $labels[] = date('M Y', strtotime($row['month'] . '-01'));
            $current = (float)$row['total'];
            $data[] = $current;
            
            if ($previous !== null && $previous > 0) {
                $percentage = (($current - $previous) / $previous) * 100;
                $growth[] = round($percentage, 1);
            } else {
                $growth[] = 0;
            }
            
            $previous = $current;
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
            'growth' => $growth
        ];
    }

    /**
     * Get top locations
     */
    public function getTopLocations(int $limit = 5): array
    {
        $sql = "SELECT 
                    l.id,
                    l.name as location_name,
                    s.name as school_name,
                    SUM(w.amount) as total_collected,
                    COUNT(w.id) as collection_count
                FROM plastic_wastes w
                JOIN locations l ON w.location_id = l.id
                JOIN schools s ON l.school_id = s.id
                WHERE w.status = 'approved'
                GROUP BY w.location_id
                ORDER BY total_collected DESC
                LIMIT ?";
        
        return Database::fetchAll($sql, [$limit]);
    }

    /**
     * Get monthly report data
     */
    public function getMonthlyReport(int $year, int $month): array
    {
        $sql = "SELECT 
                    w.*,
                    u.first_name, u.last_name,
                    l.name as location_name,
                    s.name as school_name,
                    p.name as plastic_name,
                    p.code as plastic_code
                FROM plastic_wastes w
                JOIN users u ON w.collector_id = u.id
                JOIN locations l ON w.location_id = l.id
                JOIN schools s ON l.school_id = s.id
                JOIN plastic_types p ON w.plastic_type_id = p.id
                WHERE w.status = 'approved'
                AND YEAR(w.collection_date) = ?
                AND MONTH(w.collection_date) = ?
                ORDER BY w.collection_date DESC";
        
        $records = Database::fetchAll($sql, [$year, $month]);
        
        // Summary
        $summary = [
            'total_amount' => 0,
            'total_records' => count($records),
            'by_location' => [],
            'by_plastic_type' => []
        ];
        
        foreach ($records as $record) {
            $summary['total_amount'] += (float)$record['amount'];
            
            $location = $record['location_name'];
            if (!isset($summary['by_location'][$location])) {
                $summary['by_location'][$location] = 0;
            }
            $summary['by_location'][$location] += (float)$record['amount'];
            
            $plastic = $record['plastic_name'];
            if (!isset($summary['by_plastic_type'][$plastic])) {
                $summary['by_plastic_type'][$plastic] = 0;
            }
            $summary['by_plastic_type'][$plastic] += (float)$record['amount'];
        }
        
        return [
            'records' => $records,
            'summary' => $summary
        ];
    }

    /**
     * Get yearly report data
     */
    public function getYearlyReport(int $year): array
    {
        $sql = "SELECT 
                    MONTH(w.collection_date) as month,
                    SUM(w.amount) as total
                FROM plastic_wastes w
                WHERE w.status = 'approved'
                AND YEAR(w.collection_date) = ?
                GROUP BY MONTH(w.collection_date)
                ORDER BY month ASC";
        
        $monthlyData = Database::fetchAll($sql, [$year]);
        
        $monthlyTotals = array_fill(1, 12, 0);
        foreach ($monthlyData as $row) {
            $monthlyTotals[(int)$row['month']] = (float)$row['total'];
        }
        
        return [
            'year' => $year,
            'monthly_totals' => $monthlyTotals,
            'total' => array_sum($monthlyTotals)
        ];
    }
}