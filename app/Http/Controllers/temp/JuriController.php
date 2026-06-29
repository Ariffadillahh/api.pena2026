<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\JuriService;
use Exception;

class JuriController extends Controller
{
    protected $juriService;

    public function __construct(JuriService $juriService)
    {
        $this->juriService = $juriService;
    }

    public function getDashboard(Request $request)
    {
        try {
            $data = $this->juriService->getDashboardData($request->user()->id);

            return response()->json([
                'status' => 'success',
                'data'   => $data
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function getDaftarTim(Request $request)
    {
        try {
            $search = $request->query('search');
            $perPage = $request->query('per_page', 10);

            $data = $this->juriService->getDaftarTim($request->user()->id, $search, $perPage);

            return response()->json([
                'status' => 'success',
                'data'   => $data
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function getFormPenilaian(Request $request, $teamId)
    {
        try {
            $data = $this->juriService->getFormPenilaian($teamId, $request->user()->id);
            return response()->json(['status' => 'success', 'data' => $data], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 404);
        }
    }

    public function submitPenilaian(Request $request, $teamId)
    {
        $request->validate([
            'scores'               => 'required|array',
            'scores.*.criteria_id' => 'required|exists:assessment_criteria,id',
            'scores.*.score'       => 'required|numeric|min:0|max:100',
            'notes'                => 'nullable|string',
        ]);

        try {
            $pdfUrl = $this->juriService->submitPenilaian($teamId, $request->user()->id, $request->scores, $request->notes);

            return response()->json([
                'status' => 'success',
                'message' => 'Penilaian berhasil disimpan dan Scoreboard diperbarui!',
                'pdf_url' => $pdfUrl
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan nilai: ' . $e->getMessage()
            ], 400);
        }
    }

    public function getStandings(Request $request)
    {
        try {
            $search = $request->query('search');
            $perPage = $request->query('per_page', 10);
            $userId = $request->user()->id;

            $data = $this->juriService->getStandings($userId, $search, $perPage);

            return response()->json([
                'status' => 'success',
                'data'   => $data
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
