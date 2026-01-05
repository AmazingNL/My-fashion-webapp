<?php

namespace App\Models;

class Order
{
    private ?int $orderId;
    private int $userId;
    private string $status;
    private float $totalAmount;

    private string $shippingAddress;
    private string $billingAddress;

    private string $paymentMethod;
    private string $paymentStatus;

    private ?string $createdAt;
    private ?string $updatedAt;

    public function __construct(
        ?int $orderId = null,
        int $userId = 0,
        string $status = 'pending',
        float $totalAmount = 0.0,
        string $shippingAddress = '',
        string $billingAddress = '',
        string $paymentMethod = 'credit_card',
        string $paymentStatus = 'unpaid',
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

    public function getOrderId(): ?int { return $this->orderId; }
    public function getUserId(): int { return $this->userId; }
    public function getStatus(): string { return $this->status; }
    public function getTotalAmount(): float { return $this->totalAmount; }

    public function getShippingAddress(): string { return $this->shippingAddress; }
    public function getBillingAddress(): string { return $this->billingAddress; }

    public function getPaymentMethod(): string { return $this->paymentMethod; }
    public function getPaymentStatus(): string { return $this->paymentStatus; }

    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }
}
