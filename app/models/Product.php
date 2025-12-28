<?php

namespace App\Models;

class Product {
    private $productId;
    private $name;
    private $description;
    private float $price;
    private $category;
    private $stock;
    private $image;
    private $createdAt;
    private $updatedAt;
    private $isActive;

    public function __construct($productId, $name, $description, $price, $category, $stock, $image, $createdAt, $updatedAt, $isActive) {
        $this->productId = $productId;
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->category = $category;
        $this->stock = $stock;
        $this->image = $image;
        $this->createdAt = null;
        $this->updatedAt = null;
        $this->isActive = true;
    }

    public function getId() {
        return $this->productId;
    }

    public function getName() {
        return $this->name;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getStock() {
        return $this->stock;
    }

    public function getPrice() {
        return $this->price;
    }

    public function getCategory() {
        return $this->category;
    }

    public function getImage() {
        return $this->image;
    }
    public function getCreatedAt() {
        return $this->createdAt;
    }
    public function getUpdatedAt() {
        return $this->updatedAt;
    }
    public function isActive() {
        return $this->isActive;
    }
    public function __toString() {
        return json_encode([
            'productId' => $this->productId,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'category' => $this->category,
            'stock' => $this->stock,
            'image' => $this->image,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'isActive' => $this->isActive,
        ]);
    }

}