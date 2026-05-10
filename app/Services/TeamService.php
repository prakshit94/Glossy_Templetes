<?php

namespace App\Services;

use App\Models\Team;
use App\Models\User;
use App\Models\Invitation;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class TeamService
{
    public function createTeam(User $owner, array $data): Team
    {
        $team = Team::create([
            'name' => $data['name'],
            'owner_id' => $owner->id,
            'description' => $data['description'] ?? null,
        ]);

        $team->members()->attach($owner->id, ['role' => 'owner']);

        return $team;
    }

    public function inviteUser(Team $team, string $email, string $role = 'member'): Invitation
    {
        $invitation = $team->invitations()->create([
            'email' => $email,
            'role' => $role,
            'token' => Str::random(64),
            'expires_at' => now()->addDays(7),
        ]);

        // Mail::to($email)->send(new TeamInvitationMail($invitation));

        return $invitation;
    }

    public function acceptInvitation(string $token, User $user): bool
    {
        $invitation = Invitation::where('token', $token)->first();

        if (!$invitation || $invitation->isExpired()) {
            return false;
        }

        $invitation->team->members()->attach($user->id, ['role' => $invitation->role]);
        $invitation->delete();

        return true;
    }
}
