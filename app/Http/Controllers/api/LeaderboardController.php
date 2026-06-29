<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LeaderboardService;
use Illuminate\Http\Request;
use Exception;

class LeaderboardController extends Controller
{
    protected $leaderboardService;

    public function __construct(LeaderboardService $leaderboardService)
    {
        $this->leaderboardService = $leaderboardService;
    }

    public function index(Request $request, $competitionId)
    {
        try {
            $search = $request->query('search');
            $page = (int) $request->query('page', 1);
            $perPage = (int) $request->query('per_page', 10);

            $result = $this->leaderboardService->getLeaderboard($competitionId, $search, $page, $perPage);

            return response()->json([
                'status' => 'success',
                'data' => $result['data'],
                'meta' => $result['meta']
            ], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    public function finalize($competitionId)
    {
        try {
            $this->leaderboardService->finalizeTopTeams($competitionId);
            return response()->json([
                'status' => 'success',
                'message' => 'Top 5 tim teratas berhasil diubah statusnya menjadi lolos_top_10!'
            ], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }
}
