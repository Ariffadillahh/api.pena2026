<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CriteriaService;
use Illuminate\Http\Request;
use Exception;

class CriteriaController extends Controller
{
    protected $criteriaService;

    public function __construct(CriteriaService $criteriaService)
    {
        $this->criteriaService = $criteriaService;
    }

    public function index($competitionId)
    {
        $criteria = $this->criteriaService->getCriteria($competitionId);
        return response()->json(['status' => 'success', 'data' => $criteria], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'competition_id' => 'required|exists:competitions,id',
            'name' => 'required|string|max:255',
            'weight' => 'required|integer|min:1|max:100',
            'description' => 'nullable|string',
        ]);

        try {
            $criteria = $this->criteriaService->createCriteria($request->all());
            return response()->json(['status' => 'success', 'message' => 'Kriteria berhasil ditambahkan!', 'data' => $criteria], 201);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal menambahkan kriteria.'], 400);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'weight' => 'required|integer|min:1|max:100',
            'description' => 'nullable|string',
        ]);

        try {
            $criteria = $this->criteriaService->updateCriteria($id, $request->only(['name', 'weight', 'description']));
            return response()->json(['status' => 'success', 'message' => 'Kriteria berhasil diperbarui!', 'data' => $criteria], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal memperbarui kriteria.'], 400);
        }
    }

    public function destroy($id)
    {
        try {
            $this->criteriaService->deleteCriteria($id);
            return response()->json(['status' => 'success', 'message' => 'Kriteria berhasil dihapus!'], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal menghapus kriteria.'], 400);
        }
    }
}
