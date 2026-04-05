<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentStatus;
use App\Core\RepositoryBase;

use Throwable;


class OrderRepository extends RepositoryBase implements IOrderRepository
{

    public function getAll(): array
    {
        try {
            $sql = "SELECT * FROM orders ORDER BY createdAt DESC";
            $stmt = $this->connection->query($sql);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            return array_map([$this, 'mapRowToOrder'], $rows);
        } catch (Throwable $e) {
            throw $this->dbError($e);
        }
    }

    public function findById(int $orderId): ?Order
    {
        try {
            $sql = "SELECT * FROM orders WHERE orderId = :id LIMIT 1";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':id' => $orderId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ? $this->mapRowToOrder($row) : null;
        } catch (Throwable $e) {
            throw $this->dbError($e);
        }
    }


    public function findByCustomerId(int $customerId): array
    {
        try {
            $sql = "SELECT * FROM orders WHERE userId = :userId ORDER BY createdAt DESC";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':userId' => $customerId]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            return array_map([$this, 'mapRowToOrder'], $rows);
        } catch (Throwable $e) {
            throw $this->dbError($e);
        }
    }

    public function create(Order $order): int
    {
        try {
            $sql = "INSERT INTO orders (userId, status, totalAmount, createdAt, updatedAt, shippingAddress, billingAddress, paymentMethod, paymentStatus) 
                    VALUES (:userId, :status, :totalAmount, NOW(), NOW(), :shippingAddress, :billingAddress, :paymentMethod, :paymentStatus)";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([
                ':userId' => $order->userId,
                ':status' => $order->status->value,
                ':totalAmount' => $order->totalAmount,
                ':shippingAddress' => $order->shippingAddress,
                ':billingAddress' => $order->billingAddress,
                ':paymentMethod' => $order->paymentMethod,
                ':paymentStatus' => $order->paymentStatus->value
            ]);
            return (int) $this->connection->lastInsertId();
        } catch (Throwable $e) {
            throw $this->dbError($e);
        }

    }

    public function delete(int $id): bool
    {
        try {
            $sql = "DELETE FROM orders WHERE orderId = :orderId";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':orderId' => $id]);
            return $stmt->rowCount() > 0;
        } catch (Throwable $e) {
            throw $this->dbError($e);
        }

    }

    public function updateStatus(int $orderId, string $status, ?string $paymentStatus = null): bool
    {
        try {
            $params = [
                ':status' => $status,
                ':orderId' => $orderId
            ];

            if ($paymentStatus !== null) {
                $sql = "UPDATE orders 
                    SET status = :status, paymentStatus = :paymentStatus, updatedAt = NOW() 
                    WHERE orderId = :orderId";
                $params[':paymentStatus'] = $paymentStatus;
            } else {
                $sql = "UPDATE orders 
                    SET status = :status, updatedAt = NOW() 
                    WHERE orderId = :orderId";
            }

            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount() > 0;
        } catch (Throwable $e) {
            throw $this->dbError($e);
        }
    }


    public function updatePaymentStatus(int $orderId, string $paymentStatus): bool
    {
        try {
            $sql = "UPDATE orders SET paymentStatus = :paymentStatus, updatedAt = NOW() WHERE orderId = :orderId";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':paymentStatus' => $paymentStatus, ':orderId' => $orderId]);
            return $stmt->rowCount() > 0;
        } catch (Throwable $e) {
            throw $this->dbError($e);
        }
    }

    public function updateAddresses(int $orderId, string $shippingAddress, string $billingAddress): bool
    {
        try {
            $sql = "UPDATE orders 
                SET shippingAddress = :shipping, billingAddress = :billing, updatedAt = NOW()
                WHERE orderId = :orderId";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([
                ':shipping' => $shippingAddress,
                ':billing' => $billingAddress,
                ':orderId' => $orderId
            ]);
            return $stmt->rowCount() > 0;
        } catch (Throwable $e) {
            throw $this->dbError($e);
        }
    }


    private function mapRowToOrder(array $row): Order
    {
        $statusStr = (string) ($row['status'] ?? 'pending');
        $paymentStr = (string) ($row['paymentStatus'] ?? 'pending');

        $status = OrderStatus::tryFrom($statusStr) ?? OrderStatus::PENDING;
        $payment = PaymentStatus::tryFrom($paymentStr) ?? PaymentStatus::PENDING;

        return new Order(
            (int) ($row['orderId'] ?? 0),
            (int) ($row['userId'] ?? 0),
            $status,
            (float) ($row['totalAmount'] ?? 0),
            (string) ($row['shippingAddress'] ?? ''),
            (string) ($row['billingAddress'] ?? ''),
            (string) ($row['paymentMethod'] ?? 'credit_card'),
            $payment,
            isset($row['createdAt']) ? (string) $row['createdAt'] : null,
            isset($row['updatedAt']) ? (string) $row['updatedAt'] : null
        );
    }

    private function dbError(Throwable $e): \RuntimeException
    {
        return new \RuntimeException('DB error ' . $e->getMessage(), 0, $e);
    }

}