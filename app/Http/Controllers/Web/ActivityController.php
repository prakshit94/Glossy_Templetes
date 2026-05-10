<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with('causer', 'subject')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('description', 'like', "%{$search}%")
                ->orWhere('event', 'like', "%{$search}%");
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        $activities = $query->paginate($request->get('perPage', 15))->withQueryString();

        // Mark as read when viewing index
        if (auth()->check()) {
            auth()->user()->update(['last_activity_read_at' => now()]);
        }

        if ($request->ajax()) {
            return view('activities.partials.table', compact('activities'))->render();
        }

        $totalActivities = Activity::count();
        $eventsCount = Activity::select('event', \DB::raw('count(*) as total'))->groupBy('event')->pluck('total', 'event');

        $stats = [
            'total' => $totalActivities,
            'created' => $eventsCount['created'] ?? 0,
            'updated' => $eventsCount['updated'] ?? 0,
            'deleted' => $eventsCount['deleted'] ?? 0,
        ];

        return view('activities.index', compact('activities', 'stats'));
    }

    public function markAsRead()
    {
        if (auth()->check()) {
            auth()->user()->update(['last_activity_read_at' => now()]);
        }
        return back();
    }

    public function bulkDelete(Request $request)
    {
        $ids = json_decode($request->ids, true);
        if (is_array($ids) && count($ids) > 0) {
            Activity::whereIn('id', $ids)->delete();
        }
        return back()->with('success', 'Selected activities permanently deleted.');
    }
}
