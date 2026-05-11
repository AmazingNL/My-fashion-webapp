<?php

namespace App\Mappers;

use App\DTO\ProductVariantDto;
use App\Models\ProductVariant;

class ProductVariantMapper
{
    public static function mapToProductVariant($variant): ProductVariant
    {
        return new ProductVariant(
            (int) self::value($variant, 'variantId', 0),
            (int) self::value($variant, 'productId', 0),
            (string) self::value($variant, 'size', ''),
            (string) self::value($variant, 'colour', self::value($variant, 'color', '')),
            (int) self::value($variant, 'stockQuantity', self::value($variant, 'stock', 0))
        );
    }

    public static function mapToProductVariants(array $variants): array
    {
        return array_map(static fn($variant): ProductVariant => self::mapToProductVariant($variant), $variants);
    }

    public static function mapToProductVariantDto($variant): ProductVariantDto
    {
        return new ProductVariantDto(
            (int) self::value($variant, 'variantId', 0),
            (int) self::value($variant, 'productId', 0),
            (string) self::value($variant, 'size', ''),
            (string) self::value($variant, 'colour', self::value($variant, 'color', '')),
            (int) self::value($variant, 'stockQuantity', self::value($variant, 'stock', 0))
        );
    }

    public static function mapToProductVariantDtos(array $variants): array
    {
        return array_map(static fn($variant): ProductVariantDto => self::mapToProductVariantDto($variant), $variants);
    }

    private static function value($source, string $key)
    {
        if (is_array($source)) {
            return $source[$key] ?? null;
        }

        return is_object($source) ? ($source->{$key} ?? null) : null;
    }
}