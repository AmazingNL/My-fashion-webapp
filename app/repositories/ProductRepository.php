<?php

namespace app\repositories;

use app\models\Product;
use app\repositories\IProductRepository;
use app\core\RepositoryBase;

class ProductRepository extends RepositoryBase implements IProductRepository {

    public function getAll(): array {
        // Implementation for fetching all products
        $sql = "SELECT * FROM products";
        $result = $this->getConnection()->prepare($sql);
        $products = $result->fetchAll(\PDO::FETCH_CLASS, Product::class);
        return $products;
    }

    public function findById($id): ?Product {
        // Implementation for finding a product by ID
        $sql = "SELECT * FROM products WHERE id = :id";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetchObject(Product::class);      
    }

    public function findByName($name): ?Product {
        // Implementation for finding a product by name
        $sql = "SELECT * FROM products WHERE name = :name";
        $stmt = $this->getConnection()->prepare($sql);      
        $stmt->execute([':name' => $name]);
        return $stmt->fetchObject(Product::class);

    }

    public function findByCategory($category): array {
        // Implementation for finding products by category
        $sql = "SELECT * FROM products WHERE category = :category";
        $stmt = $this->getConnection()->prepare($sql);  
        $stmt->execute([':category' => $category]);
        $products = $stmt->fetchAll(\PDO::FETCH_CLASS, Product::class);
        return $products;
    }

    public function save(Product $product): void {
        // Implementation for saving a new product
        $sql = "INSERT INTO products (name, description, price, category, image, createdAt, updatedAt, isActive)
        VALUES (:name, :description, :price, :category, :image, NOW(), NOW(), :isActive)";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([
            ':name' => $product->getName(),
            ':description' => $product->getDescription(),
            ':price' => $product->getPrice(),
            ':category' => $product->getCategory(),
            ':image' => $product->getImage(),
            ':isActive' => true
        ]);

    }

    public function update(Product $product): void {
        // Implementation for updating an existing product
        $sql = "UPDATE products SET name = :name, description = :description, price = :price, category = :category,
        image = :image, updatedAt = NOW(), isActive = :isActive WHERE id = :id";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([
            ':name' => $product->getName(),
            ':description' => $product->getDescription(),
            ':price' => $product->getPrice(),
            ':category' => $product->getCategory(),
            ':image' => $product->getImage(),
            ':isActive' => true,
            ':id' => $product->getId()
        ]);
    }

    public function delete($id): void {
        // Implementation for deleting a product by ID
        $sql = "DELETE FROM products WHERE id = :id";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([':id' => $id]);
    }
}