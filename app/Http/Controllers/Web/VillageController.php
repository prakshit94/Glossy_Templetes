<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Village;
use App\Models\Service;
use App\Models\VillageServiceMapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class VillageController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('permission:villages.view')->only(['index', 'show']);
        $this->middleware('permission:villages.create')->only(['create', 'store']);
        $this->middleware('permission:villages.edit')->only(['edit', 'update', 'bulkServiceUpdate']);
        $this->middleware('permission:villages.delete')->only(['destroy', 'bulkDelete']);
        $this->middleware('permission:villages.import')->only(['import']);
    }

    /**
     * Display a listing of the villages with dependent filters.
     */
    public function index(Request $request)
    {
        $query = Village::with(['mappings' => function($q) {
            $q->where('is_available', true)->with('service');
        }]);

        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('village_name', 'like', "%{$search}%")
                  ->orWhere('pincode', 'like', "{$search}%")
                  ->orWhere('taluka_name', 'like', "%{$search}%")
                  ->orWhere('district_name', 'like', "%{$search}%");
            });
        }

        /*
        |--------------------------------------------------------------------------
        | GEOGRAPHIC FILTERS
        |--------------------------------------------------------------------------
        */
        if ($request->filled('state')) {
            $states = array_filter(array_map('trim', explode(',', $request->state)));
            $query->whereIn('state_name', $states);
        }

        if ($request->filled('district')) {
            $districts = array_filter(array_map('trim', explode(',', $request->district)));
            $query->whereIn('district_name', $districts);
        }

        if ($request->filled('taluka')) {
            $talukas = array_filter(array_map('trim', explode(',', $request->taluka)));
            $query->whereIn('taluka_name', $talukas);
        }

        /*
        |--------------------------------------------------------------------------
        | STATS & DATA
        |--------------------------------------------------------------------------
        */
        $stats = [
            'total' => (clone $query)->count(),
            'pincodes' => (clone $query)->distinct('pincode')->count('pincode'),
            'districts_count' => (clone $query)->distinct('district_name')->count('district_name'),
            'services' => Service::active()->count(),
        ];

        $perPage = (int) $request->get('perPage', 10);
        $villages = $query->orderBy('id', 'desc')->paginate($perPage)->withQueryString();

        // Get Dynamic Lists for Filters
        $statesList = Village::distinct()->pluck('state_name')->filter()->sort()->values();
        
        $districtsList = Village::when($request->filled('state'), function($q) use ($request) {
            $states = array_map('trim', explode(',', $request->state));
            $q->whereIn('state_name', $states);
        })->distinct()->pluck('district_name')->filter()->sort()->values();

        $talukasList = Village::when($request->filled('district'), function($q) use ($request) {
            $districts = array_map('trim', explode(',', $request->district));
            $q->whereIn('district_name', $districts);
        })->distinct()->pluck('taluka_name')->filter()->sort()->values();

        if ($request->ajax()) {
            return response()->json([
                'table' => view('villages.partials.table', compact('villages'))->render(),
                'districts' => $districtsList,
                'talukas' => $talukasList,
                'stats' => $stats
            ]);
        }

        return view('villages.index', compact(
            'villages', 
            'stats', 
            'statesList', 
            'districtsList', 
            'talukasList'
        ));
    }

    public function create()
    {
        return view('villages.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'village_name' => 'required|string|max:255',
            'pincode' => 'required|string|max:10',
            'post_so_name' => 'nullable|string|max:255',
            'taluka_name' => 'nullable|string|max:255',
            'district_name' => 'nullable|string|max:255',
            'state_name' => 'nullable|string|max:255',
        ]);

        Village::create($validated);

        return redirect()->route('villages.index')->with('success', 'Village created successfully.');
    }

    public function edit(Village $village)
    {
        $services = Service::active()->get();
        $mappings = $village->mappings()->get()->keyBy('service_id');
        
        return view('villages.edit', compact('village', 'services', 'mappings'));
    }

    public function update(Request $request, Village $village)
    {
        $village->update($request->only([
            'village_name', 'pincode', 'post_so_name', 'taluka_name', 'district_name', 'state_name'
        ]));

        if ($request->has('services')) {
            foreach ($request->services as $serviceId => $data) {
                if (isset($data['is_available'])) {
                    VillageServiceMapping::updateOrCreate(
                        ['village_id' => $village->id, 'service_id' => $serviceId],
                        [
                            'is_available' => true,
                            'priority' => $data['priority'] ?? 0,
                            'remarks' => $data['remarks'] ?? null,
                            'updated_at' => now(),
                        ]
                    );
                } else {
                    VillageServiceMapping::where('village_id', $village->id)
                        ->where('service_id', $serviceId)
                        ->update(['is_available' => false]);
                }
            }
        }

        return redirect()->route('villages.index')->with('success', 'Village updated successfully.');
    }

    public function destroy(Village $village)
    {
        $village->delete();
        return redirect()->route('villages.index')->with('success', 'Village deleted.');
    }

    /**
     * Bulk Delete
     */
    public function bulkDelete(Request $request)
    {
        $ids = json_decode($request->ids);
        
        activity()
            ->performedOn(new Village())
            ->withProperties(['ids' => $ids])
            ->log('Bulk deleted ' . count($ids) . ' villages');

        Village::whereIn('id', $ids)->delete();
        return redirect()->route('villages.index')->with('success', 'Selected villages deleted.');
    }

    /**
     * Bulk Service Toggle
     */
    public function bulkServiceUpdate(Request $request)
    {
        $ids = json_decode($request->ids);
        $serviceId = $request->service_id;
        $status = $request->status === 'available';
        $service = Service::find($serviceId);

        $mappings = [];
        foreach ($ids as $id) {
            $mappings[] = [
                'village_id' => $id,
                'service_id' => $serviceId,
                'is_available' => $status,
                'updated_at' => now(),
            ];
        }

        DB::table('village_service_mappings')->upsert(
            $mappings,
            ['village_id', 'service_id'],
            ['is_available', 'updated_at']
        );

        activity()
            ->performedOn(new Village())
            ->withProperties([
                'ids' => $ids,
                'service' => $service->name ?? 'Unknown',
                'status' => $status ? 'Available' : 'Unavailable'
            ])
            ->log('Bulk updated service status for ' . count($ids) . ' villages');

        return redirect()->route('villages.index')->with('success', 'Service status updated for selected villages.');
    }

    /**
     * Handle CSV Import
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        DB::transaction(function () use ($path) {
            \Illuminate\Support\LazyCollection::make(function () use ($path) {
                $handle = fopen($path, 'r');
                fgetcsv($handle);
                while (($line = fgetcsv($handle)) !== false) {
                    yield $line;
                }
                fclose($handle);
            })
            ->chunk(1000)
            ->each(function ($chunk) {
                $data = $chunk->map(function ($row) {
                    if (count($row) < 2) return null;
                    return [
                        'village_name'    => $row[0],
                        'normalized_name' => strtolower(trim($row[0])),
                        'pincode'         => $row[1],
                        'post_so_name'    => ($row[2] ?? null) === '#N/A' ? null : ($row[2] ?? null),
                        'taluka_name'     => ($row[3] ?? null) === '#N/A' ? null : ($row[3] ?? null),
                        'district_name'   => ($row[4] ?? null) === '#N/A' ? null : ($row[4] ?? null),
                        'state_name'      => ($row[5] ?? null) === '#N/A' ? null : ($row[5] ?? null),
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ];
                })->filter()->toArray();

                DB::table('villages')->insert($data);
            });
        });

        activity()
            ->log('Imported villages from CSV file');

        return redirect()->route('villages.index')->with('success', 'Villages imported successfully.');
    }
}
