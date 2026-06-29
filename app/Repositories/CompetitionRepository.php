<?php

namespace App\Repositories;

use App\Models\Competition;

class CompetitionRepository
{
    public function getAllActive()
    {
        return Competition::with(['requirements', 'waves'])
            ->where('is_active', true)
            ->get();
    }

    public function getAll()
    {
        return Competition::with(['requirements', 'waves'])->get();
    }

    public function findBySlug(string $slug)
    {
        return Competition::with(['requirements', 'waves'])
            ->where('slug', $slug)
            ->first();
    }

    public function findById(string $id)
    {
        return Competition::with(['requirements', 'waves'])->find($id);
    }

    public function create(array $data)
    {
        return Competition::create($data);
    }

    public function update(string $id, array $data)
    {
        $competition = Competition::find($id);
        if ($competition) {
            $competition->update($data);
            return $competition;
        }
        return null;
    }

    public function delete(string $id)
    {
        $competition = Competition::find($id);
        if ($competition) {
            return $competition->delete();
        }
        return false;
    }
}
