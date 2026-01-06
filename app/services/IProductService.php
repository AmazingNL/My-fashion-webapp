<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;

interface IProductService
{
    public function getActiveProducts(): array;
    public function getProductDetails($id): array;
    public function getProductById($id): ?Product;
    public function getSimilarProducts(int $productId, string $category, int $limit = 4): array;

    // Product admin
    public function updateProduct(Product $product): array;
    public function deleteProduct($id): array;

    // ✅ Variant admin
    public function getVariantsByProductId(int $productId): array;
    public function addVariantToProduct(ProductVariant $variant): array;
    public function updateVariant(ProductVariant $variant): array;
    public function deleteVariant(int $variantId): array;

    // Product + variants create (already in your service file)
    public function createProductWithVariants(Product $product, array $variantsInput): array;
}
