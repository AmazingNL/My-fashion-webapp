<?php

namespace app\services;

use app\models\Product;

interface IProductService {
    public function getAllProducts(): array;
    public function createProduct(Product $product): bool;
    public function getProductById($id): ?Product;
    public function getProductByName($name): ?Product;
    public function getProductsByCategory($category): array;
    public function updateProduct(Product $product): bool;
    public function deleteProduct($id): bool;
}
