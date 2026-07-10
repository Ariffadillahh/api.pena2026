<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AttendanceEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AttendanceController extends Controller
{
    public function getEvents()
    {
        return response()->json([
            'status' => 'success',
            'data' => AttendanceEvent::orderBy('date', 'desc')->get()
        ]);
    }

    public function createEvent(Request $request)
    {
        $request->validate(['title' => 'required', 'date' => 'required|date']);

        $event = AttendanceEvent::create([
            'id' => (string) Str::uuid(),
            'title' => $request->title,
            'date' => $request->date,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Jadwal dibuat!']);
    }

    public function updateEvent(Request $request, $eventId)
    {
        $request->validate(['title' => 'required', 'date' => 'required|date']);

        $event = AttendanceEvent::findOrFail($eventId);
        $event->update([
            'title' => $request->title,
            'date' => $request->date,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Jadwal berhasil diperbarui!']);
    }

    public function deleteEvent($eventId)
    {
        $event = AttendanceEvent::findOrFail($eventId);
        $event->delete();

        return response()->json(['status' => 'success', 'message' => 'Jadwal berhasil dihapus!']);
    }

    public function scanQr(Request $request, $eventId)
    {
        $admin = $request->user();

        $isPO = $admin->role_id === 'rol_1a2b3c';

        $isSekertaris = false;
        if (!$isPO) {
            $staffProfile = DB::table('staff')->where('user_id', $admin->id)->first();
            if ($staffProfile && $staffProfile->division === 'Sekretaris') {
                $isSekertaris = true;
            }
        }

        if (!$isPO && !$isSekertaris) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses Ditolak! Hanya Project Officer dan Divisi Sekertaris yang berhak melakukan scan kehadiran.'
            ], 403);
        }


        $user = User::find($request->user_id);
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        $exists = DB::table('attendance_records')
            ->where('event_id', $eventId)
            ->where('user_id', $user->id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => $user->name . ' sudah absen!'], 400);
        }

        DB::table('attendance_records')->insert([
            'id' => (string) Str::uuid(),
            'event_id' => $eventId,
            'user_id' => $user->id,
            'scanned_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil absen: ' . $user->name
        ]);
    }

    public function getAttendees(Request $request, $eventId)
    {
        $perPage = $request->query('per_page', 30);
        $search = $request->query('search', '');
        $status = $request->query('status', 'hadir');
        $division = $request->query('division', 'all');
        $isExport = $request->query('export') === 'true';

        $query = DB::table('users')
            ->leftJoin('staff', 'users.id', '=', 'staff.user_id')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->leftJoin('attendance_records', function ($join) use ($eventId) {
                $join->on('users.id', '=', 'attendance_records.user_id')
                    ->where('attendance_records.event_id', '=', $eventId);
            })
            ->where(function ($q) {
                $q->whereNotNull('staff.id')
                    ->orWhere('users.role_id', 'rol_1a2b3c')
                    ->orWhere('users.email', 'nabila.cahya.ramadhani.ts25@stu.pnj.ac.id');
            })
            ->select(
                'users.id as user_id',
                'users.name',
                'users.email',
                DB::raw("CASE 
                    WHEN users.email = 'nabila.cahya.ramadhani.ts25@stu.pnj.ac.id' THEN 'VPO' 
                    ELSE roles.name 
                END as role_name"),
                DB::raw("CASE 
                    WHEN users.email = 'nabila.cahya.ramadhani.ts25@stu.pnj.ac.id' THEN 'VPO' 
                    WHEN users.role_id = 'rol_1a2b3c' THEN 'PO' 
                    ELSE staff.division 
                END as division"),
                'attendance_records.scanned_at',
                'attendance_records.status as attendance_status'
            );

        if ($status === 'hadir') {
            $query->where('attendance_records.status', 'hadir');
        } elseif ($status === 'izin') {
            $query->where('attendance_records.status', 'izin');
        } elseif ($status === 'belum') {
            $query->whereNull('attendance_records.id');
        }

        if ($search) {
            $query->where('users.name', 'like', "%{$search}%");
        }

        if ($division !== 'all' && $division !== '') {
            $query->where(function ($q) use ($division) {
                if ($division === 'VPO') {
                    $q->where('users.email', 'nabila.cahya.ramadhani.ts25@stu.pnj.ac.id');
                } elseif ($division === 'PO') {
                    $q->where('users.role_id', 'rol_1a2b3c'); 
                } else {
                    $q->where('staff.division', $division);
                }
            });
        }

        if ($status === 'belum' || $status === 'semua' || $status === 'izin') {
            $query->orderBy('users.name', 'asc');
        } else {
            $query->orderBy('attendance_records.scanned_at', 'desc');
        }

        if ($isExport) {
            return response()->json([
                'status' => 'success',
                'data' => $query->get()
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->paginate($perPage)
        ]);
    }

    public function updateAttendanceStatus(Request $request, $eventId, $userId)
    {
        $status = $request->input('status');

        if ($status === 'alfa') {
            DB::table('attendance_records')
                ->where('event_id', $eventId)
                ->where('user_id', $userId)
                ->delete();

            return response()->json(['status' => 'success', 'message' => 'Status diubah menjadi ALFA']);
        }

        $record = DB::table('attendance_records')
            ->where('event_id', $eventId)
            ->where('user_id', $userId)
            ->first();

        if ($record) {
            DB::table('attendance_records')
                ->where('id', $record->id)
                ->update([
                    'status' => $status,
                    'scanned_at' => $record->scanned_at ?? now(),
                    'updated_at' => now(),
                ]);

            return response()->json(['status' => 'success', 'message' => 'Status diubah menjadi ' . strtoupper($status)]);
        } else {
          
            DB::table('attendance_records')->insert([
                'id'         => (string) Str::uuid(),
                'event_id'   => $eventId,
                'user_id'    => $userId,
                'status'     => $status,
                'scanned_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['status' => 'success', 'message' => 'Status diubah menjadi ' . strtoupper($status)]);
        }
    }
}
