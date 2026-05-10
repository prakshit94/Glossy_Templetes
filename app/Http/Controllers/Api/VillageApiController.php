<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Village;
use App\Models\Service;
use App\Http\Resources\VillageResource;
use App\Services\VillageService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VillageApiController extends Controller
{
    public function __construct(
        protected VillageService $villageService
    ) {}

    /**
     * List villages with cursor pagination for high performance
     */
    public function index(Request $request)
    {
        $villages = Village::orderBy('id')
            ->cursorPaginate($request->get('perPage', 20));

        return VillageResource::collection($villages);
    }

    /**
     * Search villages by name or pincode
     */
    public function search(Request $request)
    {
        $request->validate(['q' => 'required|string|min:3']);

        $villages = Village::search($request->q)
            ->limit(50)
            ->get();

        return VillageResource::collection($villages);
    }

    /**
     * Check serviceability for a specific village and service
     */
    public function checkServiceability(Request $request): JsonResponse
    {
        $request->validate([
            'village_id' => 'required|exists:villages,id',
            'service_code' => 'required|exists:services,code',
        ]);

        $available = $this->villageService->isServiceAvailable(
            $request->village_id,
            $request->service_code
        );

        return response()->json([
            'village_id' => $request->village_id,
            'service_code' => $request->service_code,
            'is_serviceable' => $available,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Bulk Import API (Queue-based recommended in real prod, here direct for example)
     */
    public function bulkImport(Request $request): JsonResponse
    {
        $request->validate([
            'data' => 'required|array',
            'data.*.village_id' => 'required|integer',
            'data.*.service_id' => 'required|integer',
            'data.*.is_available' => 'required|boolean',
        ]);

        $count = $this->villageService->bulkImportMappings($request->data);

        return response()->json([
            'message' => 'Bulk import successful',
            'processed_records' => $count,
        ]);
    }
}
