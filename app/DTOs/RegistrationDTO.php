<?php

namespace App\DTOs;

readonly class RegistrationDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $username,
        public string $password,
        public ?string $device_name = null
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            username: $data['username'],
            password: $data['password'],
            device_name: $data['device_name'] ?? null
        );
    }
}
