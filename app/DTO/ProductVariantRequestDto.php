<?php

declare(strict_types=1);

namespace App\DTO;

use JsonSerializable;

final class ProductVariantRequestDto implements JsonSerializable
{
    public function __construct(
        public readonly int $variantId,
        public readonly string $size,
        public readonly string $colour,
        public readonly int $stock,
        public readonly float $price = 0.0,
        public readonly bool $delete = false
    ) {
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}