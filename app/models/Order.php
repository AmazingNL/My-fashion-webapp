<?php

use app\models\User;
use app\models\OrderStatus;

class Order {
    private $orderId;
    private $customerId;
    private OrderStatus $status;
    private $createdAt;
    private $updatedAt;
    private $totalAmount;

    public function __construct($orderId, User $customerId, $createdAt, $updatedAt, OrderStatus $status, $totalAmount) {
        $this->orderId = $orderId;
        $this->customerId = $customerId->getId();
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->status = $status;
        $this->totalAmount = $totalAmount;
    }

    public function getOrderId() {
        return $this->orderId;
    }

    public function getCustomerId() {
        return $this->customerId;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getCreatedAt() {
        return $this->createdAt;
    }

    public function getUpdatedAt() {
        return $this->updatedAt;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getTotalAmount() {
        return $this->totalAmount;
    }
}