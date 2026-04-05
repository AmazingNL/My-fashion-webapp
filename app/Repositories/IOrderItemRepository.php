<?php

namespace App\Repositories;

use App\Models\OrderItem;

interface IOrderItemRepository
{

    public function getAll(): array;
    public function findById(int $id): ?OrderItem;
    public function findByOrderId(int $orderId): array;
    public function save(OrderItem $orderItem): void;

}