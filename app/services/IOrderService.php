<?php

namespace App\Services;
use App\Models\OrderStatus;
use App\Models\PaymentStatus;
use App\Models\Order;



interface IOrderService
{
    public function placeOrder(
        int $userId,
        string $shippingAddress,
        ?string $billingAddress,
        string $paymentMethod,
        OrderStatus $orderStatus,
        PaymentStatus $paymentStatus
    ): array;

    public function getMyOrders(int $userId): array;
    public function getMyOrder(int $userId, int $orderId): Order;
    public function cancelMyOrder(int $userId, int $orderId): bool;
    public function updateMyAddresses(int $userId, int $orderId, string $shipping, ?string $billing = null): bool;
    public function adminUpdateStatus(int $orderId, OrderStatus $newStatus): bool;
    public function adminUpdatePaymentStatus(int $orderId, PaymentStatus $paymentStatus): bool;
    public function getAllOrders(): array;

}