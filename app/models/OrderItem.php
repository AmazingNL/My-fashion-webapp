<?php

namespace App\Models;

use App\Models\Order;
use App\Models\ProductVariant;

class OrderItem {
    private $orderItemId;
    private $order;
    private $productVariant;
    private $quantity;
    private $price;

    public function __construct($orderItemId, Order $order, ProductVariant $productVariant, $quantity, $price) {
        $this->orderItemId = $orderItemId;
        $this->order = $order;
        $this->productVariant = $productVariant;
        $this->quantity = $quantity;
        $this->price = $price;
    }

    public function getOrderItemId() {
        return $this->orderItemId;
    }

    public function getOrder() {
        return $this->order;
    }

    public function getProductVariant() {
        return $this->productVariant;
    }

    public function getQuantity() {
        return $this->quantity;
    }

    public function getPrice() {
        return $this->price;
    }
    public function getLineTotal() {
        return $this->$quantity * $this->price;
    }   
}