<?php

namespace App\Services;

use App\Repositories\AdminKaryaRepository;
use Exception;

class AdminKaryaService
{
    protected $repo;

    public function __construct(AdminKaryaRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getFolders($user)
    {
        if ($user->role_id === 'rol_1a2b3c' || $user->role_id === 'rol_4d5e6f' || $user->role_id === 'rol_kobh21j') {
            return $this->repo->getAllCompetitionFolders();
        }

        if ($user->role_id === 'rol_7g8h9i') {
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

    public function getKaryaInFolder($user, $competitionId, $perPage, $filter, $search)
    {
        if ($user->role_id === 'rol_7g8h9i') {
            $staff = $this->repo->getStaffAssignment($user->id);
            if (!$staff || $staff->pj_competition_id != $competitionId) {
                throw new Exception("Akses Ditolak: Anda bukan Penanggung Jawab untuk lomba ini.", 403);
            }
        }

        return $this->repo->getPaginatedKarya($competitionId, $perPage, $filter, $search);
    }
}
