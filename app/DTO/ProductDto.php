<?php

declare(strict_types=1);

namespace App\DTO;

use JsonSerializable;

final class ProductDto implements JsonSerializable
{
    public function __construct(
        public readonly int $productId,
        public readonly string $productName,
        public readonly string $description,
        public readonly float $price,
        public readonly string $category,
        public readonly int $stock,
        public readonly string $image,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
        public readonly bool $isActive = true
    ) {
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}