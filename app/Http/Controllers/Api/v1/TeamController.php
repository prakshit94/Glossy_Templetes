<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Http\Resources\TeamResource;
use App\Services\TeamService;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function __construct(
        protected TeamService $teamService
    ) {
        $this->authorizeResource(Team::class, 'team');
    }

    public function index(Request $request)
    {
        $teams = $request->user()->teams()->paginate();
        return TeamResource::collection($teams);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $team = $this->teamService->createTeam($request->user(), $data);

        return new TeamResource($team);
    }

    public function show(Team $team)
    {
        return new TeamResource($team);
    }

    public function update(Request $request, Team $team)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);

        $team->update($data);

        return new TeamResource($team);
    }

    public function destroy(Team $team)
    {
        $team->delete();
        return response()->json(null, 204);
    }

    public function invite(Request $request, Team $team)
    {
        $request->validate([
            'email' => 'required|email',
            'role' => 'required|string|in:member,admin',
        ]);

        $invitation = $this->teamService->inviteUser($team, $request->email, $request->role);

        return response()->json([
            'message' => 'Invitation sent.',
            'invitation' => $invitation,
        ]);
    }
}
