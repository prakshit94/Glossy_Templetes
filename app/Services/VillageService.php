<?php

namespace App\Services;

use App\Models\Village;
use App\Models\Service;
use App\Models\VillageServiceMapping;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\LazyCollection;
use Illuminate\Database\Eloquent\Collection;

class VillageService
{
    /**
     * Highly optimized check if a specific service is available for a village.
     * Uses MySQL EXISTS query which is significantly faster than loading relationships.
     */
    public function isServiceAvailable(int $villageId, string $serviceCode): bool
    {
        return Cache::remember("village_{$villageId}_service_{$serviceCode}", 3600, function () use ($villageId, $serviceCode) {
            return DB::table('village_service_mappings')
                ->join('services', 'village_service_mappings.service_id', '=', 'services.id')
                ->where('village_service_mappings.village_id', $villageId)
                ->where('services.code', $serviceCode)
                ->where('village_service_mappings.is_available', true)
                ->exists();
        });
    }

    /**
     * Optimized Bulk Import using MySQL UPSERT (Insert or Update).
     * Handles thousands of records in a single database trip.
     */
    public function bulkImportMappings(array $mappings): int
    {
        return DB::transaction(function () use ($mappings) {
            // Using UPSERT to handle millions of records efficiently
            // This prevents duplicate entry errors and updates existing ones in one go
            return DB::table('village_service_mappings')->upsert(
                $mappings,
                ['village_id', 'service_id'], // Unique constraint keys
                ['is_available', 'remarks', 'priority', 'updated_at'] // Columns to update on conflict
            );
        });
    }

    /**
     * Efficiently fetch villages by service using Cursor Pagination.
     * Cursor pagination is essential for millions of records as it avoids the "OFFSET" performance trap.
     */
    public function getVillagesByService(string $serviceCode, int $perPage = 50)
    {
        return Village::whereHas('serviceMappings', function ($query) use ($serviceCode) {
            $query->forService($serviceCode)->available();
        })
        ->select(['id', 'village_name', 'pincode', 'district_name'])
        ->orderBy('id')
        ->cursorPaginate($perPage);
    }

    /**
     * Mass update availability for a whole district or pincode.
     * Demonstrates chunking to prevent memory exhaustion.
     */
    public function toggleServiceForPincode(string $pincode, string $serviceCode, bool $status): void
    {
        $service = Service::where('code', $serviceCode)->firstOrFail();

        Village::byPincode($pincode)->chunkById(1000, function ($villages) use ($service, $status) {
            $mappings = $villages->map(fn($v) => [
                'village_id' => $v.id,
                'service_id' => $service->id,
                'is_available' => $status,
                'updated_at' => now(),
            ])->toArray();

            $this->bulkImportMappings($mappings);
        });
    }

    /**
     * Example of Lazy Collection usage for processing massive datasets (e.g., from CSV)
     */
    public function processLargeCsv(string $path): void
    {
        LazyCollection::make(function () use ($path) {
            $handle = fopen($path, 'r');
            while (($line = fgetcsv($handle)) !== false) {
                yield $line;
            }
            fclose($handle);
        })
        ->chunk(1000)
        ->each(function ($chunk) {
            // Process chunk and perform bulk upsert
            $this->bulkImportMappings($chunk->toArray());
        });
    }
}
