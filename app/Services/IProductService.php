<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;

interface IProductService
{
    public function getActiveProducts(): array;
    public function getProductDetails(int $id): array;
    public function getProductById($id): ?Product;
    // Product admin
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

    // Product + variants create (already in your service file)
    public function createProductWithVariants(Product $product, array $variantsInput): array;
    public function toggleFavourite($productId): array;
}
