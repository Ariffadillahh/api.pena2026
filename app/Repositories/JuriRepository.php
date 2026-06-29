<?php

namespace App\Repositories;

use App\Models\Competition;
use App\Models\Criteria;
use App\Models\User;
use App\Models\JuriAssignment;
use App\Models\Score;
use App\Models\Team;

class JuriRepository
{
    public function getAllJuris($perPage = 10)
    {
        return User::with('juriAssignments.competition')
            ->where('role_id', 'rol_jms02ks6')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function createJuri(array $userData)
    {
        return User::create($userData);
    }

    public function assignToCompetition($userId, $competitionId)
    {
        return JuriAssignment::create([
            'user_id' => $userId,
            'competition_id' => $competitionId
        ]);
    }

    public function findJuriById($id)
    {
        return User::where('id', $id)->where('role_id', 'rol_jms02ks6')->first();
    }

    public function deleteJuri($id)
    {
        $juri = $this->findJuriById($id);
        if ($juri) {
            $juri->delete();
            return true;
        }
        return false;
    }

    public function getAssignedCompetition($userId)
    {
        $assignment = JuriAssignment::where('user_id', $userId)->first();

        // \Illuminate\Support\Facades\Log::info('--- DEBUG JURI ---', [
        //     'ID_User_Yg_Login' => $userId,
        //     'Data_Assignment' => $assignment ? $assignment->toArray() : 'KOSONG/TIDAK ADA'
        // ]);

        if (!$assignment || !$assignment->competition_id) {
            return null;
        }

        $competition = Competition::find($assignment->competition_id);

        // \Illuminate\Support\Facades\Log::info('--- DEBUG LOMBA ---', [
        //     'ID_Lomba_Dicari' => $assignment->competition_id,
        //     'Data_Lomba' => $competition ? $competition->toArray() : 'LOMBA TIDAK DITEMUKAN'
        // ]);

        return $competition;
    }

    public function getStats($competitionId, $juriId)
    {
        $totalKarya = Team::where('competition_id', $competitionId)
            ->where('karya_uploaded', true)
            ->count();

        $sudahDinilai = Team::where('competition_id', $competitionId)
            ->where('karya_uploaded', true)
            ->whereHas('scores', function ($query) use ($juriId) {
                $query->where('juri_id', $juriId);
            })->count();
        return [
            'totalKarya'   => $totalKarya,
            'sudahDinilai' => $sudahDinilai,
            'belumDinilai' => $totalKarya - $sudahDinilai,
        ];
    }

    public function getCriteria($competitionId)
    {
        return Criteria::where('competition_id', $competitionId)
            ->select('id', 'name', 'weight', 'description as desc')
            ->get();
    }

    public function getTeamsToGrade($competitionId, $juriId, $search = null, $perPage = 10)
    {
        return Team::query()
            ->where('competition_id', $competitionId)
            ->where('karya_uploaded', true)
            ->whereDoesntHave('scores', function ($q) use ($juriId) {
                $q->where('juri_id', $juriId);
            })
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', '%' . $search . '%');
            })
            ->paginate($perPage);
    }

    public function getTeamWithSubmission($teamId)
    {
        return Team::with('submission')->findOrFail($teamId);
    }

    public function getExistingScores($teamId, $juriId)
    {
        return Score::where('team_id', $teamId)
            ->where('juri_id', $juriId)
            ->get();
    }

    public function saveScore($teamId, $juriId, $criteriaId, $scoreValue)
    {
        return Score::updateOrCreate(
            [
                'team_id' => $teamId,
                'juri_id' => $juriId,
                'criteria_id' => $criteriaId,
            ],
            [
                'score' => $scoreValue,
            ]
        );
    }

    public function getGradedTeamsStandings($competitionId, $juriId, $search = null, $perPage = 10)
    {
        return Team::query()
            ->where('competition_id', $competitionId)
            ->whereHas('scores', function ($q) use ($juriId) {
                $q->where('juri_id', $juriId);
            })
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', '%' . $search . '%');
            })
            ->orderByDesc('total_score')
            ->paginate($perPage);
    }
}
