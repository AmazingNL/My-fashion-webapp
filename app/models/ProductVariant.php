<?php

namespace App\Models;

class ProductVariant
{
    private ?int $variantId;
    private int $productId;
    private string $size;
    private string $colour;
    private int $stockQuantity;

    public function __construct(
        ?int $variantId,
        int $productId,
        string $size,
        string $colour,
        int $stockQuantity
    ) {
        $this->variantId = $variantId;
        $this->productId = $productId;
        $this->size = $size;
        $this->colour = $colour;
        $this->stockQuantity = $stockQuantity;
    }

    // Getters
    public function getVariantId(): ?int
    {
        return $this->variantId;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getSize(): string
    {
        return $this->size;
    }

    public function getColour(): string
    {
        return $this->colour;
    }

    public function getColor(): string
    {
        return $this->colour;
    }

    public function getStock(): int
    {
        return $this->stockQuantity;
    }

    // Setters
    public function setSize(string $size): void
    {
        $this->size = $size;
    }

    public function setColour(string $colour): void
    {
        $this->colour = $colour;
    }

    public function setColor(string $color): void
    {
        $this->colour = $color;
    }

    public function setStock(int $stockQuantity): void
    {
        $this->stockQuantity = $stockQuantity;
    }

    public function setStockQuantity(int $stockQuantity): void
    {
        $this->stockQuantity = $stockQuantity;
    }
}