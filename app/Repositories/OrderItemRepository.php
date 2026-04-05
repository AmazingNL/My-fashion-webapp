<?php

namespace App\Repositories;

use App\Core\RepositoryBase;
use App\Models\OrderItem;
use PDO;
use Throwable;

class OrderItemRepository extends RepositoryBase implements IOrderItemRepository
{
    private const TABLE = 'order_items';
    private const ORDER_ITEM_DEFAULTS = [0, 0, 0, 0, 0, 0.0, null];

    public function getAll(): array
    {
        try {
            $stmt = $this->getConnection()->prepare("SELECT * FROM " . self::TABLE);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            throw $this->dbError($e);
        }
    }


    public function findById(int $id): ?OrderItem
    {
        try {
            $stmt = $this->getConnection()->prepare(
                "SELECT * FROM " . self::TABLE . " WHERE orderItemId = :id"
            );
            $stmt->execute([':id' => $id]);
            $item = $stmt->fetchObject(OrderItem::class, self::ORDER_ITEM_DEFAULTS);
            return $item instanceof OrderItem ? $item : null;
        } catch (Throwable $e) {
            throw $this->dbError($e);
        }
    }


    public function findByOrderId(int $orderId): array
    {
        try {
            $sql = "
                SELECT
                    oi.orderItemId,
                    oi.orderId,
                    oi.productId,
                    oi.variantId,
                    oi.quantity,
                    oi.price,
                    oi.createdAt
                FROM order_items oi
                WHERE oi.orderId = :orderId
                ORDER BY oi.orderItemId DESC
            ";

            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([':orderId' => $orderId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            throw $this->dbError($e);
        }
    }


    public function save(OrderItem $orderItem): void
    {
        try {
            $sql = "INSERT INTO " . self::TABLE . " (orderId, productId, variantId, quantity, price)
                    VALUES (:orderId, :productId, :variantId, :quantity, :price)";

            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([
                ':orderId' => (int) $orderItem->orderId,
                ':productId' => (int) $orderItem->productId,
                ':variantId' => (int) $orderItem->variantId,
                ':quantity' => (int) $orderItem->quantity,
                ':price' => (float) $orderItem->price,
            ]);
        } catch (Throwable $e) {
            throw $this->dbError($e);
        }
    }


    private function dbError(Throwable $e): \RuntimeException
    {
        return new \RuntimeException('DB error ' . $e->getMessage(), 0, $e);
    }
}
