<?php

use app\models\Order;
use app\models\ProductVariant;

class OrderItem {
    private $orderItemId;
    private $orderId;
    private $productVariantId;
    private $quantity;
    private $price;
    private $lineTotal;

    public function __construct($orderItemId, Order $order, ProductVariant $productVariant, $quantity, $price, $lineTotal) {
        $this->orderItemId = $orderItemId;
        $this->orderId = $order->getOrderId();
        $this->productVariantId = $productVariant->getVariantId();
        $this->quantity = $quantity;
        $this->price = $price;
        $this->lineTotal = $lineTotal;  
    }

    public function getOrderItemId() {
        return $this->orderItemId;
    }

    public function getOrderId() {
        return $this->orderId;
    }

    public function getProductVariantId() {
        return $this->productVariantId;
    }

    public function getQuantity() {
        return $this->quantity;
    }

    public function getPrice() {
        return $this->price;
    }
    public function getLineTotal() {
        return $this->lineTotal;
    }   
}