<?php

namespace App\Services\Interfaces;

interface IOrderItemService
{
    public function createFromCart(int $orderId, array $cartItems): void;
    public function getByOrderId(int $orderId): array;

}