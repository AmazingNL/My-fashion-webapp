<?php

namespace App\Repositories;
use App\Models\Product;

interface IProductRepository {

    public function getAll(): array;
    public function getAllActive(): array;
    public function findById($id): ?Product;
    public function findByName($name): ?Product;
    public function findByCategory($category): array;
    public function save(Product $product): void;
    public function update(Product $product): void;
    public function delete($id): void;

    // Define methods for product repository
}