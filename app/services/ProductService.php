<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Repositories\IProductRepository;
use App\Services\IProductService;

class ProductService implements IProductService
{
    private IProductRepository $productRepository;

    public function __construct(IProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function getProductDetails($id): array
    {
        try {
            // Step 1: Get the product
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

            // Step 2: Get variants
            $variants = $this->productRepository->getVariantsByProductId($id);

            // FIXED: Return consistent structure even with no variants
            if (empty($variants)) {
                error_log("WARNING: Product ID $id has no variants");
                return [
                    'product' => $product,  // FIXED: Include product
                    'variants' => [],
                    'sizes' => [],
                    'colors' => [],
                    'errors' => ['This product has no size/color options available'],
                ];
            }

            // Step 3: Extract unique sizes and colors
            $sizes = [];
            $colors = [];

            foreach ($variants as $variant) {
                // FIXED: Variants are now ProductVariant objects, not arrays
                $sizes[$variant->getSize()] = true;
                $colors[$variant->getColour()] = true;  // or use getColor() - both work
            }

            return [
                'product' => $product,
                'variants' => $variants,
                'sizes' => array_keys($sizes),
                'colors' => array_keys($colors),
                'errors' => [],
            ];

        } catch (\Throwable $e) {
            error_log("Exception in getProductDetails: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());

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
            return $this->productRepository->findSimilarProducts(
                $productId,
                $category,
                $limit
            );
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
        } else {
            $session[$productId] = true;
            $_SESSION['favourites'] = $session;
            return ['productId' => $productId, 'favourited' => true];
        }
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

    public function createProductWithVariants(Product $product, array $variantsInput): array
    {
        $errors = [];

        // Validate product
        if (trim((string) $product->getName()) === '')
            $errors[] = 'Name is required.';
        if ($product->getPrice() <= 0)
            $errors[] = 'Price must be greater than 0.';
        if ($product->getStock() < 0)
            $errors[] = 'Stock cannot be negative.';

        $rows = $variantsInput;
        if (!is_array($rows) || count($rows) === 0) {
            $errors[] = 'At least one variant is required.';
        }

        if (!$errors) {
            foreach ($rows as $idx => $v) {
                $size = trim((string) ($v['size'] ?? ''));
                $colour = trim((string) (($v['colour'] ?? $v['color']) ?? ''));
                $vStock = (int) (($v['stockQuantity'] ?? $v['stock']) ?? 0);

                if ($size === '' || $colour === '') {
                    $errors[] = "Variant #" . ($idx + 1) . ": size and color are required.";
                }
                if ($vStock < 0) {
                    $errors[] = "Variant #" . ($idx + 1) . ": stock must be 0 or more.";
                }
            }
        }

        if ($errors)
            return ['errors' => $errors];

        $count = count($rows);

        // Transaction: product + variants
        $this->productRepository->beginTransaction();
        try {
            $productId = $this->productRepository->save($product);

            for ($i = 0; $i < $count; $i++) {
                $v = $rows[$i];
                $size = trim((string) $v['size']);
                $colour = trim((string) (($v['colour'] ?? $v['color']) ?? ''));
                $vStock = (int) (($v['stockQuantity'] ?? $v['stock']) ?? 0);

                $variant = new ProductVariant(
                    null,
                    $productId,
                    $size,
                    $colour,
                    $vStock
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

    public function getProductById($id): ?Product
    {
        try {
            return $this->productRepository->getProductById($id);
        } catch (\Exception $e) {
            error_log("Failed to get product by ID: " . $e->getMessage());
            return null;
        }
    }


    public function updateProduct(Product $product): array
    {
        try {
            $this->productRepository->update($product);
            return ['success' => 'Product updated successfully'];
        } catch (\Exception $e) {
            error_log("Failed to update product: " . $e->getMessage());
            return ['error' => 'Failed to update product'];
        }
    }

    public function deleteProduct($id): array
    {
        try {
            $this->productRepository->getProductById($id);
            return ['success' => 'Product deleted successfully'];
        } catch (\Exception $e) {
            error_log("Failed to delete product: " . $e->getMessage());
            return ['error' => 'Product not found'];
        }
    }
}