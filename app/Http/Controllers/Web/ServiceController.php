<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ServiceController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('permission:services.view')->only(['index', 'getVillages']);
        $this->middleware('permission:services.create')->only(['create', 'store']);
        $this->middleware('permission:services.edit')->only(['edit', 'update', 'bulkStatusUpdate']);
        $this->middleware('permission:services.delete')->only(['destroy', 'bulkDelete']);
    }

    public function index(Request $request)
    {
        $query = Service::query()->withCount(['mappings as total_villages' => function($q) {
            $q->where('is_available', true);
        }]);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
        }

        $services = $query->orderBy('id', 'asc')->paginate(10);

        // Enhance services with geographical breakdown
        foreach ($services as $service) {
            $service->geography = \App\Models\VillageServiceMapping::where('service_id', $service->id)
                ->where('is_available', true)
                ->join('villages', 'village_service_mappings.village_id', '=', 'villages.id')
                ->select(
                    DB::raw('count(distinct villages.state_name) as states_count'),
                    DB::raw('count(distinct villages.district_name) as districts_count'),
                    DB::raw('count(distinct villages.taluka_name) as talukas_count')
                )
                ->first();
                
            // Get Top 3 Districts for a quick preview
            $service->top_districts = \App\Models\VillageServiceMapping::where('service_id', $service->id)
                ->where('is_available', true)
                ->join('villages', 'village_service_mappings.village_id', '=', 'villages.id')
                ->groupBy('villages.district_name')
                ->orderByRaw('count(*) DESC')
                ->limit(3)
                ->pluck('villages.district_name');
        }

        // Stats for Widgets
        $stats = [
            'total' => Service::count(),
            'active' => Service::where('is_active', true)->count(),
            'mappings' => \App\Models\VillageServiceMapping::where('is_available', true)->count(),
        ];

        if ($request->ajax()) {
            return view('services.partials.table', compact('services'))->render();
        }

        return view('services.index', compact('services', 'stats'));
    }

    public function create()
    {
        return view('services.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:services,code',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        Service::create($validated);

        return redirect()->route('services.index')->with('success', 'Service created successfully.');
    }

    public function edit(Service $service)
    {
        return view('services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:services,code,' . $service->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $service->update($validated);

        return redirect()->route('services.index')->with('success', 'Service updated successfully.');
    }

    /**
     * Delete a specific service
     */
    public function destroy(Service $service)
    {
        $service->delete();
        return redirect()->route('services.index')->with('success', 'Service deleted successfully.');
    }

    /**
     * Get villages for a specific service (for hover dropdown)
     */
    public function getVillages(Request $request, Service $service)
    {
        $query = Village::whereHas('mappings', function($q) use ($service) {
            $q->where('service_id', $service->id)->where('is_available', true);
        });

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('village_name', 'like', "%{$search}%")
                  ->orWhere('pincode', 'like', "%{$search}%");
            });
        }

        $villages = $query->limit(100)->get(['id', 'village_name', 'pincode']);

        return response()->json($villages);
    }

    /**
     * Bulk Delete Services
     */
    public function bulkDelete(Request $request)
    {
        $ids = json_decode($request->ids);
        
        activity()
            ->performedOn(new Service())
            ->withProperties(['ids' => $ids])
            ->log('Bulk deleted ' . count($ids) . ' services');

        Service::whereIn('id', $ids)->delete();
        return redirect()->route('services.index')->with('success', 'Selected services deleted.');
    }

    /**
     * Bulk Status Update
     */
    public function bulkStatusUpdate(Request $request)
    {
        $ids = json_decode($request->ids);
        $status = $request->status === 'active';

        Service::whereIn('id', $ids)->update(['is_active' => $status]);

        activity()
            ->performedOn(new Service())
            ->withProperties([
                'ids' => $ids,
                'status' => $status ? 'Active' : 'Inactive'
            ])
            ->log('Bulk updated status for ' . count($ids) . ' services');

        return redirect()->route('services.index')->with('success', 'Service status updated.');
    }
}
