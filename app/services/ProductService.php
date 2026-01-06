<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Repositories\IProductRepository;

class ProductService implements IProductService
{
    private IProductRepository $productRepository;

    public function __construct(IProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /* =========================
       Public (existing) methods
       ========================= */

    public function getProductDetails($id): array
    {
        try {
            $product = $this->productRepository->getProductById($id);

            if (!$product) {
                error_log("Product with ID $id not found");
                return [
                    'product' => null,
                    'variants' => [],
                    'sizes' => [],
                    'colors' => [],
                    'errors' => ['Product not found'],
                ];
            }

            $variants = $this->productRepository->getVariantsByProductId($id);

            if (empty($variants)) {
                error_log("WARNING: Product ID $id has no variants");
                return [
                    'product' => $product,
                    'variants' => [],
                    'sizes' => [],
                    'colors' => [],
                    'errors' => ['This product has no size/color options available'],
                ];
            }

            $sizes = [];
            $colors = [];

            foreach ($variants as $variant) {
                $size = is_object($variant) ? $variant->getSize() : ($variant['size'] ?? '');
                $colour = is_object($variant) ? $variant->getColour() : ($variant['colour'] ?? ($variant['color'] ?? ''));

                if ($size !== '')
                    $sizes[$size] = true;
                if ($colour !== '')
                    $colors[$colour] = true;
            }

            return [
                'product' => $product,
                'variants' => $variants,
                'sizes' => array_keys($sizes),
                'colors' => array_keys($colors),
                'errors' => [],
            ];
        } catch (\Throwable $e) {
            return [
                'product' => null,
                'variants' => [],
                'sizes' => [],
                'colors' => [],
                'errors' => ['Failed to retrieve product details'],
            ];
        }
    }

    public function getActiveProducts(): array
    {
        try {
            return $this->productRepository->getAllActive();
        } catch (\Exception $e) {
            error_log("Failed to get active products: " . $e->getMessage());
            return [];
        }
    }

    public function getSimilarProducts(int $productId, string $category, int $limit = 4): array
    {
        try {
            return $this->productRepository->findSimilarProducts($productId, $category, $limit);
        } catch (\Throwable $e) {
            error_log("Failed to fetch similar products: " . $e->getMessage());
            return [];
        }
    }

    public function toggleFavourite($productId): array
    {
        if ($productId <= 0)
            return ['error' => 'Invalid product ID'];

        $session = $_SESSION['favourites'] ??= [];

        if (isset($session[$productId])) {
            unset($session[$productId]);
            $_SESSION['favourites'] = $session;
            return ['productId' => $productId, 'favourited' => false];
        }

        $session[$productId] = true;
        $_SESSION['favourites'] = $session;
        return ['productId' => $productId, 'favourited' => true];
    }

    public function addToBasket($variantId, $quantity): array
    {
        if ($variantId <= 0)
            return ['error' => 'Invalid variant ID'];

        $basket = $_SESSION['basket'] ??= [];

        if (isset($basket[$variantId])) {
            $basket[$variantId] += $quantity;
        } else {
            $basket[$variantId] = $quantity;
        }

        $_SESSION['basket'] = $basket;

        return ['variantId' => $variantId, 'quantity' => $basket[$variantId]];
    }

    public function getProductById($id): ?Product
    {
        try {
            return $this->productRepository->getProductById($id);
        } catch (\Exception $e) {
            error_log("Failed to get product by ID: " . $e->getMessage());
            return null;
        }
    }



    public function createProductWithVariants(Product $product, array $variantsInput): array
    {
        $errors = array_merge(
            $this->validateProduct($product),
            $this->validateVariantsInput($variantsInput)
        );

        if (!empty($errors))
            return ['errors' => $errors];

        $rows = $this->normalizeVariantRows($variantsInput);

        $this->productRepository->beginTransaction();
        try {
            $productId = $this->productRepository->save($product);

            foreach ($rows as $row) {
                $variant = new ProductVariant(
                    null,
                    $productId,
                    $row['size'],
                    $row['colour'],
                    $row['stockQuantity']
                );
                $this->productRepository->saveVariant($variant);
            }

            $this->productRepository->commit();
            return ['errors' => []];
        } catch (\Throwable $e) {
            $this->productRepository->rollBack();
            error_log("Failed to save product and variants: " . $e->getMessage());
            return ['errors' => ['Failed to save product and variants.']];
        }
    }

    /* =========================
       Product admin 
       ========================= */

    public function updateProduct(Product $product): array
    {
        try {
            $errors = $this->validateProduct($product);
            if (!empty($errors))
                return ['error' => implode(' ', $errors)];

            $this->productRepository->update($product);
            return ['success' => 'Product updated successfully'];
        } catch (\Throwable $e) {
            error_log("Failed to update product: " . $e->getMessage());
            return ['error' => 'Failed to update product'];
        }
    }

    public function deleteProduct($id): array
    {
        try {
            $product = $this->productRepository->getProductById((int) $id);
            if (!$product)
                return ['error' => 'Product not found'];

            // IMPORTANT: your repository delete() should deactivate by productId
            $this->productRepository->delete((int) $id);

            return ['success' => 'Product deleted successfully'];
        } catch (\Throwable $e) {
            error_log("Failed to delete product: " . $e->getMessage());
            return ['error' => 'Failed to delete product'];
        }
    }

    /* =========================
       Variant admin API
       ========================= */

    public function getVariantsByProductId(int $productId): array
    {
        try {
            return $this->productRepository->getVariantsByProductId($productId);
        } catch (\Throwable $e) {
            error_log("Failed to load variants: " . $e->getMessage());
            return [];
        }
    }

    public function addVariantToProduct(ProductVariant $variant): array
    {
        $errors = $this->validateVariant($variant);
        if (!empty($errors))
            return ['error' => implode(' ', $errors)];

        try {
            $product = $this->productRepository->getProductById($variant->getProductId());
            if (!$product)
                return ['error' => 'Product not found'];

            $this->productRepository->saveVariant($variant);
            return ['success' => 'Variant added successfully'];
        } catch (\Throwable $e) {
            error_log("Failed to add variant: " . $e->getMessage());
            return ['error' => 'Failed to add variant'];
        }
    }


    // Wrapper so controllers can call updateVariant(id, size, colour, stock, price)
    public function updateVariantByFields(
        int $variantId,
        string $size,
        string $colour,
        int $stockQuantity,
        float $price
    ): array {
        $variant = $this->productRepository->getVariantById($variantId);
        if (!$variant)
            return ['error' => 'Variant not found'];

        // If your ProductVariant model does NOT have price, keep it 0 or ignore it
        $v = new ProductVariant(
            $variantId,
            (int) $variant->getProductId(),
            $size,
            $colour,
            $stockQuantity
        );

        return $this->updateVariant($v);
    }

    // Wrapper so controllers can call createVariant(productId, size, colour, stock, price)
    public function createVariantByFields(
        int $productId,
        string $size,
        string $colour,
        int $stockQuantity,
        float $price
    ): array {
        $v = new ProductVariant(
            null,
            $productId,
            $size,
            $colour,
            $stockQuantity
        );

        return $this->addVariantToProduct($v);
    }

    public function updateVariant(ProductVariant $variant): array
    {
        if ($variant->getVariantId() === null)
            return ['error' => 'VariantId is required'];

        $errors = $this->validateVariant($variant);
        if (!empty($errors))
            return ['error' => implode(' ', $errors)];

        try {
            $existing = $this->productRepository->getVariantById((int) $variant->getVariantId());
            if (!$existing)
                return ['error' => 'Variant not found'];

            // Repo must implement updateVariant(ProductVariant $variant)
            $this->productRepository->updateVariant($variant);

            return ['success' => 'Variant updated successfully'];
        } catch (\Throwable $e) {
            error_log("Failed to update variant: " . $e->getMessage());
            return ['error' => 'Failed to update variant'];
        }
    }

    public function deleteVariant(int $variantId): array
    {
        try {
            $existing = $this->productRepository->getVariantById($variantId);
            if (!$existing)
                return ['error' => 'Variant not found'];

            // Repo must implement deleteVariant(int $variantId)
            $this->productRepository->deleteVariant($variantId);

            return ['success' => 'Variant deleted successfully'];
        } catch (\Throwable $e) {
            error_log("Failed to delete variant: " . $e->getMessage());
            return ['error' => 'Failed to delete variant'];
        }
    }

    /* =========================
       Private helpers
       ========================= */

    private function validateProduct(Product $product): array
    {
        $errors = [];

        if (trim((string) $product->getName()) === '')
            $errors[] = 'Name is required.';
        if ((float) $product->getPrice() <= 0)
            $errors[] = 'Price must be greater than 0.';
        if ((int) $product->getStock() < 0)
            $errors[] = 'Stock cannot be negative.';

        return $errors;
    }

    private function validateVariantsInput(array $variantsInput): array
    {
        if (!is_array($variantsInput) || count($variantsInput) === 0) {
            return ['At least one variant is required.'];
        }

        $errors = [];
        foreach ($variantsInput as $idx => $v) {
            $size = trim((string) ($v['size'] ?? ''));
            $colour = trim((string) (($v['colour'] ?? $v['color']) ?? ''));
            $stock = (int) (($v['stockQuantity'] ?? $v['stock']) ?? 0);

            if ($size === '' || $colour === '') {
                $errors[] = "Variant #" . ($idx + 1) . ": size and color are required.";
            }
            if ($stock < 0) {
                $errors[] = "Variant #" . ($idx + 1) . ": stock must be 0 or more.";
            }
        }

        return $errors;
    }

    private function normalizeVariantRows(array $variantsInput): array
    {
        $rows = [];
        foreach ($variantsInput as $v) {
            $rows[] = [
                'size' => trim((string) ($v['size'] ?? '')),
                'colour' => trim((string) (($v['colour'] ?? $v['color']) ?? '')),
                'stockQuantity' => (int) (($v['stockQuantity'] ?? $v['stock']) ?? 0),
            ];
        }
        return $rows;
    }

    private function validateVariant(ProductVariant $variant): array
    {
        $errors = [];

        if ($variant->getProductId() <= 0)
            $errors[] = 'Variant productId is invalid.';
        if (trim((string) $variant->getSize()) === '')
            $errors[] = 'Variant size is required.';
        if (trim((string) $variant->getColour()) === '')
            $errors[] = 'Variant colour is required.';
        if ((int) $variant->getStockQuantity() < 0)
            $errors[] = 'Variant stockQuantity cannot be negative.';

        return $errors;
    }
}
