<?php

namespace App\Contracts;

use App\Models\User;

interface UserRepositoryInterface extends RepositoryInterface
{
    public function findByEmail(string $email): ?User;
    public function findByUsername(string $username): ?User;
    public function findByEmailOrUsername(string $login): ?User;
    public function updateLastLogin(User $user, string $ip): void;
    public function suspend(User $user): void;
    public function activate(User $user): void;
}
