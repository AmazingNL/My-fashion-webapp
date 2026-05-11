<?php

declare(strict_types=1);

namespace App\DTO;

use JsonSerializable;

final class UpdateOrderStatusDto implements JsonSerializable
{
    public function __construct(public readonly string $status)
    {
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}