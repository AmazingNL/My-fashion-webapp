<?php

namespace App\Mappers;

use App\DTO\ProductDetailsDto;
use App\DTO\ProductDto;
use App\DTO\ProductListDto;

class ProductResponseMapper
{
    public static function mapToProductDto($product): ProductDto
    {
        return new ProductDto(
            (int) self::value($product, 'productId', 0),
            (string) self::value($product, 'productName', 'Product'),
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

    public static function mapToProductDtos(array $products): array
    {
        return array_map(static fn($product): ProductDto => self::mapToProductDto($product), $products);
    }

    public static function mapToProductDetailsDto($product, array $variants): ProductDetailsDto
    {
        return new ProductDetailsDto(
            $product ? self::mapToProductDto($product) : null,
            ProductVariantMapper::mapToProductVariantDtos($variants)
        );
    }

    public static function mapToProductListDto(array $data): ProductListDto
    {
        $products = (array) ($data['products'] ?? []);
        $pagination = (array) ($data['pagination'] ?? []);
        $filters = (array) ($data['filters'] ?? []);

        $totalCount = (int) ($pagination['totalCount'] ?? $data['totalCount'] ?? 0);
        $currentPage = (int) ($pagination['currentPage'] ?? $data['currentPage'] ?? 1);
        $pageSize = (int) ($pagination['pageSize'] ?? $data['pageSize'] ?? 10);
        $totalPages = (int) ($pagination['totalPages'] ?? $data['totalPages'] ?? 1);
        $startItem = $totalCount === 0 ? 0 : (($currentPage - 1) * $pageSize) + 1;
        $endItem = $totalCount === 0 ? 0 : min($startItem + count($products) - 1, $totalCount);

        return new ProductListDto(
            (string) ($data['title'] ?? 'Products'),
            self::mapToProductDtos($products),
            $totalCount,
            $currentPage,
            $pageSize,
            $totalPages,
            $startItem,
            $endItem,
            $currentPage > 1,
            $currentPage < $totalPages,
            max(1, $currentPage - 1),
            min($totalPages, $currentPage + 1),
            (array) ($filters['filterCategories'] ?? $data['filterCategories'] ?? []),
            (array) ($filters['currentFilters'] ?? $data['currentFilters'] ?? [])
        );
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