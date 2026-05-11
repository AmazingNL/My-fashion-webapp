<?php

namespace App\Mappers;

use App\DTO\RegistrationRequestDto;
use App\Models\User;

class RegistrationRequestMapper
{
    public static function mapToRegistrationRequestDto(array $data): RegistrationRequestDto
    {
        return new RegistrationRequestDto(
            trim((string) ($data['firstName'] ?? '')),
            trim((string) ($data['lastName'] ?? '')),
            trim((string) ($data['email'] ?? '')),
            trim((string) ($data['phone'] ?? '')),
            (string) ($data['password'] ?? '')
        );
    }

    public static function mapToUser(RegistrationRequestDto $dto): User
    {
        return new User(
            null,
            $dto->firstName,
            $dto->lastName,
            $dto->email,
            '',
            $dto->phone,
            'customer',
            null,
            null
        );
    }

    public static function mapToOldInput(RegistrationRequestDto $dto): array
    {
        return [
            'firstName' => $dto->firstName,
            'lastName' => $dto->lastName,
            'email' => $dto->email,
            'phone' => $dto->phone,
        ];
    }
}