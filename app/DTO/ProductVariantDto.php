<?php

declare(strict_types=1);

namespace App\DTO;

use JsonSerializable;

final class ProductVariantDto implements JsonSerializable
{
    public function __construct(
        public readonly int $variantId,
        public readonly int $productId,
        public readonly string $size,
        public readonly string $colour,
        public readonly int $stockQuantity
    ) {
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}