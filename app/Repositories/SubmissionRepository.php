<?php

namespace App\Repositories;

use App\Models\Submission;
use App\Models\Team;

class SubmissionRepository 
{
    public function findByTeamId(string $teamId)
    {
        return Submission::where('team_id', $teamId)->first();
    }

    public function createOrUpdate(string $teamId, array $data)
    {
        $submission = Submission::updateOrCreate(
            ['team_id' => $teamId],
            $data
        );

        Team::where('id', $teamId)->update(['karya_uploaded' => true]);

        return $submission;
    }
}
