<?php

namespace App\Services;

use App\Models\Product;

interface IProductService {
    public function getAllProducts(): array;
    public function createProduct(Product $product): array;
    public function getProductById($id): ?Product;
    public function getProductByName($name): ?Product;
    public function getProductsByCategory($category): array;
    public function updateProduct(Product $product): array;
    public function deleteProduct($id): array;
}
