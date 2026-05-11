<?php

declare(strict_types=1);

namespace App\DTO;

use JsonSerializable;

final class CartItemDto implements JsonSerializable
{
    public function __construct(
        public readonly int $productId,
        public readonly int $variantId,
        public readonly int $quantity,
        public readonly string $name,
        public readonly string $description,
        public readonly string $category,
        public readonly string $size,
        public readonly string $color,
        public readonly string $image,
        public readonly float $price,
        public readonly int $stockReal,
        public readonly int $stockQuantity,
        public readonly int $stockRemaining,
        public readonly float $subtotal,
        public readonly int $addedAt
    ) {
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}