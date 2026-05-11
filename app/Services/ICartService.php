<?php

namespace App\Services;

use App\DTO\CartItemRequestDto;

interface ICartService
{

    public function setUserId(int $userId): void;
    public function validateCartItemIds(int $productId, int $variantId): void;
    public function validateCartItemRequest(CartItemRequestDto $dto): void;
    public function addItem(int $productId, int $variantId, int $quantity): void;
    public function updateQuantity(int $productId, int $variantId, int $quantity): bool;
    public function removeItem(int $productId, int $variantId): bool;
    public function getCartItems(): array;
    public function getTotalPrice(): float;
    public function getItemCount(): int;
    public function clearCart(): void;
    public function isEmpty(): bool;
    public function validateCart(): array;
    public function getVirtualVariantStock(int $variantId): int;

}