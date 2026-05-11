<?php

declare(strict_types=1);

namespace App\DTO;

use JsonSerializable;

final class CartItemRequestDto implements JsonSerializable
{
    public function __construct(
        public readonly int $productId,
        public readonly int $variantId,
        public readonly int $quantity = 1
    ) {
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}