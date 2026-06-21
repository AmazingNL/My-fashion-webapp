<?php

namespace App\Services;

use App\Services\Interfaces\ICartService;

use App\DTO\CartItemRequestDto;
use App\Repositories\Interfaces\ICartRepository;
use App\Repositories\Interfaces\IProductRepository;
use Exception;

class CartService implements ICartService
{
    private IProductRepository $productRepository;
    private ICartRepository $cartRepository;
    private ?int $userId = null;

    public function __construct(IProductRepository $productRepository, ICartRepository $cartRepository)
    {
        $this->productRepository = $productRepository;
        $this->cartRepository = $cartRepository;
    }

    public function setUserId(int $userId): void
    {
        if ($userId <= 0) {
            throw new Exception('Authentication is required.');
        }

        $this->userId = $userId;
    }

    public function validateCartItemRequest(CartItemRequestDto $dto): void
    {
        $this->validateCartItemIds($dto->productId, $dto->variantId);

        if ($dto->quantity <= 0) {
            throw new Exception('Invalid input');
        }
    }

    public function validateCartItemIds(int $productId, int $variantId): void
    {
        if ($productId <= 0 || $variantId <= 0) {
            throw new Exception('Invalid input');
        }
    }


    public function addItem(int $productId, int $variantId, int $quantity): void
    {
        if ($quantity <= 0) {
            throw new Exception('Quantity must be positive');
        }

        $product = $this->requireProduct($productId);
        $variant = $this->requireVariant($variantId);
        if ((int) $variant->productId !== $product->productId) {
            throw new Exception('Invalid product variant combination');
        }

        $availableToAdd = $this->getVirtualVariantStock($variantId);
        if ($availableToAdd < $quantity) {
            throw new Exception("Insufficient stock available; only {$availableToAdd} left");
        }

        $existing = $this->cartRepository->findItem($this->requireUserId(), $productId, $variantId);
        $newQuantity = (int) ($existing['quantity'] ?? 0) + $quantity;

        $this->cartRepository->saveItem($this->requireUserId(), $productId, $variantId, $newQuantity);
    }

    public function updateQuantity(int $productId, int $variantId, int $quantity): bool
    {
        if ($quantity <= 0) {
            return $this->removeItem($productId, $variantId);
        }

        if ($this->cartRepository->findItem($this->requireUserId(), $productId, $variantId) === null) {
            return false;
        }

        $variant = $this->requireVariant($variantId);
        $this->assertCanSetQuantity($productId, $variant, $quantity);
        $this->cartRepository->saveItem($this->requireUserId(), $productId, $variantId, $quantity);

        return true;
    }

    public function removeItem(int $productId, int $variantId): bool
    {
        return $this->cartRepository->removeItem($this->requireUserId(), $productId, $variantId);
    }

    public function getCartItems(): array
    {
        $items = [];

        foreach ($this->cartItems() as $item) {
            $productId = (int) ($item['productId'] ?? 0);
            $variantId = (int) ($item['variantId'] ?? 0);
            
            // Use cart-specific lookup that includes inactive products
            $product = $this->productRepository->getProductById($productId);
            $variant = $this->productRepository->getVariantById($variantId);
            
            // Skip items with missing product or variant.
            if (!$product || !$variant) {
                error_log("Cart item skipped - Product ID: $productId (exists: " . ($product ? 'yes' : 'no') . "), Variant ID: $variantId (exists: " . ($variant ? 'yes' : 'no') . ")");
                continue;
            }

            $qty = (int) ($item['quantity'] ?? 0);
            $items[] = [
                'productId' => $productId,
                'variantId' => $variantId,
                'quantity' => $qty,
                'name' => (string) $product->productName,
                'description' => (string) $product->description,
                'category' => (string) $product->category,
                'size' => (string) $variant->size,
                'color' => (string) $variant->colour,
                'image' => (string) $product->image,
                'price' => (float) $product->price,
                'stockReal' => (int) $variant->stockQuantity,
                'stockQuantity' => (int) $variant->stockQuantity,
                'stockRemaining' => $this->getVirtualVariantStock($variantId),
                'subtotal' => (float) $product->price * $qty,
                'addedAt' => strtotime((string) ($item['createdAt'] ?? 'now')) ?: time(),
            ];
        }

        return $items;
    }

