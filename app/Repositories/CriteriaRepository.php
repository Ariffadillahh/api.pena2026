<?php

namespace App\Repositories;

use App\Models\Criteria;

class CriteriaRepository
{
    public function getByCompetition($competitionId)
    {
        return Criteria::where('competition_id', $competitionId)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function create(array $data)
    {
        return Criteria::create($data);
    }

    public function update($id, array $data)
    {
        $criteria = Criteria::findOrFail($id);
        $criteria->update($data);
        return $criteria;
    }

    public function delete($id)
    {
        $criteria = Criteria::findOrFail($id);
        $criteria->delete();
        return true;
    }
}
