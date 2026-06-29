<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function getProfile(Request $request)
    {
        $team = Team::with(['members', 'competition'])->where('user_id', $request->user()->id)->first();

        if (!$team) {
            return response()->json([
                'message' => 'Data tim belum ditemukan',
                'data' => null
            ], 404);
        }

        $formattedMembers = $team->members->map(function ($member) {
            return [
                'id'    => $member->id,
                'name'  => $member->name,
                'email' => $member->email,
                'phone' => $member->phone,
                'role'  => $member->role ?? 'Anggota',
            ];
        });

        return response()->json([
            'message' => 'Sukses mengambil data profil tim',
            'data' => [
                'id'          => $team->id,
                'name'        => $team->name,
                'institution' => $team->institution,

                'kategori'    => $team->competition ? $team->competition->category : 'Belum Terdaftar di Kategori Lomba',

                'status'      => $team->status,
                'competition' => [
                    'title'   => $team->competition ? $team->competition->title : 'Belum Terdaftar di Lomba'
                ],
                'members'     => $formattedMembers
            ]
        ], 200);
    }
}
