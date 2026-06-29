<?php

namespace App\Services;

use App\Repositories\AdminAnnouncementRepository;

class AdminAnnouncementService
{
    protected $repo;

    public function __construct(AdminAnnouncementRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getList($perPage)
    {
        return $this->repo->getAllPaginated($perPage);
    }

    public function createAnnouncement($data)
    {
        return $this->repo->create($data);
    }

    public function updateAnnouncement($id, $data)
    {
        return $this->repo->update($id, $data);
    }

    public function deleteAnnouncement($id)
    {
        return $this->repo->delete($id);
    }
}
