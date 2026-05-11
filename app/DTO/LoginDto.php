<?php

declare(strict_types=1);

namespace App\DTO;

use JsonSerializable;

final class LoginDto implements JsonSerializable
{
    public function __construct(
        public readonly string $email,
        public readonly string $password
    ) {
    }
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
