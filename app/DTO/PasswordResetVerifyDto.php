<?php

declare(strict_types=1);

namespace App\DTO;

use JsonSerializable;

final class PasswordResetVerifyDto implements JsonSerializable
{
    public function __construct(
        public readonly string $token,
        public readonly string $code,
        public readonly string $newPassword,
        public readonly string $confirmPassword
    ) {
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}