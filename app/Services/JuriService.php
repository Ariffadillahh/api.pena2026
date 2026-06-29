<?php

namespace App\Services;

use App\Repositories\JuriRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\JuriAccountCreated;
use App\Models\Criteria;
use App\Models\JuriAssignment;
use App\Models\Team;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class JuriService
{
    protected $juriRepo;

    public function __construct(JuriRepository $juriRepo)
    {
        $this->juriRepo = $juriRepo;
    }

    public function getPaginatedJuris($perPage)
    {
        return $this->juriRepo->getAllJuris($perPage);
    }

    public function createNewJuri(array $data)
    {
        DB::beginTransaction();
        try {

            $rawPassword = Str::random(8);

            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($rawPassword),
                'role_id' => 'rol_jms02ks6',
                'email_verified_at' => now(),
            ];

            $juri = $this->juriRepo->createJuri($userData);

            $this->juriRepo->assignToCompetition($juri->id, $data['competition_id']);

            $loginUrl = env('FRONTEND_URL') . '/auth/login';
            Mail::to($juri->email)->send(new JuriAccountCreated($juri, $rawPassword, $loginUrl));

            DB::commit();
            return $juri;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Gagal membuat akun Juri: " . $e->getMessage());
        }
    }

    public function updateJuri($id, array $data)
    {
        DB::beginTransaction();
        try {
            $juri = $this->juriRepo->findJuriById($id);
            if (!$juri) throw new Exception("Juri tidak ditemukan.");

            $juri->name = $data['name'];

            if ($juri->email !== $data['email']) {
                $juri->email = $data['email'];

                $rawPassword = \Illuminate\Support\Str::random(8);
                $juri->password = Hash::make($rawPassword);

                $loginUrl = env('FRONTEND_URL') . '/auth/login';
                Mail::to($juri->email)->send(new JuriAccountCreated($juri, $rawPassword, $loginUrl));
            }

            $juri->save();
            DB::commit();
            return $juri;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Gagal mengupdate Juri: " . $e->getMessage());
        }
    }

    public function deleteJuri($id)
    {
        $deleted = $this->juriRepo->deleteJuri($id);
        if (!$deleted) {
            throw new Exception("Juri tidak ditemukan.");
        }
        return true;
    }

    public function getDashboardData($userId)
    {
        $competition = $this->juriRepo->getAssignedCompetition($userId);

        if (!$competition) {
            throw new Exception("Anda belum ditugaskan sebagai juri di lomba manapun.");
        }

        $stats = $this->juriRepo->getStats($competition->id, $userId);
        $criteria = $this->juriRepo->getCriteria($competition->id);

        $assignment = JuriAssignment::where('user_id', Auth::user()->id)->first();

        return [
            'competitionName' => $competition->title,
            'competitionCategory' => $competition->category,
            'stats'           => $stats,
            'criteria'        => $criteria,
            'assignment_id' => $assignment->id,
            'hasSignature' => $assignment->signature ? true : false,
            'signature'       => $assignment->signature,
        ];
    }

    public function getDaftarTim($userId, $search = null, $perPage = 10)
    {
        $competition = $this->juriRepo->getAssignedCompetition($userId);
        if (!$competition) throw new Exception("Anda belum ditugaskan di lomba manapun.");

        $teamsPaginated = $this->juriRepo->getTeamsToGrade($competition->id, $userId, $search, $perPage);

        $items = $teamsPaginated->getCollection()->map(function ($team) {
            return [
                'id' => $team->id,
                'name' => $team->name,
                'judul' => $team->submission->original_filename ?? 'Karya Tim ' . $team->name,
                'status' => 'BELUM',
                'file_url' => $team->submission->file_path,
            ];
        });

        return [
            'data' => $items,
            'meta' => [
                'current_page' => $teamsPaginated->currentPage(),
                'last_page' => $teamsPaginated->lastPage(),
                'total' => $teamsPaginated->total(),
                'per_page' => $teamsPaginated->perPage(),
            ]
        ];
    }

    public function getFormPenilaian($teamId, $juriId)
    {
        $team = $this->juriRepo->getTeamWithSubmission($teamId);
        $criteria = $this->juriRepo->getCriteria($team->competition_id);
        $existingScores = $this->juriRepo->getExistingScores($teamId, $juriId);

        return [
            'team' => [
                'id'       => $team->id,
                'name'     => $team->name,
                'category' => $team->competition->category,
                'judul'    => $team->submission->original_filename ?? 'Karya Tim ' . $team->name,
                'score_board' => $team->score_board ?? null,
                'file_url' => $team->submission->file_path,
                'gdrive_link' => $team->submission->gdrive_link ?? null,
                'notes' => $team->notes,
            ],
            'criteria' => $criteria->map(function ($c) use ($existingScores) {
                $score = $existingScores->firstWhere('criteria_id', $c->id);
                return [
                    'id'          => $c->id,
                    'name'        => $c->name,
                    'weight'      => $c->weight,
                    'description' => $c->desc,
                    'score'       => $score ? $score->score : 0
                ];
            })
        ];
    }

    public function submitPenilaian($teamId, $juriId, $scoresData, $notes)
    {
        $totalScore = 0;

        foreach ($scoresData as $data) {
            $this->juriRepo->saveScore($teamId, $juriId, $data['criteria_id'], $data['score']);

            $criteria = Criteria::find($data['criteria_id']);
            $bobot = $criteria->weight ?? 0;
            $totalScore += ($data['score'] * $bobot) / 100;
        }

        $team = Team::with(['competition', 'submission'])->find($teamId);

        if ($team) {
            $team->update([
                'total_score' => $totalScore,
                'notes'       => $notes,
                'status'      => 'dinilai'
            ]);

            $pdfUrl = $this->generateSingleScoreboard($team);

            return $pdfUrl;
        }

        throw new \Exception("Data Tim tidak ditemukan.");
    }

    private function generateSingleScoreboard($team)
    {
        $team = Team::with(['competition', 'submission', 'scores.criteria', 'members'])->find($team->id);

        $juri = Auth::user();
        $assignment = JuriAssignment::where('user_id', $juri->id)
            ->where('competition_id', $team->competition_id)->first();
        $juriSignature = $assignment ? $assignment->signature : null;

        $folderPath = 'registrations/' . $team->id . '/score_board';
        $newFileName = 'scoreboard_' . time() . '.pdf';
        $newPath = $folderPath . '/' . $newFileName;

        $fullPublicUrl = asset('storage/' . $newPath);

        if ($team->score_board) {
            if (Storage::disk('public')->exists($team->score_board)) {
                Storage::disk('public')->delete($team->score_board);
            }
        }

        $pdf = Pdf::loadView('pdf.scoreboard', [
            'teams' => [$team],
            'competition' => $team->competition,
            'current_qr_url' => $fullPublicUrl,
            'current_juri_name' => $juri->name ?? 'Dewan Juri',
            'current_juri_signature' => $juriSignature
        ])->setPaper('a4', 'portrait');

        Storage::disk('public')->put($newPath, $pdf->output());

        $team->update(['score_board' => $newPath]);

        return $fullPublicUrl;
    }

    public function getStandings($userId, $search = null, $perPage = 10)
    {
        $competition = $this->juriRepo->getAssignedCompetition($userId);
        if (!$competition) {
            throw new Exception("Anda belum ditugaskan di lomba manapun.");
        }

        $teamsPaginated = $this->juriRepo->getGradedTeamsStandings($competition->id, $userId, $search, $perPage);

        $juriName = Auth::user()->name;

        $items = $teamsPaginated->getCollection()->map(function ($team) use ($juriName) {
            return [
                'id'          => $team->id,
                'name'        => $team->name,
                'juri_names'  => $juriName,
                'final_score' => $team->total_score ?? 0,
            ];
        });

        return [
            'data' => $items,
            'meta' => [
                'current_page' => $teamsPaginated->currentPage(),
                'last_page'    => $teamsPaginated->lastPage(),
                'total'        => $teamsPaginated->total(),
                'per_page'     => $teamsPaginated->perPage(),
            ]
        ];
    }
}
