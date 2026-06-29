<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CompetitionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CompetitionController extends Controller
{
    protected $competitionService;

    public function __construct(CompetitionService $competitionService)
    {
        $this->competitionService = $competitionService;
    }

    public function index(): JsonResponse
    {
        try {
            $competitions = $this->competitionService->getAllCompetitions();

            return response()->json([
                'status' => 'success',
                'data' => $competitions
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $slug): JsonResponse
    {
        try {
            $competition = $this->competitionService->getCompetitionDetail($slug);

            if (!$competition) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Lomba tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $competition
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getActiveCompetitions(): JsonResponse
    {
        try {
            $competitions = $this->competitionService->getActiveCompetitions();

            return response()->json([
                'status' => 'success',
                'data' => $competitions
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'requirements' => 'nullable|array',
            'requirements.*.requirement' => 'required_with:requirements|string',
            'waves' => 'nullable|array',
            'waves.*.wave_name' => 'required_with:waves|string',
            'waves.*.price' => 'required_with:waves|numeric',
            'waves.*.start_date' => 'required_with:waves|date',
            'waves.*.end_date' => 'required_with:waves|date',
        ]);

        try {
            $competition = $this->competitionService->storeCompetition($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Lomba berhasil ditambahkan',
                'data' => $competition
            ], 201);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $competition = $this->competitionService->updateCompetition($id, $request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Lomba berhasil diperbarui',
                'data' => $competition
            ], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->competitionService->deleteCompetition($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Lomba berhasil dihapus'
            ], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
