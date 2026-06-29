<?php

namespace App\Repositories;

use App\Models\Team;
use App\Models\Competition;
use App\Models\Staff;

class AdminKaryaRepository
{
    public function getAllCompetitionFolders()
    {
        return Competition::withCount(['teams' => function ($query) {
            $query->where('karya_uploaded', 1);
        }])->get();
    }

    public function getCompetitionFoldersByIds(array $competitionIds)
    {
        return Competition::withCount(['teams' => function ($query) {
            $query->where('karya_uploaded', 1);
        }])->whereIn('id', $competitionIds)->get();
    }

    public function getStaffAssignment($userId)
    {
        return Staff::where('user_id', $userId)->first();
    }

    public function getPaginatedKarya($competitionId, $perPage = 10, $filter = null, $search = null)
    {
        $query = Team::with(['user', 'members', 'team_files'])
            ->where('competition_id', $competitionId);

        if ($filter === 'sudah') {
            $query->where('karya_uploaded', 1);
        } elseif ($filter === 'belum') {
            $query->where('karya_uploaded', 0);
        } else {
            $query->orderBy('karya_uploaded', 'desc')->orderBy('created_at', 'desc');
        }

        $query->when($search, function ($q, $search) {
            return $q->where('name', 'like', '%' . $search . '%');
        });

        return $query->paginate($perPage);
    }
}
