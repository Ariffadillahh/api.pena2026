<?php

namespace App\Repositories;

use App\Models\Team;
use App\Models\Competition;
use App\Models\Staff;

class AdminTeamRepository
{
    public function getAllCompetitionFolders()
    {
        return Competition::withCount(['teams' => function ($query) {
            $query->where('status', '!=', 'draft');
        }])->get();
    }

    public function getCompetitionFoldersByIds($competitionIds)
    {
        return Competition::whereIn('id', $competitionIds)
            ->withCount(['teams' => function ($query) {
                $query->where('status', '!=', 'draft');
            }])->get();
    }

    public function getStaffAssignment($userId)
    {
        return Staff::where('user_id', $userId)->first();
    }

    public function getTeamsByCompetition($competitionId, $perPage = 10, $search = null, $status = null)
    {
        return Team::select(
            'teams.*',
            'registration_waves.wave_name as wave_name',
            'registration_waves.price'
        )
            ->leftJoin('registration_waves', 'teams.wave_id', '=', 'registration_waves.id')
            ->with([
                'user',
                'members',
                'team_files',
                'updater:id,name'
            ])
            ->where('teams.competition_id', $competitionId)
            ->where('teams.status', '!=', 'draft')
            ->when($status, function ($query, $status) {
                return $query->where('teams.status', $status);
            })
            ->when($search, function ($query, $search) {
                return $query->where('teams.name', 'like', '%' . $search . '%');
            })
            ->paginate($perPage);
    }
}
