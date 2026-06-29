<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Team;
use App\Services\AdminAnnouncementService;
use Illuminate\Http\Request;

class AdminAnnouncementController extends Controller
{
    protected $service;

    public function __construct(AdminAnnouncementService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $announcements = $this->service->getList($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $announcements
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'target_type' => 'required|in:all,competition,team',
            'competition_id' => 'required_if:target_type,competition|nullable|uuid',
            'team_id' => 'required_if:target_type,team|nullable|uuid',
            'link' => 'nullable|url',
            'is_active' => 'boolean'
        ]);

        $data = $request->only(['title', 'content', 'target_type', 'competition_id', 'team_id', 'link']);
        $data['user_id'] = $request->user()->id;
        $data['is_active'] = $request->input('is_active', true);

        if ($data['target_type'] === 'all') {
            $data['competition_id'] = null;
            $data['team_id'] = null;
        }

        try {
            $announcement = $this->service->createAnnouncement($data);
            return response()->json(['status' => 'success', 'data' => $announcement], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'target_type' => 'required|in:all,competition,team',
            'competition_id' => 'required_if:target_type,competition|nullable|uuid',
            'team_id' => 'required_if:target_type,team|nullable|uuid',
            'link' => 'nullable|url',
            'is_active' => 'boolean'
        ]);

        $data = $request->only(['title', 'content', 'target_type', 'competition_id', 'team_id', 'link', 'is_active']);

        if ($data['target_type'] === 'all') {
            $data['competition_id'] = null;
            $data['team_id'] = null;
        }

        try {
            $announcement = $this->service->updateAnnouncement($id, $data);
            return response()->json(['status' => 'success', 'data' => $announcement], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->service->deleteAnnouncement($id);
            return response()->json(['status' => 'success', 'message' => 'Pengumuman dihapus'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function getAnnouncements(Request $request)
    {
        $user = $request->user();
        $perPage = $request->query('per_page', 20);

        $isAdmin = in_array($user->role_id, [1, 2]);

        $query = Announcement::where('is_active', true)
            ->orderBy('created_at', 'desc');

        if (!$isAdmin) {
            $team = Team::where('user_id', $user->id)->first();

            $query->where(function ($q) use ($team) {
                $q->where('target_type', 'all');

                if ($team) {
                    $q->orWhere(function ($subQ) use ($team) {
                        $subQ->where('target_type', 'competition')
                            ->where('competition_id', $team->competition_id);
                    });

                    $q->orWhere(function ($subQ) use ($team) {
                        $subQ->where('target_type', 'team')
                            ->where('team_id', $team->id);
                    });
                }
            });
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->paginate($perPage)
        ]);
    }

    public function getTeamAnnouncements($teamId)
    {
        try {
            $team = Team::findOrFail($teamId);

            $announcements = Announcement::where('is_active', true)
                ->where(function ($q) use ($team) {
                    $q->where('target_type', 'all');

                    $q->orWhere(function ($subQ) use ($team) {
                        $subQ->where('target_type', 'competition')
                            ->where('competition_id', $team->competition_id);
                    });

                    $q->orWhere(function ($subQ) use ($team) {
                        $subQ->where('target_type', 'team')
                            ->where('team_id', $team->id);
                    });
                })
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $announcements
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil riwayat pesan tim.'
            ], 500);
        }
    }
}