    public function getTotalPrice(): float
    {
        $total = 0.0;

        foreach ($this->cartItems() as $item) {
            $total += $this->lineTotal($item);
        }

        return $total;
    }

    public function getItemCount(): int
    {
        $count = 0;

        foreach ($this->cartItems() as $item) {
            $count += (int) ($item['quantity'] ?? 0);
        }

        return $count;
    }

    public function clearCart(): void
    {
        $this->cartRepository->clearByUserId($this->requireUserId());
    }

    public function isEmpty(): bool
    {
        return empty($this->cartItems());
    }

    // Re-check cart items against current products, variants, and stock before checkout.
    public function validateCart(): array
    {
        $errors = [];
        $cart = $this->cartItems();

        foreach ($cart as $key => $item) {
            $product = $this->productRepository->getProductById((int) ($item['productId'] ?? 0));
            if (!$product) {
                $this->cartRepository->removeItem($this->requireUserId(), (int) ($item['productId'] ?? 0), (int) ($item['variantId'] ?? 0));
                $errors[] = 'Product no longer available';
                continue;
            }
            $variant = $this->productRepository->getVariantById((int) ($item['variantId'] ?? 0));
            if (!$variant) {
                $this->cartRepository->removeItem($this->requireUserId(), (int) ($item['productId'] ?? 0), (int) ($item['variantId'] ?? 0));
                $errors[] = 'Product variant no longer available';
                continue;
            }
            $qty = (int) ($item['quantity'] ?? 0);
            if ((int) $variant->stockQuantity < $qty) {
                $errors[] = "{$product->productName} ({$variant->size}, {$variant->colour}) only has {$variant->stockQuantity} in stock";
            }
        }
        return $errors;
    }

    /* =========================
     * Virtual stock 
     * ========================= */

    public function getReservedVariantQuantity(int $variantId): int
    {
        $reserved = 0;

        foreach ($this->cartItems() as $item) {
            if ((int) ($item['variantId'] ?? 0) === $variantId) {
                $reserved += (int) ($item['quantity'] ?? 0);
            }
        }

        return $reserved;
    }

    public function getVirtualVariantStock(int $variantId): int
    {
        $variant = $this->productRepository->getVariantById($variantId);
        if (!$variant)
            return 0;

        $reserved = $this->getReservedVariantQuantity($variantId);
        return max(0, (int) $variant->stockQuantity - $reserved);
    }

    /* =========================
     * Private helpers
     * ========================= */

    private function cartItems(): array
    {
        return $this->cartRepository->findByUserId($this->requireUserId());
    }

    private function requireUserId(): int
    {
        if ($this->userId === null || $this->userId <= 0) {
            throw new Exception('Authentication is required.');
        }

        return $this->userId;
    }

    private function requireProduct(int $productId)
    {
        $product = $this->productRepository->getProductById($productId);
        if (!$product)
            throw new Exception("Product not found");
        return $product;
    }

    private function requireVariant(int $variantId)
    {
        $variant = $this->productRepository->getVariantById($variantId);
        if (!$variant)
            throw new Exception("Product variant not found");
        return $variant;
    }

    private function assertCanSetQuantity(int $productId, $variant, int $newQuantity): void
    {
        $current = $this->cartRepository->findItem($this->requireUserId(), $productId, (int) $variant->variantId);
        $currentQty = (int) ($current['quantity'] ?? 0);
        $reservedTotal = $this->getReservedVariantQuantity((int) $variant->variantId);
        $reservedExcludingThis = max(0, $reservedTotal - $currentQty);

        $maxAllowed = max(0, (int) $variant->stockQuantity - $reservedExcludingThis);

        if ($newQuantity > $maxAllowed) {
            throw new Exception("Insufficient stock available; max allowed is {$maxAllowed}");
        }
    }

    private function lineTotal(array $item): float
    {
        $product = $this->productRepository->getProductById((int) ($item['productId'] ?? 0));
        if (!$product)
            return 0.0;

        return (float) $product->price * (int) ($item['quantity'] ?? 0);
    }

}
