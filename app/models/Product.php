<?php

class Product {
    private $productId;
    private $name;
    private $description;
    private $price;
    private $category;
    private $image;
    private $createdAt;
    private $updatedAt;
    private $isActive;

    public function __construct($productId, $name, $description, $price, $category, $image) {
        $this->productId = $productId;
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->category = $category;
        $this->image = $image;
        $this->createdAt = null;
        $this->updatedAt = null;
        $this->isActive = true;
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getDescription() {
        return $this->description;
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
}