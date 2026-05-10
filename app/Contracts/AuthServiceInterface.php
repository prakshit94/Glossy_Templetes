<?php

namespace App\Contracts;

use App\Models\User;
use App\DTOs\LoginDTO;
use App\DTOs\RegistrationDTO;

interface AuthServiceInterface
{
    public function login(LoginDTO $data): array;
    public function register(RegistrationDTO $data): User;
    public function logout(User $user, bool $allDevices = false): void;
    public function refreshTokens(string $refreshToken): array;
    public function impersonate(User $admin, User $target): void;
    public function stopImpersonating(): void;
}
