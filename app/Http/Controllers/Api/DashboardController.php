<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Get comprehensive dashboard statistics (Admin only)
     * 
     * GET /api/dashboard/statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $statistics = $this->dashboardService->getStatistics();
        return $this->success($statistics, 'Dashboard statistics retrieved successfully');
    }

    /**
     * Get librarian-specific dashboard statistics
     * 
     * GET /api/dashboard/librarian-statistics
     */
    public function librarianStatistics(Request $request): JsonResponse
    {
        $statistics = $this->dashboardService->getLibrarianStatistics();
        return $this->success($statistics, 'Librarian dashboard statistics retrieved successfully');
    }

    /**
     * Get overview statistics
     * 
     * GET /api/dashboard/overview
     */
    public function overview(Request $request): JsonResponse
    {

        $user = $request->user();

        // Return different overview based on user role
        if ($user->isAdmin()) {
            $statistics = $this->dashboardService->getStatistics();
            $overview = $statistics['overview'];
        } else {
            $statistics = $this->dashboardService->getLibrarianStatistics();
            $overview = $statistics['overview'];
        }

        return $this->success($overview, 'Dashboard overview retrieved successfully');
    }
}
