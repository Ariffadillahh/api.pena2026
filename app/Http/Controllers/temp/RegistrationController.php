<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\PjNewRegistrationMail;
use App\Models\Competition;
use App\Models\RegistrationWave;
use App\Models\Team;
use App\Models\TeamFile;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\RegistrationService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class RegistrationController extends Controller
{
    protected $registrationService;

    public function __construct(RegistrationService $registrationService)
    {
        $this->registrationService = $registrationService;
    }

    public function storeStep1(Request $request)
    {
        $validated = $request->validate([
            'competition_id' => 'required|uuid',
            'wave_id' => 'nullable|uuid',
            'team_name' => 'required|string|max:255',
            'institution' => 'required|string|max:255',
            'members' => 'required|array|min:1',
            'members.*.name' => 'required|string',
            'members.*.email' => 'required|email',
            'members.*.phone' => 'required|string',
            'members.*.role' => 'required|string|in:Ketua,Anggota',
        ]);

        try {
            $team = $this->registrationService->saveStep1($request->user()->id, $validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Data tim dan anggota berhasil disimpan.',
                'data' => $team
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function uploadFile(Request $request)
    {
        $validated = $request->validate([
            'competition_id' => 'required|uuid',
            'type' => 'required|string|in:bukti_pembayaran,bukti_follow,bukti_twibbon,surat_pernyataan,karya_tulis,poster',
            'file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,zip',
        ]);

        try {
            $fileData = $this->registrationService->uploadDocument(
                $request->user()->id,
                $validated['competition_id'],
                $request->file('file'),
                $validated['type']
            );

            return response()->json([
                'status' => 'success',
                'message' => 'File ' . $validated['type'] . ' berhasil diunggah.',
                'data' => $fileData
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengunggah file: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDraft(Request $request, $competitionId)
    {
        $draft = Team::with(['members', 'files'])
            ->where('user_id', $request->user()->id)
            ->where('competition_id', $competitionId)
            ->where('status', 'draft')
            ->first();

        return response()->json($draft);
    }

    public function deleteFile(Request $request)
    {
        $validated = $request->validate([
            'competition_id' => 'required|uuid',
            'type' => 'required|string',
        ]);

        $team = Team::where('user_id', $request->user()->id)
            ->where('competition_id', $validated['competition_id'])
            ->firstOrFail();

        $file = TeamFile::where('team_id', $team->id)
            ->where('type', $validated['type'])
            ->first();

        if ($file) {
            Storage::disk('public')->delete($file->file_path);
            $file->delete();
            return response()->json(['message' => 'File berhasil dihapus']);
        }

        return response()->json(['message' => 'File tidak ditemukan'], 404);
    }

    public function finalizeRegistration(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'competition_id' => 'required|uuid|exists:competitions,id',
            'payment_method' => 'required|string',
            'wave_id'        => 'required|uuid|exists:registration_waves,id',
        ]);

        $wave = RegistrationWave::where('id', $validated['wave_id'])
            ->where('competition_id', $validated['competition_id'])
            ->first();

        if (!$wave) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gelombang pendaftaran tidak cocok dengan kompetisi ini.'
            ], 422);
        }

        $today = Carbon::today();
        $startDate = Carbon::parse($wave->start_date)->startOfDay();
        $endDate = Carbon::parse($wave->end_date)->endOfDay()->setTime(23, 59, 59, 999);

        if ($today->lt($startDate) || $today->gt($endDate)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Masa berlaku gelombang pendaftaran ini telah habis. Silakan refresh halaman untuk memperbarui data.'
            ], 422);
        }

        $team = Team::where('user_id', $request->user()->id)
            ->where('competition_id', $validated['competition_id'])
            ->first();

        if (!$team) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data pendaftaran tim Anda tidak ditemukan. Silakan isi data tim terlebih dahulu.'
            ], 444);
        }

        $team->update([
            'status'         => 'menunggu_konfirmasi',
            'payment_method' => $validated['payment_method'],
            'wave_id'        => $validated['wave_id'],
        ]);

        $competition = Competition::find($validated['competition_id']);
        $compId = $validated['competition_id'];

        $pjs = User::whereHas('staffProfile', function ($query) use ($compId) {
            $query->where('pj_competition_id', $compId);
        })->get();

        if ($pjs->isNotEmpty() && $competition) {
            foreach ($pjs as $pj) {
                Mail::to($pj->email)->send(new PjNewRegistrationMail($team, $competition));
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Pendaftaran berhasil dikirim!'
        ], 200);
    }
}
