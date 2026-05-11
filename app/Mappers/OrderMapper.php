<?php

namespace App\Mappers;

use App\DTO\OrderDto;
use App\DTO\OrderDetailsDto;

class OrderMapper
{
    public static function mapToOrderDto($order): OrderDto
    {
        return new OrderDto(
            (int) self::value($order, 'orderId', 0),
            (int) self::value($order, 'userId', 0),
            self::enumValue(self::value($order, 'status', '')),
            (float) self::value($order, 'totalAmount', 0),
            (string) self::value($order, 'shippingAddress', ''),
            (string) self::value($order, 'billingAddress', ''),
            (string) self::value($order, 'paymentMethod', ''),
            self::enumValue(self::value($order, 'paymentStatus', '')),
            self::nullableString(self::value($order, 'createdAt')),
            self::nullableString(self::value($order, 'updatedAt'))
        );
    }

    public static function mapToOrderDtos(array $orders): array
    {
        return array_map(static fn($order): OrderDto => self::mapToOrderDto($order), $orders);
    }

    public static function mapToOrderDetailsDto($order, array $items): OrderDetailsDto
    {
        return new OrderDetailsDto(
            $order ? self::mapToOrderDto($order) : null,
            OrderItemMapper::mapToOrderItemDtos($items)
        );
    }

    private static function value($source, string $key, $default = null)
    {
        if (is_array($source)) {
            return $source[$key] ?? $default;
        }

        return is_object($source) ? ($source->{$key} ?? $default) : $default;
    }

    private static function enumValue($value): string
    {
        return is_object($value) && property_exists($value, 'value') ? (string) $value->value : (string) $value;
    }

    private static function nullableString($value): ?string
    {
        return $value === null ? null : (string) $value;
    }
}