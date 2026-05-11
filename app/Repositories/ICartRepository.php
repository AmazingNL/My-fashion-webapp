<?php

declare(strict_types=1);

namespace App\Repositories;

interface ICartRepository
{
    public function findByUserId(int $userId): array;

    public function findItem(int $userId, int $productId, int $variantId): ?array;

    public function saveItem(int $userId, int $productId, int $variantId, int $quantity): void;

    public function removeItem(int $userId, int $productId, int $variantId): bool;

    public function clearByUserId(int $userId): void;
}