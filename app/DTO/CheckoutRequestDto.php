<?php

declare(strict_types=1);

namespace App\DTO;

use JsonSerializable;

final class CheckoutRequestDto implements JsonSerializable
{
    public function __construct(
        public readonly string $shippingAddress,
        public readonly string $billingAddress,
        public readonly string $paymentMethod,
        public readonly string $returnUrl = ''
    ) {
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}