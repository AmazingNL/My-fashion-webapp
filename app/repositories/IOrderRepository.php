<?php

namespace app\repositories;

use app\models\Order;

interface IOrderRepository {
    public function getAll(): array;
    public function findById($id): ?Order;
    public function findByCustomerId($customerId): array;
    public function findByStatus($status): array;
    public function findByDateRange($startDate, $endDate): array;
    public function create(Order $order): int;
    public function update(Order $order): bool;
    public function delete($id): bool;
}