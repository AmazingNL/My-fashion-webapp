<?php

use app\models\Product;

class ProductVariant {
    private $variantId;
    private $productId; 
    private $size;
    private $color;
    private $stock;

    public function __construct($variantId, Product $product, $size, $color, $stock) {
        $this->variantId = $variantId;
        $this->productId = $product->getId(); 
        $this->size = $size;
        $this->color = $color;
        $this->stock = $stock;
    }

    public function getVariantId() {
        return $this->variantId;
    }

    public function getProductId() {
        return $this->productId;
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
