<?php

declare(strict_types=1);

namespace App\DTO;

use JsonSerializable;

final class UserDto implements JsonSerializable
{
    public function __construct(
        public readonly string $userId,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $phone,
        public readonly string $role,
        public readonly string $email,
        public readonly ?string $createdAt

    ) {
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
