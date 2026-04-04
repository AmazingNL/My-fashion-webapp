<?php

namespace App\ViewModel;

class ProductDetailsVM
{
    private ?array $product;
    private array $variants;

    public function __construct(?array $product = null, array $variants = [])
    {
        $this->product = $product;
        $this->variants = $variants;
    }

    public function hasProduct(): bool
    {
        return $this->product !== null;
    }

    public function getProduct(): ?array
    {
        return $this->product;
    }

    public function getVariants(): array
    {
        return $this->variants;
    }

}