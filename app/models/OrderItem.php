<?php

declare(strict_types=1);

namespace App\Models;

class OrderItem
{
    public int $orderItemId;
    public int $orderId;
    public int $productId;
    public int $variantId;
    public int $quantity;
    public float $price;
    public ?string $createdAt;

    public function __construct(
        int $orderItemId,
        int $orderId,
        int $productId,
        int $variantId,
        int $quantity,
        float $price,
        ?string $createdAt = null
    ) {
        $this->orderItemId = $orderItemId;
        $this->orderId = $orderId;
        $this->productId = $productId;
        $this->variantId = $variantId;
        $this->quantity = $quantity;
        $this->price = $price;
        $this->createdAt = $createdAt;
    }



}
