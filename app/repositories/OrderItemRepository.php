<?php

namespace App\Repositories;

use App\Core\RepositoryBase;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\ProductVariant;
use DateTime;
use PDO;

class OrderItemRepository extends RepositoryBase implements IOrderItemRepository
{
    private const TABLE = 'order_items';

public function getAll(): array
{
    $orderItems = [];

    $stmt = $this->getConnection()->prepare("SELECT * FROM " . self::TABLE);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    foreach ($results as $row) {
        $orderItems[] = new OrderItem(
            (int)$row['orderItemId'],
            (int)$row['productId'],
            (int)$row['orderId'],
            (int)$row['variantId'],
            (int)$row['quantity'],
            (float)$row['price'],
            $row['createdAt']
        );
    }
    return $orderItems;
}


    public function findById($id): ?OrderItem
    {
        $stmt = $this->getConnection()->prepare(
            "SELECT * FROM " . self::TABLE . " WHERE orderItemId = :id"
        );
        $stmt->execute([':id' => (int) $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new OrderItem(
            (int) $row['orderItemId'],
            (int) $row['orderId'],
            (int) $row['productId'],
            (int) $row['variantId'],
            (int) $row['quantity'],
            (float) $row['price'],
            $row['createdAt']
        );
    }


public function findByOrderId($orderId): array
{
    $orderItems = [];

    // NOTE: adjust column names if your DB uses "name" instead of "productName"
    $sql = "
        SELECT 
            oi.orderItemId, oi.orderId, oi.productId, oi.variantId, oi.quantity, oi.price, oi.createdAt,
            p.productName AS productName,
            p.category AS productCategory,
            p.image       AS productImage,
            v.size        AS variantSize,
            v.colour       AS variantColour
        FROM order_items oi
        INNER JOIN products p ON p.productId = oi.productId
        LEFT JOIN product_variants v ON v.variantId = oi.variantId
        WHERE oi.orderId = :orderId
        ORDER BY oi.orderItemId DESC
    ";

    $stmt = $this->getConnection()->prepare($sql);
    $stmt->execute([':orderId' => (int)$orderId]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    foreach ($results as $row) {
        $item = new OrderItem(
            isset($row['orderItemId']) ? (int)$row['orderItemId'] : null,
            (int)$row['orderId'],
            (int)$row['productId'],
            (int)$row['variantId'],
            (int)$row['quantity'],
            (float)$row['price'],
            $row['createdAt'] ?? null
        );

        // attach joined info (optional)
        $item->setProductName($row['productName'] ?? null);
        $item->setProductImage($row['productImage'] ?? null);
        $item->setVariantSize($row['variantSize'] ?? null);
        $item->setVariantColor($row['variantColour'] ?? null);
        $item->setProductCategory($row['productCategory'] ?? null);

        $orderItems[] = $item;
    }
    return $orderItems;
}


    public function save(OrderItem $orderItem): void
    {
        $sql = "INSERT INTO " . self::TABLE . " (orderId, productId, variantId, quantity, price, createdAt)
                VALUES (:orderId, :productId, :variantId, :quantity, :price, :createdAt)";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([
            ':orderId' => (int) $orderItem->getOrderId(),
            ':productId' => (int) $orderItem->getProductId(),
            ':variantId' => (int) $orderItem->getVariantId(),
            ':quantity' => (int) $orderItem->getQuantity(),
            ':price' => (float) $orderItem->getPrice(),
            ':createdAt' => $orderItem->getCreatedAt(),
        ]);
    }

    public function update(OrderItem $orderItem): void
    {
        $sql = "UPDATE " . self::TABLE . "
                SET orderId = :orderId,
                    productId = :productId,
                    variantId = :variantId,
                    quantity = :quantity,
                    price = :price,
                    createdAt = :createdAt
                WHERE orderItemId = :orderItemId";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([
            ':orderId' => (int) $orderItem->getOrderId(),
            'productId'=> (int) $orderItem->getProductId(),
            ':variantId' => (int) $orderItem->getVariantId(),
            ':quantity' => (int) $orderItem->getQuantity(),
            ':price' => (float) $orderItem->getPrice(),
            'createdAt'=> $orderItem->getCreatedAt(),
            ':orderItemId' => (int) $orderItem->getOrderItemId(),
        ]);
    }

    public function delete($id): void
    {
        $stmt = $this->getConnection()->prepare(
            "DELETE FROM " . self::TABLE . " WHERE orderItemId = :id"
        );
        $stmt->execute([':id' => (int) $id]);
    }
}
