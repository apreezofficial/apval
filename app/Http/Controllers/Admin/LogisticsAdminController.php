<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Services\Logistics\LogisticsReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller; // ✅ Use Laravel's core controller directly

class LogisticsAdminController extends Controller
{
    public function __construct(
        private LogisticsReportService $reportService
    ) {}

    /**
     * GET /api/admin/logistics/dashboard
     * Returns all metrics for the Admin & Monitoring Dashboard (SRS Section 8)
     */
    public function dashboard(): JsonResponse
    {
        $metrics = $this->reportService->getDashboardMetrics();

        return response()->json([
            'status' => 'success',
            'data'   => $metrics,
        ]);
    }
}