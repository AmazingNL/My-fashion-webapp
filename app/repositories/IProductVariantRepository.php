<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\ProductVariant;

interface IProductVariantRepository {
    public function getAll(): array;
    public function findById($id): ?ProductVariant;
    public function findByProductId($productId): array;
    public function save(ProductVariant $productVariant): void;
    public function update(ProductVariant $productVariant): void;
    public function delete($id): void;

    // Define methods for product variant repository
}
