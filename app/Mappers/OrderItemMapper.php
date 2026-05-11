<?php

namespace App\Mappers;

use App\DTO\OrderItemDto;

class OrderItemMapper
{
    public static function mapToOrderItemDto($item): OrderItemDto
    {
        return new OrderItemDto(
            (int) self::value($item, 'orderItemId', 0),
            (int) self::value($item, 'orderId', 0),
            (int) self::value($item, 'productId', 0),
            (int) self::value($item, 'variantId', 0),
            (int) self::value($item, 'quantity', 0),
            (float) self::value($item, 'price', 0),
            self::nullableString(self::value($item, 'createdAt'))
        );
    }

    public static function mapToOrderItemDtos(array $items): array
    {
        return array_map(static fn($item): OrderItemDto => self::mapToOrderItemDto($item), $items);
    }

    private static function value($source, string $key, $default = null)
    {
        if (is_array($source)) {
            return $source[$key] ?? $default;
        }

        return is_object($source) ? ($source->{$key} ?? $default) : $default;
    }

    private static function nullableString($value): ?string
    {
        return $value === null ? null : (string) $value;
    }
}