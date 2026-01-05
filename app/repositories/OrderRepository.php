<?php

namespace App\Repositories;

use App\Models\Order;
use App\Repositories\IOrderRepository;
use App\Core\RepositoryBase;


class OrderRepository extends RepositoryBase implements IOrderRepository
{
    // Implementation of repository methods here

    public function getAll(): array
    {
        $sql = "SELECT * FROM orders ORDER BY createdAt DESC";
        $stmt = $this->connection->query($sql);

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_map([$this, 'mapRowToOrder'], $rows);
    }


    public function findById($orderId): ?Order
    {
        $sql = "SELECT * FROM orders WHERE orderId = :id LIMIT 1";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':id' => $orderId]);

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row ? $this->mapRowToOrder($row) : null;
    }


    public function findByCustomerId($customerId): array
    {
        $sql = "SELECT * FROM orders WHERE userId = :userId ORDER BY createdAt DESC";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':userId' => $customerId]);

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_map([$this, 'mapRowToOrder'], $rows);
    }

    private function mapRowToOrder(array $r): Order
    {
        return new Order(
            (int) ($r['orderId'] ?? 0),
            (int) ($r['userId'] ?? 0),
            (string) ($r['status'] ?? 'pending'),
            (float) ($r['totalAmount'] ?? 0.0),
            (string) ($r['shippingAddress'] ?? ''),
            (string) ($r['billingAddress'] ?? ''),
            (string) ($r['paymentMethod'] ?? 'credit_card'),
            (string) ($r['paymentStatus'] ?? 'unpaid'),
            $r['createdAt'] ?? null,
            $r['updatedAt'] ?? null
        );
    }



    public function findByStatus($status): array
    {
        // Implementation code
        $sql = "SELECT * FROM orders WHERE status = :status";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':status' => $status]);
        $orders = $stmt->fetchAll(\PDO::FETCH_CLASS, Order::class);
        return $orders;

    }

    public function findByDateRange($startDate, $endDate): array
    {
        // Implementation code
        $sql = "SELECT * FROM orders WHERE createdAt BETWEEN :startDate AND :endDate";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':startDate' => $startDate, ':endDate' => $endDate]);
        $orders = $stmt->fetchAll(\PDO::FETCH_CLASS, Order::class);
        return $orders;

    }

    public function create(Order $order): int
    {
        // Implementation code
        $sql = "INSERT INTO orders (userId, status, totalAmount, createdAt, updatedAt, shippingAddress, billingAddress, paymentMethod, paymentStatus) 
                VALUES (:userId, :status, :totalAmount, NOW(), NOW(), :shippingAddress, :billingAddress, :paymentMethod, :paymentStatus)";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            ':userId' => $order->getUserId(),
            ':status' => $order->getStatus(),
            ':totalAmount' => $order->getTotalAmount(),
            ':shippingAddress' => $order->getShippingAddress(),
            ':billingAddress' => $order->getBillingAddress(),
            ':paymentMethod' => $order->getPaymentMethod(),
            ':paymentStatus' => $order->getPaymentStatus()
        ]);
        return (int) $this->connection->lastInsertId();

    }

    public function delete($id): bool
    {
        // Implementation code
        $sql = "DELETE FROM orders WHERE orderId = :orderId";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->execute([':orderId' => $id]);
        return $result;

    }

    public function updateStatus(int $orderId, string $status): bool
    {
        $sql = "UPDATE orders SET status = :status, updatedAt = NOW() WHERE orderId = :orderId";
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute([':status' => $status, ':orderId' => $orderId]);
    }

    public function updatePaymentStatus(int $orderId, string $paymentStatus): bool
    {
        $sql = "UPDATE orders SET paymentStatus = :paymentStatus, updatedAt = NOW() WHERE orderId = :orderId";
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute([':paymentStatus' => $paymentStatus, ':orderId' => $orderId]);
    }

    public function updateAddresses(int $orderId, string $shippingAddress, string $billingAddress): bool
    {
        $sql = "UPDATE orders 
            SET shippingAddress = :shipping, billingAddress = :billing, updatedAt = NOW()
            WHERE orderId = :orderId";
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute([
            ':shipping' => $shippingAddress,
            ':billing' => $billingAddress,
            ':orderId' => $orderId
        ]);
    }

}