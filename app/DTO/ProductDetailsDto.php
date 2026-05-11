<?php

declare(strict_types=1);

namespace App\DTO;

use JsonSerializable;

final class ProductDetailsDto implements JsonSerializable
{
    public function __construct(
        public readonly ?ProductDto $product,
        public readonly array $variants
    ) {
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}