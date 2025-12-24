<?php

namespace app\services;

use app\models\product;
use app\models\ProductVariant;
use app\repositories\IProductRepository;
use app\services\IProductService;

class ProductService implements IProductService {
    private IProductRepository $productRepository;

    public function __construct(IProductRepository $productRepository) {
        $this->productRepository = $productRepository;
    }

    public function getAllProducts(): array {
        try {
            return $this->productRepository->getAll();
        } catch (\Exception $e) {
            return [];
        }

    }

    public function createProduct(Product $product): bool {
        try {
            $this->productRepository->save($product);
            return true;
        } catch (\Exception $e) {
            return false;
        }   
    }

    public function getProductById($id): ?Product {
        try {
            return $this->productRepository->findById($id);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getProductByName($name): ?Product {
        try {
            return $this->productRepository->findByName($name);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getProductsByCategory($category): array {
        try {
            return $this->productRepository->findByCategory($category);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function updateProduct(Product $product): bool {
        try {
            $this->productRepository->update($product);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function deleteProduct($id): bool {
        try {
            $this->productRepository->findById($id);
        } catch (\Exception $e) {
            return false;
        }   
    }
}