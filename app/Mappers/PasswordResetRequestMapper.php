<?php

namespace App\Mappers;

use App\DTO\PasswordResetRequestDto;
use App\DTO\PasswordResetVerifyDto;

class PasswordResetRequestMapper
{
    public static function mapToPasswordResetRequestDto(array $data): PasswordResetRequestDto
    {
        return new PasswordResetRequestDto(strtolower(trim((string) ($data['email'] ?? ''))));
    }

    public static function mapToPasswordResetVerifyDto(array $data): PasswordResetVerifyDto
    {
        return new PasswordResetVerifyDto(
            (string) ($data['token'] ?? ''),
            trim((string) ($data['code'] ?? '')),
            (string) ($data['newPassword'] ?? ''),
            (string) ($data['confirmPassword'] ?? '')
        );
    }
}