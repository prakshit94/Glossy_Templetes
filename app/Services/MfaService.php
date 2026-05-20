<?php

namespace App\Services;

use App\Models\User;
use App\Contracts\MfaServiceInterface;
use Illuminate\Support\Str;
use PragmaRX\Google2FALaravel\Support\Authenticator;

class MfaService implements MfaServiceInterface
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = app('pragmarx.google2fa');
    }

    public function generateSecret(User $user): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function getQrCodeUrl(User $user): string
    {
        return $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $user->two_factor_secret
        );
    }

    public function verifyToken(User $user, string $token): bool
    {
        return $this->google2fa->verifyKey($user->two_factor_secret, $token);
    }

    public function enableMfa(User $user, string $token): void
    {
        if (!$this->verifyToken($user, $token)) {
            throw new \InvalidArgumentException('Invalid MFA token.');
        }
        $user->update([
            'two_factor_confirmed_at' => now(),
        ]);
    }

    public function disableMfa(User $user): void
    {
        $user->update([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);
    }

    public function generateRecoveryCodes(User $user): array
    {
        $codes = collect(range(1, 8))->map(function () {
            return strtolower(Str::random(10) . '-' . Str::random(10));
        })->toArray();

        $user->update([
            'two_factor_recovery_codes' => encrypt(json_encode($codes)),
        ]);

        return $codes;
    }

    public function verifyRecoveryCode(User $user, string $code): bool
    {
        $codes = json_decode(decrypt($user->two_factor_recovery_codes), true);

        if (in_array($code, $codes)) {
            $newCodes = array_diff($codes, [$code]);
            $user->update([
                'two_factor_recovery_codes' => encrypt(json_encode(array_values($newCodes))),
            ]);
            return true;
        }

        return false;
    }
}
