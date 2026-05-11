<?php

declare(strict_types=1);

namespace App\DTO;

use JsonSerializable;

final class CartSummaryDto implements JsonSerializable
{
    public function __construct(
        public readonly array $items,
        public readonly float $total,
        public readonly int $itemCount,
        public readonly bool $isEmpty
    ) {
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}