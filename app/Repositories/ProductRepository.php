<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Repositories\IProductRepository;
use App\Core\RepositoryBase;
use Exception;
use RuntimeException;

class ProductRepository extends RepositoryBase implements IProductRepository
{

    /** @return Product[] */
    public function getAllActive(): array
    {
        try {
            $sql = "SELECT * FROM products WHERE isActive = 1 ORDER BY createdAt DESC";
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            return $rows;
        } catch (Exception $e) {
            throw new RuntimeException("DB error " . $e);
        }
    }

    /** @return ProductVariant[] */
    public function getVariantsByProductId(int $id): array
    {
        try {
            $sql = "SELECT * FROM product_variants WHERE productId = :pid ORDER BY size, colour";
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([':pid' => $id]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            return array_map(function (array $row): ProductVariant {
                return new ProductVariant(
                    (int) ($row['variantId'] ?? 0),
                    (int) ($row['productId'] ?? 0),
                    (string) ($row['size'] ?? ''),
                    (string) ($row['colour'] ?? ''),
                    (int) ($row['stockQuantity'] ?? 0)
                );
            }, $rows);
        } catch (Exception $e) {
            throw new RuntimeException("DB error" . $e);
        }

    }


    public function getVariantById(int $variantId): ?ProductVariant
    {
        try {
            $sql = "SELECT * FROM product_variants WHERE variantId = :variantId";
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([':variantId' => $variantId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$row) {
                return null;
            }

            return new ProductVariant(
                (int) ($row['variantId'] ?? 0),
                (int) ($row['productId'] ?? 0),
                (string) ($row['size'] ?? ''),
                (string) ($row['colour'] ?? ''),
                (int) ($row['stockQuantity'] ?? 0)
            );
        } catch (Exception $e) {
            throw new RuntimeException("DB error" . $e);
        }

    }

    public function getProductById(int $id): ?Product
    {
        try {
            $sql = "SELECT * FROM products 
            WHERE productId = :id 
            AND isActive = 1";
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$row) {
                return null;
            }

            return new Product(
                (int) ($row['productId'] ?? 0),
                (string) ($row['productName'] ?? ''),
                (string) ($row['description'] ?? ''),
                (float) ($row['price'] ?? 0),
                (string) ($row['category'] ?? ''),
                (int) ($row['stock'] ?? 0),
                (string) ($row['image'] ?? ''),
                $row['createdAt'] ?? null,
                $row['updatedAt'] ?? null,
                (bool) ($row['isActive'] ?? false)
            );
        } catch (Exception $e) {
            throw new RuntimeException("DB error" . $e);
        }

    }

    // For cart displays: include inactive products so customers can complete purchases
    public function getProductByIdForCart(int $id): ?Product
    {
        try {
            $sql = "SELECT * FROM products WHERE productId = :id";
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$row) {
                return null;
            }

            return new Product(
                (int) ($row['productId'] ?? 0),
                (string) ($row['productName'] ?? ''),
                (string) ($row['description'] ?? ''),
                (float) ($row['price'] ?? 0),
                (string) ($row['category'] ?? ''),
                (int) ($row['stock'] ?? 0),
                (string) ($row['image'] ?? ''),
                $row['createdAt'] ?? null,
                $row['updatedAt'] ?? null,
                (bool) ($row['isActive'] ?? false)
            );
        } catch (Exception $e) {
            throw new RuntimeException("DB error" . $e);
        }

    }

