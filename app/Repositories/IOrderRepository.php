<?php

namespace App\Repositories;

use App\Models\Order;

interface IOrderRepository
{
    public function getAll(): array;
    public function findById(int $id): ?Order;
    public function findByCustomerId(int $customerId): array;
    public function create(Order $order): int;
    public function delete(int $id): bool;
    public function updateStatus(int $orderId, string $status, ?string $paymentStatus = null): bool;
    public function updatePaymentStatus(int $orderId, string $paymentStatus): bool;
    public function updateAddresses(int $orderId, string $shippingAddress, string $billingAddress): bool;
}