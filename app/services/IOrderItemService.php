<?php

namespace App\Services;

interface IOrderItemService
{
    public function createFromCart(int $orderId, array $cartItems): array;
    public function getByOrderId(int $orderId): array;

}