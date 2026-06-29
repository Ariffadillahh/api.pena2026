<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AdminKaryaService;
use Illuminate\Http\Request;

class AdminKaryaController extends Controller
{
    protected $service;

    public function __construct(AdminKaryaService $service)
    {
        $this->service = $service;
    }

    public function getFolders(Request $request)
    {
        $folders = $this->service->getFolders($request->user());
        return response()->json([
            'status' => 'success',
            'data' => $folders
        ], 200);
    }

    public function getKarya(Request $request, $competitionId)
    {
        try {
            $perPage = $request->query('per_page', 10);
            $filter = $request->query('filter', 'all');
            $search = $request->query('search');

            $karya = $this->service->getKaryaInFolder($request->user(), $competitionId, $perPage, $filter, $search);

            return response()->json([
                'status' => 'success',
                'data' => $karya
            ], 200);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 500;
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }
}
