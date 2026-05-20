<?php

namespace App\Services;

use App\Models\User;
use App\DTOs\LoginDTO;
use App\DTOs\RegistrationDTO;
use App\Contracts\AuthServiceInterface;
use App\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    public function login(LoginDTO $data): array
    {
        $user = $this->userRepository->findByEmailOrUsername($data->login);

        if (!$user || !Hash::check($data->password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => [trans('auth.failed')],
            ]);
        }

        if ($user->status === 'suspended') {
            throw ValidationException::withMessages([
                'login' => ['Your account is suspended.'],
            ]);
        }

        // Handle MFA if enabled
        if ($user->two_factor_secret && !$data->mfa_token) {
            return ['requires_mfa' => true, 'user_id' => $user->id];
        }

        if ($data->mfa_token) {
            $mfaService = app(\App\Services\MfaService::class);
            if (!$mfaService->verifyToken($user, $data->mfa_token)) {
                throw ValidationException::withMessages([
                    'mfa_token' => ['Invalid MFA token.'],
                ]);
            }
        }

        Auth::login($user, $data->remember);
        $this->userRepository->updateLastLogin($user, request()->ip());

        $token = $user->createToken($data->device_name ?? 'web')->plainTextToken;
        $refreshToken = $this->createRefreshToken($user);

        return [
            'user' => $user,
            'access_token' => $token,
            'refresh_token' => $refreshToken->token,
        ];
    }

    public function register(RegistrationDTO $data): User
    {
        $user = $this->userRepository->create([
            'name' => $data->name,
            'email' => $data->email,
            'username' => $data->username,
            'password' => Hash::make($data->password),
        ]);

        return $user;
    }

    public function logout(User $user, bool $allDevices = false): void
    {
        if ($allDevices) {
            $user->tokens()->delete();
            $user->refreshTokens()->update(['revoked' => true]);
        } else {
            $user->currentAccessToken()->delete();
        }

        Auth::logout();
    }

    public function refreshTokens(string $refreshToken): array
    {
        $tokenRecord = \App\Models\RefreshToken::where('token', $refreshToken)->first();

        if (!$tokenRecord || !$tokenRecord->isValid()) {
            throw new \Exception('Invalid or expired refresh token.');
        }

        $user = $tokenRecord->user;
        $newToken = $user->createToken('refreshed')->plainTextToken;
        
        // Rotate refresh token
        $tokenRecord->update(['revoked' => true]);
        $newRefreshToken = $this->createRefreshToken($user);

        return [
            'access_token' => $newToken,
            'refresh_token' => $newRefreshToken->token,
        ];
    }

    protected function createRefreshToken(User $user)
    {
        return $user->refreshTokens()->create([
            'token' => Str::random(64),
            'expires_at' => now()->addDays(30),
        ]);
    }

    public function impersonate(User $admin, User $target): void
    {
        session(['impersonate' => $admin->id]);
        Auth::login($target);
    }

    public function stopImpersonating(): void
    {
        $originalId = session()->pull('impersonate');
        if ($originalId) {
            $admin = User::find($originalId);
            Auth::login($admin);
        }
    }
}
