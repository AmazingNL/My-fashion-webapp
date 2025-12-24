<?php

namespace app\repositories;

use app\models\OrderItem;

interface IOrderItemRepository {

    public function getAll(): array;
    public function findById($id): ?OrderItem;
    public function findByOrderId($orderId): array;
    public function save(OrderItem $orderItem): void;
    public function update(OrderItem $orderItem): void;
    public function delete($id): void;

    // Define methods for order item repository
}