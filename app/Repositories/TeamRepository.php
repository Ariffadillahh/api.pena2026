<?php

namespace App\Repositories;

use App\Models\Team;
use App\Models\TeamMember;
use App\Models\TeamFile;

class TeamRepository
{
    public function updateOrCreateDraft(array $conditions, array $data)
    {
        return Team::updateOrCreate($conditions, $data);
    }

    public function syncMembers(string $teamId, array $members)
    {
        TeamMember::where('team_id', $teamId)->delete();

        $team = Team::find($teamId);
        return $team->members()->createMany($members);
    }

    public function findFileByType(string $teamId, string $type)
    {
        return TeamFile::where('team_id', $teamId)->where('type', $type)->first();
    }

    public function saveFileInfo(array $data)
    {
        return TeamFile::create($data);
    }
}
