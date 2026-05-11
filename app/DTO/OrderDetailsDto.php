<?php

declare(strict_types=1);

namespace App\DTO;

use JsonSerializable;

final class OrderDetailsDto implements JsonSerializable
{
    public function __construct(
        public readonly ?OrderDto $order,
        public readonly array $items
    ) {
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}