<?php

namespace app\repositories;

use app\models\OrderItem;
use app\models\Order;
use app\models\ProductVariant;
use app\repositories\IOrderItemRepository;
use app\core\RepositoryBase;

class OrderItemRepository extends RepositoryBase implements IOrderItemRepository {

    public function getAll(): array {
        $orderItems = [];
        $results = $this->db->query("SELECT * FROM OrderItems");
        foreach ($results as $row) {
            $order = new Order($row['orderId'], null, null, null, null, null, null);
            $productVariant = new ProductVariant($row['productVariantId'], null, null, null, null);
            $orderItem = new OrderItem($row['orderItemId'], $order, $productVariant, $row['quantity'], $row['price']);
            $orderItems[] = $orderItem;
        }
        return $orderItems;
    }   
    public function findById($id): ?OrderItem {
        $stmt = $this->connection->prepare("SELECT * FROM OrderItems WHERE orderItemId = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row) {
            $order = new Order($row['orderId'], null, null, null, null, null, null);
            $productVariant = new ProductVariant($row['productVariantId'], null, null, null, null);
            return new OrderItem($row['orderItemId'], $order, $productVariant, $row['quantity'], $row['price']);
        }
        return null;
    }
    public function findByOrderId($orderId): array {
        $orderItems = [];
        $stmt = $this->connection->prepare("SELECT * FROM OrderItems WHERE orderId = :orderId");
        $stmt->execute([':orderId' => $orderId]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($results as $row) {
            $order = new Order($row['orderId'], null, null, null, null, null, null);
            $productVariant = new ProductVariant($row['productVariantId'], null, null, null, null);
            $orderItem = new OrderItem($row['orderItemId'], $order, $productVariant, $row['quantity'], $row['price']);
            $orderItems[] = $orderItem;
        }
        return $orderItems;
    }
    public function save(OrderItem $orderItem): void {
        $sql = "INSERT INTO OrderItems (orderId, productVariantId, quantity, price)
                VALUES (:orderId, :productVariantId, :quantity, :price)";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            ':orderId' => $orderItem->getOrder()->getOrderId(),
            ':productVariantId' => $orderItem->getProductVariant()->getVariantId(),
            ':quantity' => $orderItem->getQuantity(),
            ':price' => $orderItem->getPrice()
        ]);
    }
    public function update(OrderItem $orderItem): void {
        $sql = "UPDATE OrderItems SET orderId = :orderId, productVariantId = :productVariantId,
            quantity = :quantity, price = :price WHERE orderItemId = :orderItemId";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            ':orderId' => $orderItem->getOrder()->getOrderId(),
            ':productVariantId' => $orderItem->getProductVariant()->getVariantId(),
            ':quantity' => $orderItem->getQuantity(),
            ':price' => $orderItem->getPrice(),
            ':orderItemId' => $orderItem->getOrderItemId()
        ]);
    }
    public function delete($id): void {
        $stmt = $this->connection->prepare("DELETE FROM OrderItems WHERE orderItemId = :id");
        $stmt->execute([':id' => $id]);
    }
}