<?php

namespace App\Services;

use App\Repositories\DashboardRepository;


class DashboardService
{
    protected $dashboardRepo;

    public function __construct(DashboardRepository $dashboardRepo)
    {
        $this->dashboardRepo = $dashboardRepo;
    }

    public function getStatistics()
    {
        $totalTim = $this->dashboardRepo->getTotalTim();
        $totalAnggota = $this->dashboardRepo->getTotalAnggota();
        $totalPeserta = $totalAnggota;
        $karyaTerkumpul = $this->dashboardRepo->getKaryaTerkumpul();

        $competitions = $this->dashboardRepo->getTeamCountByCompetition();

        $categoryBreakdown = [
            'bp_siswa'        => 0,
            'bp_mahasiswa'    => 0,
            'kti_siswa'       => 0,
            'kti_mahasiswa'   => 0,
            'essay_siswa'     => 0,
            'essay_mahasiswa' => 0,
            'info_siswa'      => 0,
            'info_mahasiswa'  => 0,
        ];

        foreach ($competitions as $comp) {
            $title = strtolower($comp->title);
            $kategoriLomba = strtolower($comp->category ?? '');

            $isMahasiswa = \Illuminate\Support\Str::contains($kategoriLomba, 'mahasiswa') || \Illuminate\Support\Str::contains($title, 'mahasiswa');

            if (\Illuminate\Support\Str::contains($title, 'business')) {
                if ($isMahasiswa) $categoryBreakdown['bp_mahasiswa'] += $comp->teams_count;
                else $categoryBreakdown['bp_siswa'] += $comp->teams_count;
            } elseif (\Illuminate\Support\Str::contains($title, ['kti', 'karya tulis'])) {
                if ($isMahasiswa) $categoryBreakdown['kti_mahasiswa'] += $comp->teams_count;
                else $categoryBreakdown['kti_siswa'] += $comp->teams_count;
            } elseif (\Illuminate\Support\Str::contains($title, ['essay', 'esai'])) {
                if ($isMahasiswa) $categoryBreakdown['essay_mahasiswa'] += $comp->teams_count;
                else $categoryBreakdown['essay_siswa'] += $comp->teams_count;
            } elseif (\Illuminate\Support\Str::contains($title, 'info')) {
                if ($isMahasiswa) $categoryBreakdown['info_mahasiswa'] += $comp->teams_count;
                else $categoryBreakdown['info_siswa'] += $comp->teams_count;
            }
        }

        $deadlineString = env('DEADLINE_PENDAFTARAN', '2026-08-22 00:00:00');
        $deadline = \Carbon\Carbon::parse($deadlineString);
        $statusPendaftaran = now()->lessThan($deadline) ? 'AKTIF' : 'DITUTUP';

        $competitionsData = \App\Models\Competition::with(['teams.submission', 'teams.score', 'juris'])->get();

        $gradingProgress = $competitionsData->map(function ($comp) {

            $submittedTeams = $comp->teams->filter(function ($team) {
                return $team->submission !== null;
            });

            $totalKarya = $submittedTeams->count();

            $sudahDinilai = $submittedTeams->filter(function ($team) {
                return $team->score !== null;
            })->count();

            $belumDinilai = $totalKarya - $sudahDinilai;

            $juriNames = $comp->juris->pluck('name')->join(', ');

            return [
                'id'            => $comp->id,
                'title'         => $comp->title,
                'category'      => $comp->category ?? 'Mahasiswa',
                'juri_name'     => !empty($juriNames) ? $juriNames : 'Belum Ditentukan',
                'total_karya'   => $totalKarya,
                'sudah_dinilai' => $sudahDinilai,
                'belum_dinilai' => $belumDinilai,
            ];
        });

        return [
            'total_peserta'      => $totalPeserta,
            'total_tim'          => $totalTim,
            'karya_terkumpul'    => $karyaTerkumpul,
            'status_pendaftaran' => $statusPendaftaran,
            'category_breakdown' => $categoryBreakdown,
            'grading_progress'   => $gradingProgress
        ];
    }
}
