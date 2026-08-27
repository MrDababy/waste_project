<?php
/**
 * API Controller
 * 
 * Handles AJAX/API requests for charts and real-time data.
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Services\StatisticsService;
use App\Repositories\LocationRepository;

class ApiController extends Controller
{
    /**
     * @var StatisticsService
     */
    private StatisticsService $statisticsService;

    /**
     * @var LocationRepository
     */
    private LocationRepository $locationRepository;

    public function __construct()
    {
        parent::__construct();
        $this->statisticsService = new StatisticsService();
        $this->locationRepository = new LocationRepository();
    }

    /**
     * Get overview statistics
     */
    public function statistics(): void
    {
        $stats = $this->statisticsService->getOverviewStats();
        $this->jsonSuccess($stats);
    }

    /**
     * Get chart data
     */
    public function chartData(): void
    {
        $type = $this->getParam('type', 'monthly');
        $period = $this->getParam('period', 'current_year');
        
        $data = match($type) {
            'monthly' => $this->statisticsService->getMonthlyData(),
            'yearly' => $this->statisticsService->getYearlyData(),
            'distribution' => $this->statisticsService->getPlasticTypeDistribution(),
            'locations' => $this->statisticsService->getLocationComparison(),
            'growth' => $this->statisticsService->getGrowthData(),
            default => []
        };

        $this->jsonSuccess($data);
    }

    /**
     * Get locations for map
     */
    public function locations(): void
    {
        $locations = $this->locationRepository->getAllWithStats();
        $this->jsonSuccess($locations);
    }
}