<?php

namespace App\Services;

use App\DTO\ProductRequestDto;
use App\Models\Product;
use App\Models\ProductVariant;

interface IProductService
{
    public function getActiveProducts(): array;
    public function getProductDetails(int $id): array;
    public function getProductListData(array $query): array;
    public function getEmptyProductListData(): array;
    public function getProductById($id): ?Product;
    // Product admin
    public function saveUploadedProductImage(array $file): array;
    public function resolveProductImagePath($existing, array $file): ?string;
    public function validateProductRequest(ProductRequestDto $dto): array;
    public function updateProduct(Product $product): array;
    public function deleteProduct($id): array;

    // Variant admin
    public function getVariantsByProductId(int $productId): array;
    public function addVariantToProduct(ProductVariant $variant): array;
    public function updateVariantByFields(
        int $variantId,
        string $size,
        string $colour,
        int $stockQuantity,
        float $price
    ): array;

    public function createVariantByFields(
        int $productId,
        string $size,
        string $colour,
        int $stockQuantity,
        float $price
    ): array;
    public function deleteVariant(int $variantId): array;
    public function applyVariantChanges(int $productId, array $variantRows): void;

    public function createProductWithVariants(Product $product, array $variantsInput): array;
    public function toggleFavourite($productId): array;
}
