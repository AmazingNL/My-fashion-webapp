<?php

namespace App\Repositories;

use App\Models\Order;

interface IOrderRepository
{
    public function getAll(): array;
    public function countAll(): int;
    public function findById($id): ?Order;
    public function findByCustomerId($customerId): array;
    public function findByStatus($status): array;
    public function findByDateRange($startDate, $endDate): array;
    public function create(Order $order): int;
    public function delete($id): bool;
    public function updateStatus(int $orderId, string $status, ?string $paymentStatus = null): bool;
    public function updatePaymentStatus(int $id, string $status): bool;
    public function updateAddresses(int $orderId, string $shippingAddress, string $billedAddress): bool;



}