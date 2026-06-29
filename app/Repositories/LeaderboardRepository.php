<?php

namespace App\Repositories;

use App\Models\Team;

class LeaderboardRepository
{
    public function getTeamsWithScores($competitionId, $search = null)
    {
        $query = Team::where('competition_id', $competitionId)
            ->whereHas('scores')
            ->with(['scores.criteria', 'scores.user']);

        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        return $query->get();
    }

    public function updateTeamsStatus(array $teamIds, $status)
    {
        return Team::whereIn('id', $teamIds)->update(['status' => $status]);
    }
}
