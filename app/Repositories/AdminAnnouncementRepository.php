<?php

namespace App\Repositories;

use App\Models\Announcement;

class AdminAnnouncementRepository
{
    public function getAllPaginated($perPage = 10)
    {
        return Announcement::with([
            'author:id,name',
            'competition:id,title',
            'team:id,name'
        ])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findById($id)
    {
        return Announcement::findOrFail($id);
    }

    public function create(array $data)
    {
        return Announcement::create($data);
    }

    public function update($id, array $data)
    {
        $announcement = $this->findById($id);
        $announcement->update($data);
        return $announcement;
    }

    public function delete($id)
    {
        $announcement = $this->findById($id);
        return $announcement->delete();
    }
}
