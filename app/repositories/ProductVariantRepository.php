<?php

namespace app\repositories;

use app\models\Product;
use app\models\ProductVariant;
use app\repositories\IProductVariantRepository;
use app\core\RepositoryBase;

class ProductVariantRepository extends RepositoryBase implements IProductVariantRepository {
    public function getAll(): array
{
    $sql = "
        SELECT 
            pv.variantId,
            pv.size,
            pv.color,
            pv.stock,

            p.id AS productId,
            p.name,
            p.description,
            p.price
        FROM ProductVariants pv
        LEFT JOIN Products p ON pv.productId = p.id
    ";

    $stmt = $this->connection->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    $productVariants = [];

    foreach ($rows as $row) {
        $product = new Product(
            $row['productId'],
            $row['name'],
            $row['description'],
            $row['price']
        );

        $variant = new ProductVariant(
            $row['variantId'],
            $product,
            $row['size'],
            $row['color'],
            $row['stock']
        );

        $productVariants[] = $variant;
    }

    return $productVariants;
}


    public function findById($id): ?ProductVariant {
        $stmt = $this->connection->prepare("SELECT * FROM product_variants WHERE variant_id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row) {
            $product = new Product($row['product_id'], "", "", 0); // Placeholder, should fetch actual product
            return new ProductVariant(
                $row['variant_id'],
                $product,
                $row['size'],
                $row['color'],
                $row['stock']
            );
        }
        return null;
    }
    public function findByProductId($productId): array {
        $stmt = $this->connection->prepare("SELECT * FROM product_variants WHERE product_id = :productId");
        $stmt->bindParam(':productId', $productId);
        $stmt->execute();
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $productVariants = [];
        foreach ($results as $row) {
            $product = new Product($row['product_id'], "", "", 0); // Placeholder, should fetch actual product
            $productVariant = new ProductVariant(
                $row['variant_id'],
                $product,
                $row['size'],
                $row['color'],
                $row['stock']
            );
            $productVariants[] = $productVariant;
        }
        return $productVariants;
    }
    public function save(ProductVariant $productVariant): void {
        $stmt = $this->connection->prepare("INSERT INTO product_variants (product_id, size, color, stock) 
            VALUES (:productId, :size, :color, :stock)");
        $stmt->bindParam(':productId', $productVariant->getProductId());
        $stmt->bindParam(':size', $productVariant->getSize());
        $stmt->bindParam(':color', $productVariant->getColor());
        $stmt->bindParam(':stock', $productVariant->getStock());
        $stmt->execute();
    }
    public function update(ProductVariant $productVariant): void {
        $stmt = $this->connection->prepare("UPDATE product_variants 
            SET product_id = :productId, size = :size, color = :color, stock = :stock 
            WHERE variant_id = :variantId");
        $stmt->bindParam(':productId', $productVariant->getProductId());
        $stmt->bindParam(':size', $productVariant->getSize());
        $stmt->bindParam(':color', $productVariant->getColor());
        $stmt->bindParam(':stock', $productVariant->getStock());
        $stmt->bindParam(':variantId', $productVariant->getVariantId());
        $stmt->execute();
    }
    public function delete($id): void {
        $stmt = $this->connection->prepare("DELETE FROM product_variants WHERE variant_id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    // Implement methods for product variant repository
}   