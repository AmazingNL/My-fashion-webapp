<?php

namespace App\Mappers;

use App\DTO\ProductRequestDto;
use App\DTO\ProductVariantRequestDto;
use App\Models\Product;

class ProductRequestMapper
{
    public static function mapToProductRequestDto(array $data, ?string $imagePath = null): ProductRequestDto
    {
        return new ProductRequestDto(
            isset($data['productId']) ? (int) $data['productId'] : null,
            trim((string) (($data['productName'] ?? $data['name']) ?? '')),
            trim((string) ($data['description'] ?? '')),
            (float) ($data['price'] ?? 0),
            trim((string) ($data['category'] ?? '')),
            (int) ($data['stock'] ?? 0),
            $imagePath,
            self::mapVariantRows($data['variants'] ?? [])
        );
    }

    public static function mapToVariantRequestDto(array $data): ProductVariantRequestDto
    {
        return new ProductVariantRequestDto(
            (int) ($data['variantId'] ?? 0),
            trim((string) ($data['size'] ?? '')),
            trim((string) (($data['colour'] ?? $data['color']) ?? '')),
            (int) (($data['stock'] ?? $data['stockQuantity']) ?? 0),
            (float) ($data['price'] ?? 0),
            (bool) ($data['delete'] ?? false)
        );
    }

    public static function mapToVariantChangeDtos(array $data): array
    {
        $ids = self::asArray($data['variantId'] ?? []);
        $sizes = self::asArray($data['variantSize'] ?? []);
        $colours = self::asArray($data['variantColour'] ?? []);
        $stocks = self::asArray($data['variantStock'] ?? []);
        $prices = self::asArray($data['variantPrice'] ?? []);
        $deleteIds = array_map('intval', self::asArray($data['variantDeleteIds'] ?? []));

        $count = max(count($ids), count($sizes), count($colours), count($stocks), count($prices));

        $variants = [];
        for ($i = 0; $i < $count; $i++) {
            $variantId = (int) ($ids[$i] ?? 0);
            $variants[] = self::mapToVariantRequestDto([
                'variantId' => $variantId,
                'delete' => in_array($variantId, $deleteIds, true),
                'size' => $sizes[$i] ?? '',
                'colour' => $colours[$i] ?? '',
                'stock' => $stocks[$i] ?? 0,
                'price' => $prices[$i] ?? 0,
            ]);
        }

        return $variants;
    }

    public static function mapToVariantInputRows(array $variants): array
    {
        return array_map(static fn(ProductVariantRequestDto $variant): array => [
            'size' => $variant->size,
            'colour' => $variant->colour,
            'stockQuantity' => $variant->stock,
        ], $variants);
    }

    public static function mapToProduct(ProductRequestDto $dto, ?int $productId = null): Product
    {
        return new Product(
            $productId ?? $dto->productId,
            $dto->productName,
            $dto->description,
            $dto->price,
            $dto->category,
            $dto->stock,
            (string) $dto->image,
            null,
            null,
            true
        );
    }

    public static function mapVariantRows(array $variants): array
    {
        return array_map(static fn($variant): ProductVariantRequestDto => self::mapToVariantRequestDto((array) $variant), $variants);
    }

    private static function asArray($value): array
    {
        return is_array($value) ? $value : [];
    }
}