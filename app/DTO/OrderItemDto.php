<?php

declare(strict_types=1);

namespace App\DTO;

use JsonSerializable;

final class OrderItemDto implements JsonSerializable
{
    public function __construct(
        public readonly int $orderItemId,
        public readonly int $orderId,
        public readonly int $productId,
        public readonly int $variantId,
        public readonly int $quantity,
        public readonly float $price,
        public readonly ?string $createdAt = null
    ) {
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}