<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Services\AdminTeamService;
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
}
