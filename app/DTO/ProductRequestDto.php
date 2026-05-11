<?php

declare(strict_types=1);

namespace App\DTO;

use JsonSerializable;

final class ProductRequestDto implements JsonSerializable
{
    public function __construct(
        public readonly ?int $productId,
        public readonly string $productName,
        public readonly string $description,
        public readonly float $price,
        public readonly string $category,
        public readonly int $stock,
        public readonly ?string $image,
        public readonly array $variants = []
    ) {
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}