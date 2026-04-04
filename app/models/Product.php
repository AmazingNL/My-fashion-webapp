<?php

namespace App\Models;

class Product
{
    public ?int $productId = null;
    public string $productName = '';
    public string $description = '';
    public float $price = 0.0;
    public string $category = '';
    public int $stock = 0;
    public string $image = '';
    public ?string $createdAt = null;
    public ?string $updatedAt = null;
    public bool $isActive = true;

    public function __construct(
        ?int $productId = null,
        string $productName = '',
        string $description = '',
        float $price = 0.0,
        string $category = '',
        int $stock = 0,
        string $image = '',
        \DateTimeInterface|string|null $createdAt = null,
        \DateTimeInterface|string|null $updatedAt = null,
        bool $isActive = true
    ) {
        if (func_num_args() === 0) {
            return;
        }

        $this->productId = $productId;
        $this->productName = $productName;
        $this->description = $description;
        $this->price = $price;
        $this->category = $category;
        $this->stock = $stock;
        $this->image = $image;
        $this->createdAt = $createdAt instanceof \DateTimeInterface ? $createdAt->format('Y-m-d H:i:s') : $createdAt;
        $this->updatedAt = $updatedAt instanceof \DateTimeInterface ? $updatedAt->format('Y-m-d H:i:s') : $updatedAt;
        $this->isActive = $isActive;
    }


}