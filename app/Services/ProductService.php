<?php

namespace App\Services;

use App\DTO\ProductRequestDto;
use App\DTO\ProductVariantRequestDto;
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


    public function getProductDetails(int $id): array
    {
        return $this->productRepository->getProductDetailsById($id);
    }


    public function getActiveProducts(): array
    {
        return $this->productRepository->getAllActive();
    }

    public function getProductListData(array $query): array
    {
        $pageSize = max(1, (int) ($query['pageSize'] ?? 12));
        $search = trim((string) ($query['search'] ?? ''));
        $selectedCategory = $this->selectedCategory($query['category'] ?? '');
        $minPrice = isset($query['minPrice']) && $query['minPrice'] !== '' ? (float) $query['minPrice'] : null;
        $maxPrice = isset($query['maxPrice']) && $query['maxPrice'] !== '' ? (float) $query['maxPrice'] : null;

        $products = $this->getActiveProducts();
        $filterCategories = $this->extractFilterCategories($products);
        $filteredProducts = $this->filterProducts($products, $search, $selectedCategory, $minPrice, $maxPrice);
        $totalCount = count($filteredProducts);
        $totalPages = max(1, (int) ceil($totalCount / $pageSize));
        $page = min(max(1, (int) ($query['page'] ?? 1)), $totalPages);
        $offset = ($page - 1) * $pageSize;
        $pagedProducts = array_slice($filteredProducts, $offset, $pageSize);

        return [
            'products' => $pagedProducts,
            'pagination' => [
                'totalCount' => $totalCount,
                'currentPage' => $page,
                'pageSize' => $pageSize,
                'totalPages' => $totalPages,
            ],
            'filters' => [
                'filterCategories' => $filterCategories,
                'currentFilters' => [
                    'search' => $search,
                    'category' => $selectedCategory,
                    'minPrice' => $minPrice,
                    'maxPrice' => $maxPrice,
                    'pageSize' => $pageSize,
                ],
            ],
        ];
    }

    public function getEmptyProductListData(): array
    {
        return [
            'products' => [],
            'pagination' => [
                'totalCount' => 0,
                'currentPage' => 1,
                'pageSize' => 10,
                'totalPages' => 1,
            ],
            'filters' => [
                'filterCategories' => [],
                'currentFilters' => [],
            ],
        ];
    }


    public function toggleFavourite($productId): array
    {
        $productId = (int) $productId;
        if ($productId <= 0) {
            return ['error' => 'Invalid product id.'];
        }

        if ($this->getProductById($productId) === null) {
            return ['error' => 'Product not found.'];
        }

        return ['error' => 'Favourites require persistent storage.'];
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
            return ['errors' => [], 'productId' => $productId];
        } catch (\Throwable $e) {
            $this->productRepository->rollBack();
            error_log("Failed to save product and variants: " . $e->getMessage());
            return ['errors' => ['Failed to save product and variants.']];
        }
    }

    /* =========================
        Product admin 
       ========================= */

    public function saveUploadedProductImage(array $file): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return [null, null];
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            return [null, 'File exceeds upload limit.'];
        }

        $maxBytes = 5 * 1024 * 1024;
        if (($file['size'] ?? 0) > $maxBytes) {
            return [null, 'Image must be 5MB or smaller.'];
        }

        $tmp = $file['tmp_name'] ?? '';
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            return [null, 'Invalid uploaded file.'];
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file($tmp);

        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if (!isset($allowed[$mime])) {
            return [null, 'Only JPG, PNG, or WEBP images are allowed.'];
        }

        $dirFs = dirname(__DIR__, 2) . '/public/images/products';
        if (!is_dir($dirFs) && !mkdir($dirFs, 0755, true)) {
            return [null, 'Could not create image folder.'];
        }

        $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
        $destFs = $dirFs . '/' . $name;

        if (!move_uploaded_file($tmp, $destFs)) {
            return [null, 'Could not save uploaded image.'];
        }

        return ['/images/products/' . $name, null];
    }

    public function resolveProductImagePath($existing, array $file): ?string
    {
        [$newImagePath, $imageError] = $this->saveUploadedProductImage($file);
        if ($imageError !== null) {
            throw new \RuntimeException($imageError);
        }

        if ($newImagePath !== null) {
            return $newImagePath;
        }

        if (is_array($existing)) {
            return (string) ($existing['image'] ?? '');
        }

        if (is_object($existing) && method_exists($existing, 'getImage')) {
            return (string) $existing->getImage();
        }

        return is_object($existing) ? (string) ($existing->image ?? '') : null;
    }

    public function validateProductRequest(ProductRequestDto $dto): array
    {
        if ($dto->productName === '' || $dto->category === '') {
            return ['Product name and category are required.'];
        }
        if ($dto->price < 0) {
            return ['Price cannot be negative.'];
        }
        if ($dto->stock < 0) {
            return ['Stock cannot be negative.'];
        }

        return [];
    }

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

    public function applyVariantChanges(int $productId, array $variantRows): void
    {
        foreach ($variantRows as $row) {
            if (!$row instanceof ProductVariantRequestDto) {
                continue;
            }

            if ($this->shouldDeleteVariant($row)) {
                $this->deleteVariant($row->variantId);
                continue;
            }

            if ($this->isVariantRowSkippable($row) || $this->isVariantRowInvalid($row)) {
                continue;
            }

            if ($row->variantId > 0) {
                $this->updateVariantByFields(
                    $row->variantId,
                    $row->size,
                    $row->colour,
                    $row->stock,
                    $row->price
                );
            } else {
                $this->createVariantByFields(
                    $productId,
                    $row->size,
                    $row->colour,
                    $row->stock,
                    $row->price
                );
            }
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

    private function selectedCategory($categoryInput): string
    {
        if (is_array($categoryInput)) {
            return implode(',', array_values(array_filter(array_map(static fn($item): string => trim((string) $item), $categoryInput))));
        }

        return trim((string) $categoryInput);
    }

    private function filterProducts(array $products, string $search, string $category, ?float $minPrice, ?float $maxPrice): array
    {
        $selectedCategories = array_values(array_filter(array_map('trim', explode(',', strtolower($category)))));
        return array_values(array_filter($products, function ($product) use ($search, $selectedCategories, $minPrice, $maxPrice): bool {
            $name = strtolower((string) ($product['productName'] ?? ''));
            $description = strtolower((string) ($product['description'] ?? ''));
            $productCategory = strtolower((string) ($product['category'] ?? ''));
            $price = (float) ($product['price'] ?? 0);
            if ($search !== '') {
                $field = strtolower($search);
                if (!str_contains($name, $field) && !str_contains($description, $field) 
                    && !str_contains($productCategory, $field) && !str_contains((string) $price, $field)) {
                    return false;
                }
            }
            if (!empty($selectedCategories) && !in_array($productCategory, $selectedCategories, true)) {
                return false;
            }
            if ($minPrice !== null && $price < $minPrice) {
                return false;
            }
            if ($maxPrice !== null && $price > $maxPrice) {
                return false;
            }
            return true;
        }));
    }

    private function extractFilterCategories(array $products): array
    {
        $categories = [];
        foreach ($products as $product) {
            $category = trim((string) ($product['category'] ?? ''));
            if ($category !== '') {
                $categories[$category] = $category;
            }
        }
        return array_values($categories);
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

    private function shouldDeleteVariant(ProductVariantRequestDto $row): bool
    {
        return $row->variantId > 0 && $row->delete;
    }

    private function isVariantRowSkippable(ProductVariantRequestDto $row): bool
    {
        return $row->size === '' && $row->colour === '' && $row->variantId === 0;
    }

    private function isVariantRowInvalid(ProductVariantRequestDto $row): bool
    {
        if ($row->size === '' || $row->colour === '') {
            return true;
        }

        if ($row->stock < 0) {
            return true;
        }

        return $row->price < 0;
    }

}
