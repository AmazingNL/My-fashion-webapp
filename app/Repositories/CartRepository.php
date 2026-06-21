<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Interfaces\ICartRepository;

use App\Core\RepositoryBase;
use PDO;
use PDOException;
use RuntimeException;

class CartRepository extends RepositoryBase implements ICartRepository
{
    public function findByUserId(int $userId): array
    {
        try {
            $sql = 'SELECT * FROM cart_items WHERE userId = :userId ORDER BY createdAt ASC, cartItemId ASC';
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([':userId' => $userId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            throw new RuntimeException('Failed to load cart items: ' . $e->getMessage());
        }
    }

    public function findItem(int $userId, int $productId, int $variantId): ?array
    {
        try {
            $sql = 'SELECT * FROM cart_items
                    WHERE userId = :userId AND productId = :productId AND variantId = :variantId
                    LIMIT 1';
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([
                ':userId' => $userId,
                ':productId' => $productId,
                ':variantId' => $variantId,
            ]);

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (PDOException $e) {
            throw new RuntimeException('Failed to load cart item: ' . $e->getMessage());
        }
    }

    public function saveItem(int $userId, int $productId, int $variantId, int $quantity): void
    {
        try {
            $existing = $this->findItem($userId, $productId, $variantId);

            if ($existing !== null) {
                $sql = 'UPDATE cart_items SET quantity = :quantity WHERE cartItemId = :cartItemId LIMIT 1';
                $stmt = $this->getConnection()->prepare($sql);
                $stmt->execute([
                    ':quantity' => $quantity,
                    ':cartItemId' => (int) $existing['cartItemId'],
                ]);
                return;
            }

            $sql = 'INSERT INTO cart_items (userId, productId, variantId, quantity)
                    VALUES (:userId, :productId, :variantId, :quantity)';
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([
                ':userId' => $userId,
                ':productId' => $productId,
                ':variantId' => $variantId,
                ':quantity' => $quantity,
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException('Failed to save cart item: ' . $e->getMessage());
        }
    }

    public function removeItem(int $userId, int $productId, int $variantId): bool
    {
        try {
            $sql = 'DELETE FROM cart_items
                    WHERE userId = :userId AND productId = :productId AND variantId = :variantId
                    LIMIT 1';
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([
                ':userId' => $userId,
                ':productId' => $productId,
                ':variantId' => $variantId,
            ]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException('Failed to remove cart item: ' . $e->getMessage());
        }
    }

    public function clearByUserId(int $userId): void
    {
        try {
            $sql = 'DELETE FROM cart_items WHERE userId = :userId';
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([':userId' => $userId]);
        } catch (PDOException $e) {
            throw new RuntimeException('Failed to clear cart: ' . $e->getMessage());
        }
    }
}