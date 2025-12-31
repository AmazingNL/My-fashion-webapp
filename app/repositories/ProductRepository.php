<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Repositories\IProductRepository;
use App\Core\RepositoryBase;

class ProductRepository extends RepositoryBase implements IProductRepository
{

    /** @return Product[] */
    public function getAllActive(): array
    {
        $sql = "SELECT * FROM Products WHERE isActive = 1 ORDER BY createdAt DESC";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        return array_map(fn($r) => $this->mapProduct($r), $rows);
    }

    public function findSimilarProducts(int $excludeProductId, string $category, int $limit = 4): array
    {
        $sql = "SELECT * FROM Products 
            WHERE category = :category 
            AND productId != :excludeId
            AND isActive = 1
            AND stock > 0
            ORDER BY RAND()
            LIMIT :limit";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':category', $category, \PDO::PARAM_STR);
        $stmt->bindValue(':excludeId', $excludeProductId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(fn($r) => $this->mapProduct($r), $rows);
    }

    /** @return ProductVariant[] */
    public function getVariantsByProductId(int $productId): array
    {
        $sql = "SELECT * FROM ProductVariants WHERE productId = :pid ORDER BY size, colour";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([':pid' => $productId]);

        $productVariants = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        return array_map(fn($r) => $this->mapProductVariant($r), $productVariants);
    }


    public function getVariantById(int $variantId): ?ProductVariant
    {
        $sql = "SELECT * FROM ProductVariants WHERE variantId = :variantId";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([':variantId' => $variantId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }
        return $this->mapProductVariant($row);
    }

    public function getProductById(int $id): ?Product
    {
        $sql = "SELECT * FROM Products 
            WHERE productId = :id 
            AND isActive = 1";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }
        return $this->mapProduct($row);
    }


    public function save(Product $product): int
    {
        $sql = "INSERT INTO Products
                (productName, description, price, category, stock, image, createdAt, updatedAt, isActive)
                VALUES (:productName, :description, :price, :category, :stock, :image, NOW(), NOW(), :isActive)";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([
            ':productName' => $product->getName(),
            ':description' => $product->getDescription(),
            ':price' => $product->getPrice(),
            ':category' => $product->getCategory(),
            ':stock' => $product->getStock(),
            ':image' => $product->getImage(),
            ':isActive' => 1

        ]);

        return (int) $this->getConnection()->lastInsertId();
    }

    public function saveVariant(ProductVariant $variant): void
    {
        $sql = "INSERT INTO ProductVariants
                (productId, size, colour, stockQuantity)
                VALUES (:productId, :size, :colour, :stockQuantity)";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([
            ':productId' => $variant->getProductId(),
            ':size' => $variant->getSize(),
            ':colour' => $variant->getColour(),
            ':stockQuantity' => $variant->getStock()
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


    ///// Private helper to map database row to Product model

    private function mapProduct(array $r): Product
    {
        return new Product(
            (int) $r['productId'],
            (string) $r['productName'],
            (string) $r['description'],
            (float) $r['price'],
            (string) $r['category'],
            (int) $r['stock'],
            (string) $r['image'],
            $r['createdAt'] ?? null,
            $r['updatedAt'] ?? null,
            (bool) $r['isActive']
        );
    }

    private function mapProductVariant(array $r): ProductVariant
    {
        return new ProductVariant(
            (int) $r['variantId'],
            (int) $r['productId'],
            (string) $r['size'],
            (string) $r['colour'],
            (int) $r['stockQuantity']
        );
    }

}