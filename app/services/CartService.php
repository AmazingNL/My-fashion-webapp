<?php

namespace App\Services;

use App\Repositories\IProductRepository;
use Exception;
use InvalidArgumentException;

class CartService
{
    private const CART_KEY = 'shopping_cart';

    private IProductRepository $productRepository;

    public function __construct(IProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
        $this->initializeCart();
    }

    /* =========================
     * Public API (<= 15 lines)
     * ========================= */

    public function addItem(int $productId, int $variantId, int $quantity): void
    {
        $this->assertPositiveQuantity($quantity);

        [$product, $variant] = $this->loadProductAndVariant($productId, $variantId);
        $this->assertVariantBelongsToProduct($variant, $productId);

        $this->assertCanAddQuantity($productId, $variantId, $quantity);

        $key = $this->generateKey($productId, $variantId);
        $this->upsertCartItem($key, $productId, $variantId, $quantity);
    }

    public function updateQuantity(int $productId, int $variantId, int $quantity): bool
    {
        if ($quantity <= 0)
            return $this->removeItem($productId, $variantId);

        $key = $this->generateKey($productId, $variantId);
        if (!$this->cartHasKey($key))
            return false;

        $variant = $this->requireVariant($variantId);
        $this->assertCanSetQuantity($key, $variant, $quantity);

        $this->setCartQuantity($key, $quantity);
        return true;
    }

    public function removeItem(int $productId, int $variantId): bool
    {
        $key = $this->generateKey($productId, $variantId);
        if (!$this->cartHasKey($key))
            return false;

        $this->unsetCartKey($key);
        return true;
    }

    public function getCartItems(): array
    {
        $out = [];

        foreach ($this->cart() as $item) {
            $row = $this->buildCartItemRow($item);
            if ($row)
                $out[] = $row;
        }

        return $out;
    }

    public function getTotalPrice(): float
    {
        $total = 0.0;

        foreach ($this->cart() as $item) {
            $total += $this->lineTotal($item);
        }

        return $total;
    }

    public function getItemCount(): int
    {
        $count = 0;

        foreach ($this->cart() as $item) {
            $count += (int) ($item['quantity'] ?? 0);
        }

        return $count;
    }

    public function hasItem(int $productId, int $variantId): bool
    {
        return $this->cartHasKey($this->generateKey($productId, $variantId));
    }

    public function getItemQuantity(int $productId, int $variantId): int
    {
        $key = $this->generateKey($productId, $variantId);
        return (int) ($this->cart()[$key]['quantity'] ?? 0);
    }

    public function clearCart(): void
    {
        $_SESSION[self::CART_KEY] = [];
    }

    public function isEmpty(): bool
    {
        return empty($this->cart());
    }

    public function validateCart(): array
    {
        $errors = [];

        foreach ($this->cart() as $key => $item) {
            $msg = $this->validateCartRow($key, $item);
            if ($msg)
                $errors[] = $msg;
        }

        return $errors;
    }

    public function getCartSummary(): array
    {
        return [
            'itemCount' => $this->getItemCount(),
            'totalPrice' => $this->getTotalPrice(),
            'isEmpty' => $this->isEmpty(),
        ];
    }

    /* =========================
     * Virtual stock (your goal)
     * ========================= */

    public function getReservedVariantQuantity(int $variantId): int
    {
        $reserved = 0;

        foreach ($this->cart() as $item) {
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
        return max(0, $variant->getStockQuantity() - $reserved);
    }

    /* =========================
     * Private helpers
     * ========================= */

    private function initializeCart(): void
    {
        if (!isset($_SESSION[self::CART_KEY]) || !is_array($_SESSION[self::CART_KEY])) {
            $_SESSION[self::CART_KEY] = [];
        }
    }

    private function cart(): array
    {
        return $_SESSION[self::CART_KEY] ?? [];
    }

    private function cartHasKey(string $key): bool
    {
        return isset($_SESSION[self::CART_KEY][$key]);
    }

    private function setCartQuantity(string $key, int $quantity): void
    {
        $_SESSION[self::CART_KEY][$key]['quantity'] = $quantity;
    }

    private function unsetCartKey(string $key): void
    {
        unset($_SESSION[self::CART_KEY][$key]);
    }

    private function assertPositiveQuantity(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException("Quantity must be positive");
        }
    }

