<?php

namespace App\DTOs;

readonly class LoginDTO
{
    public function __construct(
        public string $login, // email or username
        public string $password,
        public bool $remember = false,
        public ?string $device_name = null,
        public ?string $mfa_token = null
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            login: $data['login'] ?? $data['email'],
            password: $data['password'],
            remember: $data['remember'] ?? false,
            device_name: $data['device_name'] ?? null,
            mfa_token: $data['mfa_token'] ?? null
        );
    }
}
