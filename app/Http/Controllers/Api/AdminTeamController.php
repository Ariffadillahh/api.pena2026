<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Services\AdminTeamService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminTeamController extends Controller
{
    protected $service;

    public function __construct(AdminTeamService $service)
    {
        $this->service = $service;
    }

    public function getFolders(Request $request)
    {
        $folders = $this->service->getFolders($request->user());
        return response()->json(['data' => $folders], 200);
    }


    public function getTeams(Request $request, $competitionId)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search');
            $status = $request->input('status');

            $teams = $this->service->getTeamsInFolder(
                $request->user(),
                $competitionId,
                $perPage,
                $search,
                $status
            );

            return response()->json($teams, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 403);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
            'payment_status' => 'required|string',
        ]);

        try {
            $team = Team::findOrFail($id);
            $team->status = $request->status;
            $team->payment_status = $request->payment_status;
            $team->updated_by = $request->user()->id;
            $team->save();

            return response()->json([
                'message' => 'Status tim berhasil diperbarui',
                'data' => $team
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengupdate: ' . $e->getMessage()], 500);
        }
    }

    public function getAllTeams()
    {
        try {
            $teams = Team::select('id', 'name', 'institution')
                ->orderBy('name', 'asc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $teams
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data tim: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->service->deleteTeam($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Tim beserta seluruh berkas berhasil dihapus secara permanen.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus tim: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportTeamsInFolder(Request $request, $folderId)
    {
        $search = $request->query('search', '');
        $status = $request->query('status', '');

        $query = \App\Models\Team::with(['members', 'wave', 'files', 'submission'])
            ->where('competition_id', $folderId)
            ->where('status', '!=', 'draft');

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('institution', 'like', "%{$search}%");
            });
        }

        $teams = $query->orderBy('created_at', 'desc')->get();

        $exportData = $teams->map(function ($team, $index) {

            $sortedMembers = $team->members->sortByDesc(function ($member) {
                return (strtolower($member->role) === 'ketua' || $member->is_leader == 1) ? 1 : 0;
            });

            $anggotaList = $sortedMembers->map(function ($member) {
                $roleLabel = (strtolower($member->role) === 'ketua' || $member->is_leader == 1) ? 'Ketua Tim' : 'Anggota';
                return "{$member->name} ({$member->email}) [{$roleLabel}]";
            })->implode(" | ");

            $waveName = $team->wave ? $team->wave->wave_name : 'Tidak Terdaftar/Gratis';
            $price = $team->wave ? $team->wave->price : 0;

            $fileFollow = $team->files->where('type', 'bukti_follow')->first();
            $fileTwibbon = $team->files->where('type', 'bukti_twibbon')->first();
            $filePernyataan = $team->files->where('type', 'surat_pernyataan')->first();
            $filePembayaran = $team->files->where('type', 'bukti_pembayaran')->first();

            $getLink = function ($file) {
                return $file ? asset('storage/' . $file->file_path) : 'Belum Upload';
            };

            $karyaLinks = [];
            if ($team->submission) {
                if (!empty($team->submission->file_path)) {
                    $karyaLinks[] = asset('storage/' . $team->submission->file_path) . "##Web Link";
                }
                if (!empty($team->submission->gdrive_link)) {
                    $karyaLinks[] = $team->submission->gdrive_link . "##GDrive";
                }
            }

            $karyaFinal = !empty($karyaLinks) ? implode(" | ", $karyaLinks) : 'Belum Kumpul';

            if ($team->score_board) {
                $totalSkor = asset('storage/' . $team->score_board);
            } else {
                $totalSkor = 'Belum Dinilai';
            }

            return [
                'no' => $index + 1,
                'nama_tim' => $team->name,
                'instansi' => $team->institution ?? '-',
                'anggota_tim' => $anggotaList,
                'status_pembayaran' => strtoupper($team->payment_status ?? 'PENDING'),
                'gelombang' => $waveName,
                'biaya' => 'Rp ' . number_format($price, 0, ',', '.'),
                'metode_pembayaran' => strtoupper($team->payment_method ?? '-'),
                'bukti_follow' => $getLink($fileFollow),
                'bukti_twibbon' => $getLink($fileTwibbon),
                'surat_pernyataan' => $getLink($filePernyataan),
                'bukti_pembayaran' => $getLink($filePembayaran),
                'karya' => $karyaFinal,
                'total_skor' => $totalSkor,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $exportData
        ]);
    }
}
