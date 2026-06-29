<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\Team;
use App\Services\SubmissionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubmissionController extends Controller
{
    protected $submissionService;

    public function __construct(SubmissionService $submissionService)
    {
        $this->submissionService = $submissionService;
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'nullable|file|mimes:pdf,png,jpg,jpeg|max:10240',
            'gdrive_link' => 'nullable|url'
        ]);

        if (!$request->hasFile('file') && !$request->filled('gdrive_link')) {
            return response()->json(['message' => 'Harap unggah file atau masukkan link G-Drive.'], 422);
        }

        $deadlineString = env('DEADLINE_PENDAFTARAN', '2026-08-22 00:00:00');
        $deadline = Carbon::parse($deadlineString);

        try {
            DB::beginTransaction();

            $team = Team::where('user_id', $request->user()->id)->firstOrFail();

            if (now() > $deadline) {
                return response()->json(['message' => 'Batas waktu pengumpulan telah lewat!'], 403);
            }

            $submission = $this->submissionService->handleUpload(
                $team->id,
                $request->file('file'),
                $request->gdrive_link
            );

            DB::commit();

            return response()->json([
                'message' => 'Karya berhasil disubmit!',
                'data' => $submission
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal mengunggah karya. Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function get(Request $request)
    {
        $team = Team::where('user_id', $request->user()->id)->first();
        if (!$team) {
            return response()->json(['message' => 'Team tidak ditemukan'], 404);
        }

        $submission = Submission::where('team_id', $team->id)->first();

        if ($submission && $submission->file_path) {
            $submission->file_url = url('storage/' . $submission->file_path);
        }

        return response()->json(['data' => $submission], 200);
    }
}
