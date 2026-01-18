<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\ProductVariant;

interface IProductRepository
{
    public function getAllActive(): array;
    public function findSimilarProducts(int $excludeProductId, string $category, int $limit = 4): array;

    public function getProductById(int $id): ?Product;

    /** @return ProductVariant[] */
    public function getVariantsByProductId(int $id): array;

    public function getVariantById(int $id): ?ProductVariant;

    public function save(Product $product): int;
    public function saveVariant(ProductVariant $variant): void;

    public function update(Product $product): void;
    public function delete($id): void;

    // (Admin variant management)
    public function updateVariant(ProductVariant $variant): void;
    public function deleteVariant(int $variantId): void;

    public function beginTransaction(): void;
    public function commit(): void;
    public function rollBack(): void;
}
