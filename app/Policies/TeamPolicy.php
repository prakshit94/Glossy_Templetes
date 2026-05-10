<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TeamPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Any authenticated user can view teams they belong to (filtered in controller)
    }

    public function view(User $user, Team $team): bool
    {
        return $team->owner_id === $user->id || $team->members()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return true; // Any user can create a team
    }

    public function update(User $user, Team $team): bool
    {
        return $team->owner_id === $user->id;
    }

    public function delete(User $user, Team $team): bool
    {
        return $team->owner_id === $user->id;
    }
}
