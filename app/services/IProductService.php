<?php

namespace App\Services;

use App\Models\Product;

interface IProductService {
    public function getActiveProducts(): array;
    public function getProductDetails($id): array;
    public function getProductById($id): ?Product;
    public function getSimilarProducts(int $productId, string $category, int $limit = 4): array;
    public function updateProduct(Product $product): array;
    public function deleteProduct($id): array;
}
