<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Repositories\IProductRepository;
use Exception;

class ProductService implements IProductService
{
    private IProductRepository $productRepository;

    public function __construct(IProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }


    public function getProductDetails(int $id): array
    {
        return $this->productRepository->getProductDetailsById($id);
    }


    public function getActiveProducts(): array
    {
        return $this->productRepository->getAllActive();
    }


    public function toggleFavourite($productId): array
    {
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
        return $this->productRepository->getProductById((int) $id);

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
                    0,
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

            if (!$this->productRepository->update($product)) {
                return ['error' => 'Product not found or not updated'];
            }
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
            if (!$this->productRepository->delete((int) $id)) {
                return ['error' => 'Product not found or already inactive'];
            }

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
            $product = $this->productRepository->getProductById($variant->productId);
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
        if (!$variant) {
            return ['error' => 'Variant not found'];
        }

        // If your ProductVariant model does NOT have price, keep it 0 or ignore it
        $v = new ProductVariant(
            $variantId,
            (int) $variant->productId,
            $size,
            $colour,
            $stockQuantity
        );

        return $this->updateVariant($v);
    }

    public function createVariantByFields(
        int $productId,
        string $size,
        string $colour,
        int $stockQuantity,
        float $price
    ): array {
        return $this->addVariantToProduct(new ProductVariant(0, $productId, $size, $colour, $stockQuantity));
    }

    public function updateVariant(ProductVariant $variant): array
    {
        if ($variant->variantId <= 0) {
            return ['error' => 'VariantId is required'];
        }

        $errors = $this->validateVariant($variant);
        if (!empty($errors)) {
            return ['error' => implode(' ', $errors)];
        }

        try {
            if (!$this->productRepository->updateVariant($variant)) {
                return ['error' => 'Variant not found or not updated'];
            }

            return ['success' => 'Variant updated successfully'];
        } catch (\Throwable $e) {
            error_log("Failed to update variant: " . $e->getMessage());
            return ['error' => 'Failed to update variant'];
        }
    }

    public function deleteVariant(int $variantId): array
    {
        try {
            if (!$this->productRepository->deleteVariant($variantId)) {
                return ['error' => 'Variant not found or already deleted'];
            }

            return ['success' => 'Variant deleted successfully'];
        } catch (\Throwable $e) {
            error_log("Failed to delete variant: " . $e->getMessage());
            return ['error' => 'Failed to delete variant'];
        }
    }

    private function validateProduct(Product $product): array
    {
        $errors = [];

        if (trim((string) $product->productName) === '')
            $errors[] = 'Name is required.';
        if ((float) $product->price <= 0)
            $errors[] = 'Price must be greater than 0.';
        if ((int) $product->stock < 0)
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

        if ($variant->productId <= 0) {
            $errors[] = 'Variant productId is invalid.';
        }
        if (trim((string) $variant->size) === '') {
            $errors[] = 'Variant size is required.';
        }
        if (trim((string) $variant->colour) === '') {
            $errors[] = 'Variant colour is required.';
        }
        if ((int) $variant->stockQuantity < 0) {
            $errors[] = 'Variant stockQuantity cannot be negative.';
        }

        return $errors;
    }

    private function extractSizesColors(array $variants): array
    {
        $sizes = $colors = [];

        foreach ($variants as $v) {
            $size = is_object($v) ? (string) ($v->size ?? '') : (string) ($v['size'] ?? '');
            $colour = is_object($v) ? (string) ($v->colour ?? ($v->color ?? '')) : (string) ($v['colour'] ?? ($v['color'] ?? ''));

            if ($size !== '')
                $sizes[$size] = true;
            if ($colour !== '')
                $colors[$colour] = true;
        }

        return [array_keys($sizes), array_keys($colors)];
    }

}
