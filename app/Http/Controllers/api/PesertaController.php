<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;

class PesertaController extends Controller
{
    public function getDashboardStatus(Request $request)
    {
        $team = Team::where('user_id', $request->user()->id)
            ->with('competition:id,announcement_date')
            ->first();

        if (!$team) return response()->json(['message' => 'Team tidak ditemukan'], 404);

        $today = now();
        $pendaftaranText = 'Belum Daftar';

        if ($team->status === 'draft') {
            $pendaftaranText = 'Belum Daftar';
        } elseif ($team->status === 'menunggu_konfirmasi') {
            $pendaftaranText = 'Menunggu Konfirmasi';
        } elseif (in_array($team->status, ['menunggu_penilaian', 'dinilai', 'lolos_top_10', 'tidak_lolos'])) {
            $pendaftaranText = 'Selesai';
        }

        $displayStatus = [
            'pendaftaran' => $pendaftaranText,
            'payment'     => ucfirst(str_replace('_', ' ', $team->payment_status)),
            'unggahKarya' => 'Belum',
            'top10'       => 'Belum'
        ];

        if ($team->payment_status === 'valid') {
            $displayStatus['payment'] = 'Selesai';
        }

        $isPaymentValid = $team->payment_status === 'valid';
        $isRegistrationDone = in_array($team->status, ['menunggu_penilaian', 'dinilai', 'lolos_top_10', 'tidak_lolos']);

        if ($isPaymentValid && $isRegistrationDone) {
            $displayStatus['unggahKarya'] = $team->karya_uploaded ? 'Selesai' : 'Sedang Berlangsung';

            if ($team->competition && $team->competition->announcement_date) {
                $annDate = \Carbon\Carbon::parse($team->competition->announcement_date);

                if ($today >= $annDate) {
                    $displayStatus['top10'] = ($team->status === 'lolos_top_10') ? 'Lolos Finalis' : 'Tidak Lolos';
                } else {
                    $displayStatus['top10'] = 'Menunggu';
                }
            }
        }

        return response()->json($displayStatus);
    }

    public function getTiketFinalis(Request $request)
    {
        $user = $request->user();

        $team = Team::with(['competition', 'submission'])
            ->where('user_id', $user->id)
            ->first();

        if (!$team) {
            return response()->json(['status' => 'error', 'message' => 'Data tim tidak ditemukan.'], 404);
        }

        if ($team->status !== 'lolos_top_10') {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak. Tim Anda belum dinyatakan lolos sebagai finalis.'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $team->id,
                'name' => $team->name,
                'submission_title' => $team->submission->original_filename ?? 'Karya Tim ' . $team->name,
                'competition_title' => $team->competition->title ?? 'Tidak Diketahui',
                'competition_category' => $team->competition->category ?? 'Umum',
            ]
        ], 200);
    }
}
