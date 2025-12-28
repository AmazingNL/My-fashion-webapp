<?php

namespace App\Models;

use App\Models\Product;

class ProductVariant {
    private $variantId;
    private $product;
    private $size;
    private $color;
    private $stock;

    public function __construct($variantId, Product $product, $size, $color, $stock) {
        $this->variantId = $variantId;
        $this->product = $product; 
        $this->size = $size;
        $this->color = $color;
        $this->stock = $stock;
    }

    public function getVariantId() {
        return $this->variantId;
    }

    public function getProduct() {
        return $this->product;
    }

    public function getSize() {
        return $this->size;
    }

    public function getColor() {
        return $this->color;
    }

    public function getStock() {
        return $this->stock;
    }
}
