<?php

namespace App\Services;

interface ICartService {

    public function addItem(int $productId, int $variantId, int $quantity): void;
    public function updateQuantity(int $productId, int $variantId, int $quantity): bool;
    public function removeItem(int $productId, int $variantId): bool;
    public function getCartItems(): array;
    public function getTotalPrice(): float;
    public function getItemCount(): int;
    public function clearCart(): void;
}