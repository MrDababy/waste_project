<?php
/**
 * Public Controller
 * 
 * Handles public-facing pages including homepage, dashboard, and map.
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Services\StatisticsService;
use App\Repositories\LocationRepository;
use App\Repositories\WasteRepository;

class PublicController extends Controller
{
    /**
     * @var StatisticsService
     */
    private StatisticsService $statisticsService;

    /**
     * @var LocationRepository
     */
    private LocationRepository $locationRepository;

    /**
     * @var WasteRepository
     */
    private WasteRepository $wasteRepository;

    public function __construct()
    {
        parent::__construct();
        $this->statisticsService = new StatisticsService();
        $this->locationRepository = new LocationRepository();
        $this->wasteRepository = new WasteRepository();
        
        // Set public layout
        $this->setLayout('public');
    }

    /**
     * Homepage
     */
    public function index(): string
    {
        // Get basic statistics for homepage
        $stats = $this->statisticsService->getOverviewStats();
        
        // Get recent collections for the activity feed
        $recentCollections = $this->wasteRepository->getRecentApproved(5);
        
        // Get top locations
        $topLocations = $this->statisticsService->getTopLocations(3);
        
        return $this->render('public/home', [
            'stats' => $stats,
            'recentCollections' => $recentCollections,
            'topLocations' => $topLocations,
            'pageTitle' => 'Track Plastic. Measure Impact. Build a Cleaner Future.'
        ]);
    }

    /**
     * Public Dashboard
     */
    public function dashboard(): string
    {
        // Get all statistics
        $stats = $this->statisticsService->getOverviewStats();
        $monthlyData = $this->statisticsService->getMonthlyData();
        $yearlyData = $this->statisticsService->getYearlyData();
        $plasticTypeDistribution = $this->statisticsService->getPlasticTypeDistribution();
        $locationComparison = $this->statisticsService->getLocationComparison();
        $growthData = $this->statisticsService->getGrowthData();
        
        return $this->render('public/dashboard', [
            'stats' => $stats,
            'monthlyData' => $monthlyData,
            'yearlyData' => $yearlyData,
            'plasticTypeDistribution' => $plasticTypeDistribution,
            'locationComparison' => $locationComparison,
            'growthData' => $growthData,
            'pageTitle' => 'Plastic Waste Analytics Dashboard'
        ]);
    }

    /**
     * Interactive Map
     */
    public function map(): string
    {
        $locations = $this->locationRepository->getAllWithStats();
        
        return $this->render('public/map', [
            'locations' => $locations,
            'pageTitle' => 'Plastic Collection Map'
        ]);
    }
}