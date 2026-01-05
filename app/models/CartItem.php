<?php

declare(strict_types=1);

namespace App\Models;

class CartItem
{
    private ?int $cartItemId;
    private ?int $userId;
    private ?string $sessionId;
    private int $productId;
    private ?int $variantId;
    private int $quantity;
    private ?string $createdAt;

    // Product details (when joined)
    private ?string $productName = null;
    private ?float $productPrice = null;
    private ?string $productImage = null;
    private ?string $variantSize = null;
    private ?string $variantColour = null;

    public function __construct(
        ?int $cartItemId,
        ?int $userId,
        ?string $sessionId,
        int $productId,
        ?int $variantId,
        int $quantity,
        ?string $createdAt = null
    ) {
        $this->cartItemId = $cartItemId;
        $this->userId = $userId;
        $this->sessionId = $sessionId;
        $this->productId = $productId;
        $this->variantId = $variantId;
        $this->quantity = $quantity;
        $this->createdAt = $createdAt;
    }

    // Getters
    public function getCartItemId(): ?int { return $this->cartItemId; }
    public function getUserId(): ?int { return $this->userId; }
    public function getSessionId(): ?string { return $this->sessionId; }
    public function getProductId(): int { return $this->productId; }
    public function getVariantId(): ?int { return $this->variantId; }
    public function getQuantity(): int { return $this->quantity; }
    public function getCreatedAt(): ?string { return $this->createdAt; }

    public function getProductName(): ?string { return $this->productName; }
    public function getProductPrice(): ?float { return $this->productPrice; }
    public function getProductImage(): ?string { return $this->productImage; }
    public function getVariantSize(): ?string { return $this->variantSize; }
    public function getVariantColour(): ?string { return $this->variantColour; }

    // Setters
    public function setCartItemId(?int $cartItemId): void { $this->cartItemId = $cartItemId; }
    public function setQuantity(int $quantity): void { $this->quantity = $quantity; }

    public function setProductName(?string $name): void { $this->productName = $name; }
    public function setProductPrice(?float $price): void { $this->productPrice = $price; }
    public function setProductImage(?string $image): void { $this->productImage = $image; }
    public function setVariantSize(?string $size): void { $this->variantSize = $size; }
    public function setVariantColour(?string $colour): void { $this->variantColour = $colour; }

    /**
     * Get subtotal for this cart item
     */
    public function getSubtotal(): float
    {
        return $this->productPrice ? ($this->productPrice * $this->quantity) : 0.0;
    }
}