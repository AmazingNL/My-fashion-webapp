<?php

namespace App\Models;

class Product
{
    private $productId;
    private $productName;
    private $description;
    private float $price;
    private $category;
    private $stock;
    private $image;
    private $createdAt;
    private $updatedAt;
    private $isActive;

    public function __construct($productId, $productName, $description, $price, $category, $stock, $image, $createdAt, $updatedAt, $isActive)
    {
        $this->productId = $productId;
        $this->productName = $productName;
        $this->description = $description;
        $this->price = $price;
        $this->category = $category;
        $this->stock = $stock;
        $this->image = $image;
        $this->createdAt = null;
        $this->updatedAt = null;
        $this->isActive = true;
    }

    public function getId()
    {
        return $this->productId;
    }

    public function getName()
    {
        return $this->productName;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getStock()
    {
        return $this->stock;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
    public function isActive()
    {
        return $this->isActive;
    }


}