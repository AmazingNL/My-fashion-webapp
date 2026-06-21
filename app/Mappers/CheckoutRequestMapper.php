<?php

namespace App\Mappers;

use App\DTO\CheckoutRequestDto;

class CheckoutRequestMapper
{
    public static function mapToCheckoutRequestDto(array $data): CheckoutRequestDto
    {
        $shipping = trim((string) ($data['shippingAddress'] ?? ''));
        $billing = trim((string) ($data['billingAddress'] ?? '')) ?: $shipping;

        return new CheckoutRequestDto(
            $shipping,
            $billing,
            trim((string) ($data['paymentMethod'] ?? 'stripe')),
            trim((string) ($data['returnUrl'] ?? ''))
        );
    }
}