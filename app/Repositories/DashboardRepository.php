<?php

namespace App\Repositories;

use App\Models\Competition;
use App\Models\Team;
use App\Models\Submission;
use App\Models\TeamMember;

class DashboardRepository
{
    public function getTotalTim()
    {
        return Team::count();
    }

    public function getTotalAnggota()
    {
        return TeamMember::count();
    }

    public function getKaryaTerkumpul()
    {
        return Submission::count();
    }

    public function getTeamCountByCompetition()
    {
        return Competition::withCount('teams')->get();
    }
}
