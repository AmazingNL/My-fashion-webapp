<?php

namespace App\Services;

use App\Repositories\IProductRepository;
use Exception;

class CartService implements ICartService
{
    private const CART_KEY = 'shopping_cart';

    private IProductRepository $productRepository;

    public function __construct(IProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
        $this->initializeCart();
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

        $key = $this->generateKey($productId, $variantId);
        $cart = $this->sessionCart();
        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $quantity;
        } else {
            $cart[$key] = [
                'productId' => $productId,
                'variantId' => $variantId,
                'quantity' => $quantity,
                'addedAt' => time(),
            ];
        }

        $_SESSION[self::CART_KEY] = $cart;
    }

    public function updateQuantity(int $productId, int $variantId, int $quantity): bool
    {
        if ($quantity <= 0) {
            return $this->removeItem($productId, $variantId);
        }

        $key = $this->generateKey($productId, $variantId);
        $cart = $this->sessionCart();
        if (!isset($cart[$key])) {
            return false;
        }

        $variant = $this->requireVariant($variantId);
        $this->assertCanSetQuantity($key, $variant, $quantity);
        $cart[$key]['quantity'] = $quantity;
        $_SESSION[self::CART_KEY] = $cart;

        return true;
    }

    public function removeItem(int $productId, int $variantId): bool
    {
        $key = $this->generateKey($productId, $variantId);
        $cart = $this->sessionCart();
        if (!isset($cart[$key])) {
            return false;
        }

        unset($cart[$key]);
        $_SESSION[self::CART_KEY] = $cart;
        return true;
    }

    public function getCartItems(): array
    {
        $items = [];

        foreach ($this->sessionCart() as $item) {
            $productId = (int) ($item['productId'] ?? 0);
            $variantId = (int) ($item['variantId'] ?? 0);
            
            // Use cart-specific lookup that includes inactive products
            $product = $this->productRepository->getProductByIdForCart($productId);
            $variant = $this->productRepository->getVariantById($variantId);
            
            // Skip items with missing product or variant, but don't remove from session
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
                'addedAt' => (int) ($item['addedAt'] ?? time()),
            ];
        }

        return $items;
    }

    public function getTotalPrice(): float
    {
        $total = 0.0;

        foreach ($this->sessionCart() as $item) {
            $total += $this->lineTotal($item);
        }

        return $total;
    }

    public function getItemCount(): int
    {
        $count = 0;

        foreach ($this->sessionCart() as $item) {
            $count += (int) ($item['quantity'] ?? 0);
        }

        return $count;
    }

    public function clearCart(): void
    {
        $_SESSION[self::CART_KEY] = [];
    }

    public function isEmpty(): bool
    {
        return empty($this->sessionCart());
    }

    // Re-check cart items against current products, variants, and stock before checkout.
    public function validateCart(): array
    {
        $errors = [];
        $cart = $this->sessionCart();

        foreach ($cart as $key => $item) {
            $product = $this->productRepository->getProductById((int) ($item['productId'] ?? 0));
            if (!$product) {
                unset($cart[$key]);
                $errors[] = 'Product no longer available';
                continue;
            }
            $variant = $this->productRepository->getVariantById((int) ($item['variantId'] ?? 0));
            if (!$variant) {
                unset($cart[$key]);
                $errors[] = 'Product variant no longer available';
                continue;
            }
            $qty = (int) ($item['quantity'] ?? 0);
            if ((int) $variant->stockQuantity < $qty) {
                $errors[] = "{$product->productName} ({$variant->size}, {$variant->colour}) only has {$variant->stockQuantity} in stock";
            }
        }
        $_SESSION[self::CART_KEY] = $cart;
        return $errors;
    }

    /* =========================
     * Virtual stock 
     * ========================= */

    public function getReservedVariantQuantity(int $variantId): int
    {
        $reserved = 0;

        foreach ($this->sessionCart() as $item) {
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

    private function sessionCart(): array
    {
        return $_SESSION[self::CART_KEY] ?? [];
    }

    private function initializeCart(): void
    {
        if (!isset($_SESSION[self::CART_KEY]) || !is_array($_SESSION[self::CART_KEY])) {
            $_SESSION[self::CART_KEY] = [];
        }
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

    private function assertCanSetQuantity(string $key, $variant, int $newQuantity): void
    {
        $currentQty = (int) ($_SESSION[self::CART_KEY][$key]['quantity'] ?? 0);
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

    private function generateKey(int $productId, int $variantId): string
    {
        return "p{$productId}_v{$variantId}";
    }
}