    public function getProductDetailsById(int $id): array
    {
        try {
            $sql = "SELECT
                        p.productId,
                        p.productName,
                        p.description,
                        p.price,
                        p.category,
                        p.stock,
                        p.image,
                        p.createdAt,
                        p.updatedAt,
                        p.isActive,
                        pv.variantId,
                        pv.size,
                        pv.colour,
                        pv.stockQuantity
                    FROM products p
                    LEFT JOIN product_variants pv ON pv.productId = p.productId
                    WHERE p.productId = :id AND p.isActive = 1
                    ORDER BY pv.size, pv.colour";

            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([':id' => $id]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            if (empty($rows)) {
                return ['product' => null, 'variants' => []];
            }

            return $this->mapJoinedProductDetails($rows);
        } catch (Exception $e) {
            throw new RuntimeException('DB error ' . $e->getMessage());
        }
    }


    public function save(Product $product): int
    {
        try {
            $sql = "INSERT INTO products
                (productName, description, price, category, stock, image, createdAt, updatedAt, isActive)
                VALUES 
                (:productName, :description, :price, :category, :stock, :image, NOW(), NOW(), :isActive)";

            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([
                ':productName' => $product->productName,
                ':description' => $product->description,
                ':price' => $product->price,
                ':category' => $product->category,
                ':stock' => $product->stock,
                ':image' => $product->image,
                ':isActive' => $product->isActive
            ]);
            return (int) $this->getConnection()->lastInsertId();
        } catch (Exception $e) {
            throw new RuntimeException("DB error" . $e);
        }
    }

    public function saveVariant(ProductVariant $variant): void
    {
        try {
            $sql = "INSERT INTO product_variants
                (productId, size, colour, stockQuantity)
                VALUES
                (:productId, :size, :colour, :stockQuantity)";

            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([
                ':productId' => $variant->productId,
                ':size' => $variant->size,
                ':colour' => $variant->colour,
                ':stockQuantity' => $variant->stockQuantity
            ]);

        } catch (Exception $e) {
            throw new RuntimeException("DB error" . $e);
        }

    }

    public function update(Product $product): bool
    {
        try {
            $sql = "UPDATE products 
            SET productName = :productName,
                description = :description,
                price = :price,
                category = :category,
                stock = :stock,
                image = :image,
                updatedAt = NOW(),
                isActive = :isActive
            WHERE productId = :productId";
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([
                ':productName' => $product->productName,
                ':description' => $product->description,
                ':price' => $product->price,
                ':category' => $product->category,
                ':stock' => $product->stock,
                ':image' => $product->image,
                ':isActive' => 1,
                ':productId' => (int) $product->productId,
            ]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            throw new RuntimeException("DB error" . $e);
        }

    }

    public function delete($id): bool
    {
        try {
            $sql = "UPDATE products SET isActive = 0, updatedAt = NOW() WHERE productId = :id";
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([':id' => (int) $id]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            throw new RuntimeException("DB error" . $e);
        }

    }

    public function updateVariant(ProductVariant $variant): bool
    {
        try {
            $sql = "UPDATE product_variants
            SET size = :size,
                colour = :colour,
                stockQuantity = :stockQuantity
            WHERE variantId = :variantId";

            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([
                ':size' => $variant->size,
                ':colour' => $variant->colour,
                ':stockQuantity' => $variant->stockQuantity,
                ':variantId' => (int) $variant->variantId,
            ]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            throw new RuntimeException("DB error" . $e);
        }
    }

    public function deleteVariant(int $variantId): bool
    {
        try {
            $sql = "DELETE FROM product_variants WHERE variantId = :variantId";
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([':variantId' => $variantId]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            throw new RuntimeException("DB error " . $e);
        }
    }


    private function mapJoinedProductDetails(array $rows): array
    {
        $first = $rows[0];
        $product = [
            'productId' => (int) $first['productId'],
            'productName' => (string) $first['productName'],
            'description' => (string) ($first['description'] ?? ''),
            'price' => (float) $first['price'],
            'category' => (string) ($first['category'] ?? ''),
            'stock' => (int) ($first['stock'] ?? 0),
            'image' => (string) ($first['image'] ?? ''),
            'createdAt' => $first['createdAt'] ?? null,
            'updatedAt' => $first['updatedAt'] ?? null,
            'isActive' => (bool) ($first['isActive'] ?? false),
        ];
        $variants = [];
        foreach ($rows as $row) {
            if ($row['variantId'] === null) {
                continue;
            }
            $variants[] = [
                'variantId' => (int) $row['variantId'],
                'productId' => (int) $row['productId'],
                'size' => (string) ($row['size'] ?? ''),
                'colour' => (string) ($row['colour'] ?? ''),
                'stockQuantity' => (int) ($row['stockQuantity'] ?? 0),
            ];
        }
        return ['product' => $product, 'variants' => $variants];
    }
}