    private function loadProductAndVariant(int $productId, int $variantId): array
    {
        $product = $this->requireProduct($productId);
        $variant = $this->requireVariant($variantId);
        return [$product, $variant];
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

    private function assertVariantBelongsToProduct($variant, int $productId): void
    {
        if ($variant->getProductId() !== $productId) {
            throw new Exception("Invalid product variant combination");
        }
    }

    private function upsertCartItem(string $key, int $productId, int $variantId, int $quantity): void
    {
        if ($this->cartHasKey($key)) {
            $_SESSION[self::CART_KEY][$key]['quantity'] += $quantity;
            return;
        }

        $_SESSION[self::CART_KEY][$key] = $this->newCartItem($productId, $variantId, $quantity);
    }

    private function newCartItem(int $productId, int $variantId, int $quantity): array
    {
        return [
            'productId' => $productId,
            'variantId' => $variantId,
            'quantity' => $quantity,
            'addedAt' => time(),
        ];
    }

    private function assertCanAddQuantity(int $productId, int $variantId, int $quantityToAdd): void
    {
        $availableToAdd = $this->getVirtualVariantStock($variantId);
        if ($availableToAdd < $quantityToAdd) {
            throw new Exception("Insufficient stock available; only {$availableToAdd} left");
        }
    }

    private function assertCanSetQuantity(string $key, $variant, int $newQuantity): void
    {
        $currentQty = (int) ($_SESSION[self::CART_KEY][$key]['quantity'] ?? 0);
        $reservedTotal = $this->getReservedVariantQuantity($variant->getVariantId());
        $reservedExcludingThis = max(0, $reservedTotal - $currentQty);

        $maxAllowed = max(0, $variant->getStockQuantity() - $reservedExcludingThis);

        if ($newQuantity > $maxAllowed) {
            throw new Exception("Insufficient stock available; max allowed is {$maxAllowed}");
        }
    }

    private function buildCartItemRow(array $item): ?array
    {
        $productId = (int) ($item['productId'] ?? 0);
        $variantId = (int) ($item['variantId'] ?? 0);

        $product = $this->productRepository->getProductById($productId);
        $variant = $this->productRepository->getVariantById($variantId);

        if (!$product || !$variant)
            return null;

        return $this->formatCartItemRow($item, $product, $variant);
    }

    private function formatCartItemRow(array $item, $product, $variant): array
    {
        $qty = (int) ($item['quantity'] ?? 0);

        return [
            'productId' => (int) $item['productId'],
            'variantId' => (int) $item['variantId'],
            'quantity' => $qty,
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'category' => $product->getCategory(),
            'size' => $variant->getSize(),
            'color' => $variant->getColor(),
            'image' => $product->getImage(),
            'price' => (float) $product->getPrice(),
            'stockReal' => $variant->getStockQuantity(),
            'stockQuantity' => (int) $variant->getStockQuantity(),
            'stockRemaining' => $this->getVirtualVariantStock($variant->getVariantId()),
            'subtotal' => $product->getPrice() * $qty,
            'addedAt' => (int) ($item['addedAt'] ?? time()),
        ];
    }

    private function lineTotal(array $item): float
    {
        $product = $this->productRepository->getProductById((int) ($item['productId'] ?? 0));
        if (!$product)
            return 0.0;

        return $product->getPrice() * (int) ($item['quantity'] ?? 0);
    }

    private function validateCartRow(string $key, array $item): ?string
    {
        $product = $this->productRepository->getProductById((int) ($item['productId'] ?? 0));
        if (!$product) {
            $this->unsetCartKey($key);
            return "Product no longer available";
        }

        $variant = $this->productRepository->getVariantById((int) ($item['variantId'] ?? 0));
        if (!$variant) {
            $this->unsetCartKey($key);
            return "Product variant no longer available";
        }

        return $this->stockErrorMessage($product, $variant, (int) ($item['quantity'] ?? 0));
    }

    private function stockErrorMessage($product, $variant, int $qty): ?string
    {
        if ($variant->getStockQuantity() >= $qty)
            return null;

        return "{$product->getName()} ({$variant->getSize()}, {$variant->getColor()}) only has {$variant->getStockQuantity()} in stock";
    }

    private function generateKey(int $productId, int $variantId): string
    {
        return "p{$productId}_v{$variantId}";
    }
}
