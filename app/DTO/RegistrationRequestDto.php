<?php

declare(strict_types=1);

namespace App\DTO;

use JsonSerializable;

final class RegistrationRequestDto implements JsonSerializable
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly string $phone,
        public readonly string $password
    ) {
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}