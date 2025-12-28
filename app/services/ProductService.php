<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Repositories\IProductRepository;
use App\Services\IProductService;

class ProductService implements IProductService
{
    private IProductRepository $productRepository;

    public function __construct(IProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function getAllProducts(): array
    {
        try {
            return $this->productRepository->getAll();
        } catch (\Exception $e) {
            return [];
        }

    }

public function createProduct(Product $product): array
{
    $errors = [];

    if (trim((string)$product->getName()) === '') $errors['error'] = 'Name is required.';
    if ($product->getPrice() <= 0) $errors['error'] = 'Price must be greater than 0.';
    if ($product->getStock() < 0) $errors['error'] = 'Stock cannot be negative.';

    if ($errors) return $errors;

    $this->productRepository->save($product);
    return [];
}

    public function getProductById($id): ?Product
    {
        try {
            return $this->productRepository->findById($id);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getProductByName($name): ?Product
    {
        try {
            return $this->productRepository->findByName($name);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getProductsByCategory($category): array
    {
        try {
            return $this->productRepository->findByCategory($category);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function updateProduct(Product $product): array
    {
        try {
            $this->productRepository->update($product);
            return ['success' => 'Product updated successfully'];
        } catch (\Exception $e) {
            return ['error' => 'Failed to update product'];
        }
    }

    public function deleteProduct($id): array
    {
        try {
            $this->productRepository->findById($id);
            return ['success' => 'Product deleted successfully'];
        } catch (\Exception $e) {
            return ['error' => 'Product not found'];
        }
    }
}