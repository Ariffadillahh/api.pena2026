<?php

namespace App\Services;

use App\Repositories\CriteriaRepository;

class CriteriaService
{
    protected $criteriaRepo;

    public function __construct(CriteriaRepository $criteriaRepo)
    {
        $this->criteriaRepo = $criteriaRepo;
    }

    public function getCriteria($competitionId)
    {
        return $this->criteriaRepo->getByCompetition($competitionId);
    }

    public function createCriteria($data)
    {
        return $this->criteriaRepo->create($data);
    }

    public function updateCriteria($id, $data)
    {
        return $this->criteriaRepo->update($id, $data);
    }

    public function deleteCriteria($id)
    {
        return $this->criteriaRepo->delete($id);
    }
}
