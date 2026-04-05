<?php

namespace App\Models;

class Order
{
    public int $orderId;
    public int $userId;
    public OrderStatus $status;
    public float $totalAmount;

    public string $shippingAddress;
    public string $billingAddress;

    public string $paymentMethod;
    public PaymentStatus $paymentStatus;

    public ?string $createdAt;
    public ?string $updatedAt;

    public function __construct(
        int $orderId = 0,
        int $userId = 0,
        OrderStatus $status = OrderStatus::PENDING,
        float $totalAmount = 0.0,
        string $shippingAddress = '',
        string $billingAddress = '',
        string $paymentMethod = 'credit_card',
        PaymentStatus $paymentStatus = PaymentStatus::PENDING,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->orderId = $orderId;
        $this->userId = $userId;
        $this->status = $status;
        $this->totalAmount = $totalAmount;
        $this->shippingAddress = $shippingAddress;
        $this->billingAddress = $billingAddress;
        $this->paymentMethod = $paymentMethod;
        $this->paymentStatus = $paymentStatus;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }


}
