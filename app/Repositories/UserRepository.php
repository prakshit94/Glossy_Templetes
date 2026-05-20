<?php

namespace App\Repositories;

use App\Models\User;
use App\Contracts\UserRepositoryInterface;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function findByUsername(string $username): ?User
    {
        return $this->model->where('username', $username)->first();
    }

    public function findByEmailOrUsername(string $login): ?User
    {
        return $this->model->where(function ($query) use ($login) {
            $query->where('email', $login)
                  ->orWhere('username', $login);
        })->first();
    }

    public function updateLastLogin(User $user, string $ip): void
    {
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip,
        ]);
    }

    public function suspend(User $user): void
    {
        $user->update(['status' => 'suspended']);
    }

    public function activate(User $user): void
    {
        $user->update(['status' => 'active']);
    }
}
