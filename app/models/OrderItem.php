<?php

declare(strict_types=1);

namespace App\Models;

class OrderItem
{
    private ?int $orderItemId;
    private int $orderId;
    private int $productId;
    private int $variantId;
    private int $quantity;
    private float $price;
    private ?string $createdAt;

    // Joined info (optional)
    private ?string $productName = null;
    private ?string $productImage = null;
    private ?string $variantSize = null;
    private ?string $variantColor = null;
    private ?string $productCategory = null;

    public function __construct(
        ?int $orderItemId,
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

    // Core getters
    public function getOrderItemId(): ?int { return $this->orderItemId; }
    public function getOrderId(): int { return $this->orderId; }
    public function getProductId(): int { return $this->productId; }
    public function getVariantId(): int { return $this->variantId; }
    public function getQuantity(): int { return $this->quantity; }
    public function getPrice(): float { return $this->price; }
    public function getCreatedAt(): ?string { return $this->createdAt; }

    // Joined getters
    public function getProductName(): ?string { return $this->productName; }
    public function getProductImage(): ?string { return $this->productImage; }
    public function getVariantSize(): ?string { return $this->variantSize; }
    public function getVariantColor(): ?string { return $this->variantColor; }
    public function getProductCategory(): ?string { return $this->productCategory; }

    // Joined setters
    public function setProductName(?string $name): void { $this->productName = $name; }
    public function setProductImage(?string $image): void { $this->productImage = $image; }
    public function setVariantSize(?string $size): void { $this->variantSize = $size; }
    public function setVariantColor(?string $color): void { $this->variantColor = $color; }
    public function setProductCategory(?string $category): void { $this->productCategory = $category; }

}
