<?php

declare(strict_types=1);

namespace App\DTO;

use JsonSerializable;

final class OrderDto implements JsonSerializable
{
    public function __construct(
        public readonly int $orderId,
        public readonly int $userId,
        public readonly string $status,
        public readonly float $totalAmount,
        public readonly string $shippingAddress,
        public readonly string $billingAddress,
        public readonly string $paymentMethod,
        public readonly string $paymentStatus,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null
    ) {
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}