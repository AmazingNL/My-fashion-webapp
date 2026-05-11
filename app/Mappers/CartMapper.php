<?php

namespace App\Mappers;

use App\DTO\CartItemDto;
use App\DTO\CartItemRequestDto;
use App\DTO\CartSummaryDto;

class CartMapper
{
    public static function mapToCartItemRequestDto(array $data): CartItemRequestDto
    {
        return new CartItemRequestDto(
            (int) ($data['productId'] ?? 0),
            (int) ($data['variantId'] ?? 0),
            (int) ($data['quantity'] ?? 1)
        );
    }

    public static function mapToCartItemDto(array $item): CartItemDto
    {
        return new CartItemDto(
            (int) ($item['productId'] ?? 0),
            (int) ($item['variantId'] ?? 0),
            (int) ($item['quantity'] ?? 0),
            (string) ($item['name'] ?? ''),
            (string) ($item['description'] ?? ''),
            (string) ($item['category'] ?? ''),
            (string) ($item['size'] ?? ''),
            (string) ($item['color'] ?? ''),
            (string) ($item['image'] ?? ''),
            (float) ($item['price'] ?? 0),
            (int) ($item['stockReal'] ?? 0),
            (int) ($item['stockQuantity'] ?? 0),
            (int) ($item['stockRemaining'] ?? 0),
            (float) ($item['subtotal'] ?? 0),
            (int) ($item['addedAt'] ?? 0)
        );
    }

    public static function mapToCartItemDtos(array $items): array
    {
        return array_map(static fn(array $item): CartItemDto => self::mapToCartItemDto($item), $items);
    }

    public static function mapToCartSummaryDto(array $items, float $total, int $itemCount, bool $isEmpty): CartSummaryDto
    {
        return new CartSummaryDto(self::mapToCartItemDtos($items), $total, $itemCount, $isEmpty);
    }
}