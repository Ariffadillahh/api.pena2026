<?php

namespace App\Services;

use App\Models\Team;
use App\Repositories\LeaderboardRepository;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;

class LeaderboardService
{
    protected $leaderboardRepo;

    public function __construct(LeaderboardRepository $leaderboardRepo)
    {
        $this->leaderboardRepo = $leaderboardRepo;
    }

    public function getLeaderboard($competitionId, $search = null, $page = 1, $perPage = 10)
    {
        $totalTeams = Team::where('competition_id', $competitionId)->where('karya_uploaded', true)->count();

        $unscoredTeams = Team::where('competition_id', $competitionId)
            ->where('karya_uploaded', true)
            ->whereDoesntHave('scores')
            ->count();

        $isAllScored = ($totalTeams > 0) && ($unscoredTeams === 0);

        $teams = $this->leaderboardRepo->getTeamsWithScores($competitionId, $search);

        $leaderboard = $teams->map(function ($team) {
            $totalScores = $team->scores->groupBy('juri_id')->map(function ($juriScores) {
                return $juriScores->sum(function ($s) {
                    return ($s->score * ($s->criteria->weight ?? 0)) / 100;
                });
            });

            $finalScore = $totalScores->avg();
            $juriNames = $team->scores->pluck('user.name')->unique()->implode(' | ');

            return [
                'id'          => $team->id,
                'name'        => $team->name,
                'status'      => $team->status,
                'final_score' => round($finalScore, 2),
                'juri_names'  => $juriNames
            ];
        });

        $sortedLeaderboard = $leaderboard->sortByDesc('final_score')->values();

        $paginated = new LengthAwarePaginator(
            $sortedLeaderboard->forPage($page, $perPage)->values(),
            $sortedLeaderboard->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return [
            'data' => $paginated->items(),
            'meta' => [
                'current_page'  => $paginated->currentPage(),
                'last_page'     => $paginated->lastPage(),
                'per_page'      => $paginated->perPage(),
                'total'         => $paginated->total(),
                'is_all_scored' => $isAllScored 
            ]
        ];
    }

    public function finalizeTopTeams($competitionId)
    {
        $leaderboard = $this->getLeaderboard($competitionId);

        $teams = $leaderboard['data'] ?? [];

        if (empty($teams)) {
            throw new Exception("Tidak ada tim yang dinilai untuk lomba ini.");
        }

        $top5TeamIds = collect($teams)->take(5)->pluck('id')->toArray();

        $this->leaderboardRepo->updateTeamsStatus($top5TeamIds, 'lolos_top_10');

        return true;
    }
}
