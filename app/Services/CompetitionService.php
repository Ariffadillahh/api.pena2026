<?php

namespace App\Services;

use App\Models\Competition;
use App\Repositories\CompetitionRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CompetitionService
{
    protected $competitionRepository;

    public function __construct(CompetitionRepository $competitionRepository)
    {
        $this->competitionRepository = $competitionRepository;
    }

    public function getActiveCompetitions()
    {
        $competitions = $this->competitionRepository->getAllActive();

        return $competitions;
    }

    public function getCompetitionDetail(string $slug)
    {
        $competition = $this->competitionRepository->findBySlug($slug);

        if (!$competition) {
            throw new Exception('Kompetisi tidak ditemukan.');
        }

        return $competition;
    }

    public function getAllCompetitions()
    {
        $competitions = $this->competitionRepository->getAll();

        return $competitions;
    }

    public function storeCompetition(array $data)
    {
        DB::beginTransaction();
        try {
            $data['slug'] = Str::slug($data['title'] . '-' . $data['category']);

            $competition = $this->competitionRepository->create($data);

            if (!empty($data['requirements'])) {
                $competition->requirements()->createMany($data['requirements']);
            }

            if (!empty($data['waves'])) {
                $competition->waves()->createMany($data['waves']);
            }

            DB::commit();
            return $this->competitionRepository->findById($competition->id);
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('Gagal menyimpan lomba: ' . $e->getMessage());
        }
    }

    public function updateCompetition(string $id, array $data)
    {
        $competition = $this->competitionRepository->findById($id);
        if (!$competition) {
            throw new Exception('Lomba tidak ditemukan.');
        }

        DB::beginTransaction();
        try {
            if (isset($data['title']) && $data['title'] !== $competition->title) {
                $baseSlug = Str::slug($data['title']);
                $slug = $baseSlug;
                $counter = 1;

                while (Competition::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }

                $data['slug'] = $slug;
            } else {
                unset($data['slug']);
            }

            $this->competitionRepository->update($id, $data);

            if (isset($data['requirements'])) {
                $competition->requirements()->delete();
                $competition->requirements()->createMany($data['requirements']);
            }

            if (isset($data['waves'])) {
                $competition->waves()->delete();
                $competition->waves()->createMany($data['waves']);
            }

            DB::commit();
            return $this->competitionRepository->findById($id);
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('Gagal memperbarui lomba: ' . $e->getMessage());
        }
    }

    public function deleteCompetition(string $id)
    {
        $deleted = $this->competitionRepository->delete($id);

        if (!$deleted) {
            throw new Exception('Lomba tidak ditemukan atau gagal dihapus.');
        }

        return true;
    }
}
