<?php

namespace App\Contracts;

use App\Models\User;

interface MfaServiceInterface
{
    public function generateSecret(User $user): string;
    public function getQrCodeUrl(User $user): string;
    public function verifyToken(User $user, string $token): bool;
    public function enableMfa(User $user, string $token): void;
    public function disableMfa(User $user): void;
    public function generateRecoveryCodes(User $user): array;
    public function verifyRecoveryCode(User $user, string $code): bool;
}
