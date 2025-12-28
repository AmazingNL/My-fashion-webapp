<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\IProductRepository;
use App\Core\RepositoryBase;

class ProductRepository extends RepositoryBase implements IProductRepository
{

    public function getAll(): array
    {
        // Implementation for fetching all products
        $sql = "SELECT * FROM Products";
        $result = $this->getConnection()->prepare($sql);
        $products = $result->fetchAll(\PDO::FETCH_CLASS, Product::class);
        return $products;
    }

    public function getAllActive(): array
    {
        $sql = "SELECT * FROM Products WHERE isActive = 1 ORDER BY createdAt DESC";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();

        $product = $stmt->fetchAll(PDO::FETCH_CLASS, Product::class) ?: [];
        return array_map(fn($r) => $this->mapProduct($r), $product);
    }

    public function findById($id): ?Product
    {
        // Implementation for finding a product by ID
        $sql = "SELECT * FROM Products WHERE id = :id";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetchObject(Product::class);
    }

    public function findByName($name): ?Product
    {
        // Implementation for finding a product by name
        $sql = "SELECT * FROM Products WHERE name = :name";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([':name' => $name]);
        return $stmt->fetchObject(Product::class);

    }

    public function findByCategory($category): array
    {
        // Implementation for finding products by category
        $sql = "SELECT * FROM Products WHERE category = :category";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([':category' => $category]);
        $products = $stmt->fetchAll(\PDO::FETCH_CLASS, Product::class);
        return $products;
    }

    public function save(Product $product): void
    {
        // Implementation for saving a new product
        $sql = "INSERT INTO Products (productName, description, price, category, stock, image, createdAt, updatedAt, isActive)
        VALUES (:productName, :description, :price, :category, :stock, :image, NOW(), NOW(), :isActive)";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([
            ':productName' => $product->getName(),
            ':description' => $product->getDescription(),
            ':price' => $product->getPrice(),
            ':category' => $product->getCategory(),
            ':image' => $product->getImage(),
            ':stock' => $product->getStock(),
            ':isActive' => true
        ]);

    }

    public function update(Product $product): void
    {
        // Implementation for updating an existing product
        $sql = "UPDATE Products SET productName = :productName, description = :description, price = :price, category = :category,
        stock = :stock, image = :image, updatedAt = NOW(), isActive = :isActive WHERE id = :id";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([
            ':productName' => $product->getName(),
            ':description' => $product->getDescription(),
            ':price' => $product->getPrice(),
            ':category' => $product->getCategory(),
            ':image' => $product->getImage(),
            ':stock' => $product->getStock(),
            ':isActive' => true,
            ':updatedAt' => date('Y-m-d H:i:s'),
            ':id' => $product->getId()
        ]);
    }

    public function delete($id): void
    {
        // Implementation for deleting a product by ID
        $sql = "DELETE FROM Products WHERE id = :id";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([':id' => $id]);
    }
}