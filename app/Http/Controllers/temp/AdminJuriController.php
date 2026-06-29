<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\JuriService;

class AdminJuriController extends Controller
{
    protected $juriService;

    public function __construct(JuriService $juriService)
    {
        $this->juriService = $juriService;
    }

    public function index(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 10);
            $juris = $this->juriService->getPaginatedJuris($perPage);

            return response()->json([
                'status' => 'success',
                'data' => $juris
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'competition_id' => 'required|exists:competitions,id'
        ]);

        try {
            $juri = $this->juriService->createNewJuri($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Akun juri berhasil dibuat dan instruksi telah dikirim ke email.',
                'data' => $juri
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
        ]);

        try {
            $juri = $this->juriService->updateJuri($id, $request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Data juri berhasil diperbarui.',
                'data' => $juri
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->juriService->deleteJuri($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Akun juri berhasil dihapus.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
