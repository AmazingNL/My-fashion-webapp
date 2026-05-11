<?php

namespace App\Mappers;

use App\Models\Product;

class ProductModelMapper
{
    public static function mapToProduct($product): Product
    {
        return new Product(
            (int) self::value($product, 'productId', 0),
            (string) self::value($product, 'productName', ''),
            (string) self::value($product, 'description', ''),
            (float) self::value($product, 'price', 0),
            (string) self::value($product, 'category', ''),
            (int) self::value($product, 'stock', 0),
            (string) self::value($product, 'image', ''),
            self::nullableString(self::value($product, 'createdAt')),
            self::nullableString(self::value($product, 'updatedAt')),
            (bool) self::value($product, 'isActive', true)
        );
    }

    public static function mapToProducts(array $products): array
    {
        return array_map(static fn($product): Product => self::mapToProduct($product), $products);
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