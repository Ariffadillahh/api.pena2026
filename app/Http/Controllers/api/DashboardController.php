<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function getStats()
    {
        try {
            $stats = $this->dashboardService->getStatistics();

            return response()->json([
                'message' => 'Sukses mengambil data statistik',
                'data'    => $stats
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data statistik',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
