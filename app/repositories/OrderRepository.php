<?php

namespace app\repositories;

use app\models\Order;
use app\repositories\IOrderRepository;
use app\core\RepositoryBase;


class OrderRepository extends RepositoryBase implements IOrderRepository {
    // Implementation of repository methods here

    public function getAll(): array {
        // Implementation code
        $sql = "SELECT * FROM orders";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        $orders = $stmt->fetchAll(\PDO::FETCH_CLASS, Order::class);
        return $orders;

    }

    public function findById($id): ?Order {
        // Implementation code
        $sql = "SELECT * FROM orders WHERE id = :id";
        $stmt = $this->connection->prepare($sql);   
        $stmt->execute([':id' => $id]);
        $order = $stmt->fetchObject(Order::class) ?: null;
        return $order;

    }

    public function findByCustomerId($customerId): array {
        // Implementation code
        $sql = "SELECT * FROM orders WHERE customerId = :customerId";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':customerId' => $customerId]);
        $orders = $stmt->fetchAll(\PDO::FETCH_CLASS, Order::class);
        return $orders;
    }

    public function findByStatus($status): array {
        // Implementation code
        $sql = "SELECT * FROM orders WHERE status = :status";
        $stmt = $this->connection->prepare($sql);  
        $stmt->execute([':status' => $status]);
        $orders = $stmt->fetchAll(\PDO::FETCH_CLASS, Order::class);
        return $orders;

    }

    public function findByDateRange($startDate, $endDate): array {
        // Implementation code
        $sql = "SELECT * FROM orders WHERE orderDate BETWEEN :startDate AND :endDate";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':startDate' => $startDate, ':endDate' => $endDate]);
        $orders = $stmt->fetchAll(\PDO::FETCH_CLASS, Order::class);
        return $orders;

    }

    public function create(Order $order): int {
        // Implementation code
        $sql = "INSERT INTO orders (customerId, orderDate, status, totalAmount, createdAt, updatedAt) 
                VALUES (:customerId, :orderDate, :status, :totalAmount, NOW(), NOW())";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            ':customerId' => $order->getCustomerId(),
            ':orderDate' => $order->getOrderDate(),
            ':status' => $order->getStatus(),
            ':totalAmount' => $order->getTotalAmount()
        ]);
        return (int)$this->connection->lastInsertId();

    }

    public function update(Order $order): bool {
        // Implementation code
        $sql = "UPDATE orders SET customerId = :customerId, orderDate = :orderDate, status = :status, 
            totalAmount = :totalAmount, updatedAt = NOW() WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->execute([
            ':customerId' => $order->getCustomerId(),
            ':orderDate' => $order->getOrderDate(),
            ':status' => $order->getStatus(),
            ':totalAmount' => $order->getTotalAmount(),
            ':id' => $order->getId()
        ]);
        return $result;

    }

    public function delete($id): bool {
        // Implementation code
        $sql = "DELETE FROM orders WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->execute([':id' => $id]);
        return $result;

    }
}