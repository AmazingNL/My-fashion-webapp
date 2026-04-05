<?php

declare(strict_types=1);

namespace App\Models;

class CartItem
{
    public int $cartItemId;
    public int $userId;
    public string $sessionId;
    public int $productId;
    public int $variantId;
    public int $quantity;
    public string $createdAt;

    public function __construct(
        int $cartItemId,
        int $userId,
        string $sessionId,
        int $productId,
        int $variantId,
        int $quantity,
        string $createdAt
    ) {
        $this->cartItemId = $cartItemId;
        $this->userId = $userId;
        $this->sessionId = $sessionId;
        $this->productId = $productId;
        $this->variantId = $variantId;
        $this->quantity = $quantity;
        $this->createdAt = $createdAt;
    }

}