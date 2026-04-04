<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\ProductVariant;

interface IProductRepository
{
    public function getAllActive(): array;

    public function getProductById(int $id): ?Product;

    // For cart displays: include inactive products so customers can complete purchases
    public function getProductByIdForCart(int $id): ?Product;

    /** Returns ['product' => ?array, 'variants' => array] from a single joined query */
    public function getProductDetailsById(int $id): array;

    /** @return ProductVariant[] */
    public function getVariantsByProductId(int $id): array;

    public function getVariantById(int $id): ?ProductVariant;

    public function save(Product $product): int;
    public function saveVariant(ProductVariant $variant): void;

    public function update(Product $product): bool;
    public function delete($id): bool;

    // (Admin variant management)
    public function updateVariant(ProductVariant $variant): bool;
    public function deleteVariant(int $variantId): bool;

    public function beginTransaction(): void;
    public function commit(): void;
    public function rollBack(): void;
}
