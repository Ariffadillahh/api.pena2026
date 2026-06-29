<?php

namespace App\Services;

use App\Repositories\AdminTeamRepository;

class AdminTeamService
{
    protected $repo;

    public function __construct(AdminTeamRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getFolders($user)
    {
        if ($user->role_id == 'rol_1a2b3c' || $user->role_id == 'rol_4d5e6f' || $user->role_id === 'rol_kobh21j') {
            return $this->repo->getAllCompetitionFolders();
        }

        if ($user->role_id == 'rol_7g8h9i') {
            $staff = $this->repo->getStaffAssignment($user->id);

            if ($staff) {
                $competitionIds = [];

                if (!empty($staff->pj_competition_id)) {
                    $competitionIds[] = $staff->pj_competition_id;
                }

                if (!empty($staff->pj_competition_id_2)) {
                    $competitionIds[] = $staff->pj_competition_id_2;
                }

                if (count($competitionIds) > 0) {
                    return $this->repo->getCompetitionFoldersByIds($competitionIds);
                }
            }
        }

        return [];
    }

    public function getTeamsInFolder($user, $competitionId, $perPage = 10, $search = null, $status = null)
    {
        if ($user->role_id === 4) {
            throw new \Exception("Akses Ditolak: Anda tidak memiliki izin untuk melihat tim.");
        }

        $superRoles = ['rol_1a2b3c', 'rol_4d5e6f', 'rol_kobh21j'];  

        if (in_array($user->role_id, $superRoles)) {
            return $this->repo->getTeamsByCompetition($competitionId, $perPage, $search, $status);
        }

        $staff = $this->repo->getStaffAssignment($user->id);

        if (!$staff || ($staff->pj_competition_id !== $competitionId && $staff->pj_competition_id_2 !== $competitionId)) {
            throw new \Exception("Akses Ditolak: Anda bukan PJ untuk lomba ini.");
        }

        return $this->repo->getTeamsByCompetition($competitionId, $perPage, $search, $status);
    }
}
