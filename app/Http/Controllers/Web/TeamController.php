<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Team::class, 'team');
    }

    public function index(Request $request)
    {
        $query = Team::query()->with(['owner', 'members']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        $perPage = $request->input('perPage', 10);
        $teams = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return view('teams.partials.table', compact('teams'))->render();
        }

        $totalCount = Team::count();
        $membersCount = \DB::table('team_user')->count();
        $newThisMonth = Team::whereMonth('created_at', now()->month)->count();
        $avgMembers = $totalCount > 0 ? round($membersCount / $totalCount) : 0;

        $stats = [
            'total' => $totalCount,
            'members' => $membersCount,
            'newThisMonth' => $newThisMonth,
            'avgMembers' => $avgMembers,
        ];

        return view('teams.index', compact('teams', 'stats'));
    }

    public function create()
    {
        return view('teams.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $team = Team::create([
            'name' => $data['name'],
            'description' => $data['description'],
            'owner_id' => auth()->id(),
        ]);

        // Owner is also a member/admin
        $team->members()->attach(auth()->id(), ['role' => 'admin']);

        return redirect()->route('teams.index')->with('success', 'Team created successfully.');
    }

    public function show(Team $team)
    {
        return view('teams.show', compact('team'));
    }

    public function edit(Team $team)
    {
        return view('teams.edit', compact('team'));
    }

    public function update(Request $request, Team $team)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $team->update($data);

        return redirect()->route('teams.index')->with('success', 'Team updated successfully.');
    }

    public function destroy(Team $team)
    {
        $team->delete();
        return redirect()->route('teams.index')->with('success', 'Team deleted successfully.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = json_decode($request->ids, true);
        if (empty($ids)) return back()->with('error', 'No teams selected.');

        Team::whereIn('id', $ids)->delete();

        return redirect()->route('teams.index')->with('success', count($ids) . ' teams deleted successfully.');
    }
}
