<?php

namespace App\Models;

class ProductVariant
{
    public int $variantId;
    public int $productId;
    public string $size;
    public string $colour;
    public int $stockQuantity;

    public function __construct(int $variantId, int $productId, string $size, string $colour, int $stockQuantity
    ) {
        $this->variantId = $variantId;
        $this->productId = $productId;
        $this->size = $size;
        $this->colour = $colour;
        $this->stockQuantity = $stockQuantity;
    }

}