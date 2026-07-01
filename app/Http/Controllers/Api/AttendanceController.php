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
        $isExport = $request->query('export') === 'true';

        $query = DB::table('attendance_records')
            ->join('users', 'attendance_records.user_id', '=', 'users.id')
            ->leftJoin('staff', 'users.id', '=', 'staff.user_id')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->where('attendance_records.event_id', $eventId)
            ->select(
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

                'attendance_records.scanned_at'
            )
            ->orderBy('attendance_records.scanned_at', 'desc');

        if ($search) {
            $query->where('users.name', 'like', "%{$search}%");
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
}
