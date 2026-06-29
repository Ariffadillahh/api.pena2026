<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamAttendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminTicketController extends Controller
{
    public function scanTicket(Request $request)
    {
        $request->validate([
            'team_id' => 'required',
            'day'     => 'required|in:1,2'
        ]);

        $team = Team::with(['competition', 'attendance', 'members'])->find($request->team_id);

        if (!$team) {
            return response()->json(['status' => 'error', 'message' => 'Tiket Tidak Valid! Data tim tidak ditemukan.'], 404);
        }

        $day = $request->day;

        if ($day == 1) {
            if ($team->status !== 'lolos_top_10') {
                return response()->json([
                    'status' => 'error',
                    'message' => "Akses Ditolak. Hanya tim yang lolos top 10 yang dapat melakukan absensi hari pertama."
                ], 400);
            }
        }

        $attendance = TeamAttendance::firstOrCreate(['team_id' => $team->id]);

        if ($day == 1) {
            if ($attendance->day_1_status) {
                return response()->json(['status' => 'error', 'message' => 'Gagal! Tim ini SUDAH MELAKUKAN SCAN ABSEN untuk HARI PERTAMA.'], 400);
            }
            $attendance->update([
                'day_1_status' => true,
                'day_1_scanned_at' => now()->timezone('Asia/Jakarta')
            ]);
        } else {
            if ($attendance->day_2_status) {
                return response()->json(['status' => 'error', 'message' => 'Gagal! Tim ini SUDAH MELAKUKAN SCAN ABSEN untuk HARI KEDUA.'], 400);
            }
            $attendance->update([
                'day_2_status' => true,
                'day_2_scanned_at' => now()->timezone('Asia/Jakarta')
            ]);
        }

        $membersData = $team->members ? $team->members->map(function ($member) {
            return [
                'id' => $member->id,
                'name' => $member->name,
                'role' => $member->role ?? 'Anggota',
            ];
        }) : [];

        return response()->json([
            'status' => 'success',
            'message' => "Verifikasi Berhasil! Kehadiran HARI {$day} tercatat.",
            'data' => [
                'team_id'     => $team->id,
                'team_name'   => $team->name,
                'competition' => $team->competition->title ?? 'Tidak Diketahui',
                'category'    => $team->competition->category ?? 'Umum',
                'day'         => $day,
                'members'     => $membersData
            ]
        ], 200);
    }

    public function getAttendanceList()
    {
        $teams = Team::with(['competition', 'attendance'])
            ->where('status', 'lolos_top_10')
            ->get();

        $data = $teams->map(function ($team) {
            return [
                'id'           => $team->id,
                'team_name'    => $team->name,
                'competition'  => $team->competition->title ?? '-',
                'category'     => $team->competition->category ?? '-',
                'day_1_status' => $team->attendance->day_1_status ?? false,
                'day_1_time'   => $team->attendance->day_1_scanned_at
                    ? Carbon::parse($team->attendance->day_1_scanned_at)->format('H:i') . ' WIB'
                    : null,

                'day_2_status' => $team->attendance->day_2_status ?? false,
                'day_2_time'   => $team->attendance->day_2_scanned_at
                    ? Carbon::parse($team->attendance->day_2_scanned_at)->format('H:i') . ' WIB'
                    : null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $data
        ], 200);
    }
}
