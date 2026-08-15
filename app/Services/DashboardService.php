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

        $menungguBreakdown = [
            'bp_siswa'        => 0,
            'bp_mahasiswa'    => 0,
            'kti_siswa'       => 0,
            'kti_mahasiswa'   => 0,
            'essay_siswa'     => 0,
            'essay_mahasiswa' => 0,
            'info_siswa'      => 0,
            'info_mahasiswa'  => 0,
        ];

        $competitionsData = \App\Models\Competition::with(['teams.submission', 'teams.score', 'juris'])->get();

        foreach ($competitionsData as $comp) {
            $title = strtolower($comp->title);
            $kategoriLomba = strtolower($comp->category ?? '');
            $isMahasiswa = \Illuminate\Support\Str::contains($kategoriLomba, 'mahasiswa') || \Illuminate\Support\Str::contains($title, 'mahasiswa');

            $timValidCount = $comp->teams->filter(function ($t) {
                return $t->status === 'menunggu_penilaian' && $t->payment_status === 'valid';
            })->count();

            $timMenungguCount = $comp->teams->filter(function ($t) {
                return $t->status === 'menunggu_konfirmasi';
            })->count();

            if (\Illuminate\Support\Str::contains($title, ['business', 'bussines', 'bisnis', 'bp'])) {
                if ($isMahasiswa) {
                    $categoryBreakdown['bp_mahasiswa'] += $timValidCount;
                    $menungguBreakdown['bp_mahasiswa'] += $timMenungguCount;
                } else {
                    $categoryBreakdown['bp_siswa'] += $timValidCount;
                    $menungguBreakdown['bp_siswa'] += $timMenungguCount;
                }
            } elseif (\Illuminate\Support\Str::contains($title, ['kti', 'karya tulis'])) {
                if ($isMahasiswa) {
                    $categoryBreakdown['kti_mahasiswa'] += $timValidCount;
                    $menungguBreakdown['kti_mahasiswa'] += $timMenungguCount;
                } else {
                    $categoryBreakdown['kti_siswa'] += $timValidCount;
                    $menungguBreakdown['kti_siswa'] += $timMenungguCount;
                }
            } elseif (\Illuminate\Support\Str::contains($title, ['essay', 'esai'])) {
                if ($isMahasiswa) {
                    $categoryBreakdown['essay_mahasiswa'] += $timValidCount;
                    $menungguBreakdown['essay_mahasiswa'] += $timMenungguCount;
                } else {
                    $categoryBreakdown['essay_siswa'] += $timValidCount;
                    $menungguBreakdown['essay_siswa'] += $timMenungguCount;
                }
            } elseif (\Illuminate\Support\Str::contains($title, 'info')) {
                if ($isMahasiswa) {
                    $categoryBreakdown['info_mahasiswa'] += $timValidCount;
                    $menungguBreakdown['info_mahasiswa'] += $timMenungguCount;
                } else {
                    $categoryBreakdown['info_siswa'] += $timValidCount;
                    $menungguBreakdown['info_siswa'] += $timMenungguCount;
                }
            }
        }

        $deadlineString = '2026-08-17 00:00:00';
        $deadline = \Carbon\Carbon::parse($deadlineString);
        $statusPendaftaran = now()->lessThan($deadline) ? 'AKTIF' : 'DITUTUP';

        $gradingProgress = $competitionsData->map(function ($comp) {
            $timWajibSubmit = $comp->teams->filter(function ($team) {
                return $team->status === 'menunggu_penilaian' && $team->payment_status === 'valid';
            });

            $totalTimLomba = $timWajibSubmit->count();

            $belumSubmit = $timWajibSubmit->filter(function ($team) {
                return $team->submission === null;
            })->count();

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
                'id'              => $comp->id,
                'title'           => $comp->title,
                'category'        => $comp->category ?? 'Mahasiswa',
                'juri_name'       => !empty($juriNames) ? $juriNames : 'Belum Ditentukan',
                'total_karya'     => $totalKarya,
                'sudah_dinilai'   => $sudahDinilai,
                'belum_dinilai'   => $belumDinilai,
                'belum_submit'    => $belumSubmit,
                'total_tim_lomba' => $totalTimLomba,
            ];
        });

        return [
            'total_peserta'      => $totalPeserta,
            'total_tim'          => $totalTim,
            'karya_terkumpul'    => $karyaTerkumpul,
            'status_pendaftaran' => $statusPendaftaran,
            'category_breakdown' => $categoryBreakdown,
            'menunggu_breakdown' => $menungguBreakdown,
            'grading_progress'   => $gradingProgress
        ];
    }
}
