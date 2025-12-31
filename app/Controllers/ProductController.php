<?php

namespace App\Controllers;

use App\Models\Product;
use App\Services\ProductService;
use App\Core\ControllerBase;

class ProductController extends ControllerBase
{
    private ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }


    //////// For admin ///////
    public function addProductForm(): void
    {
        // Render the add product form (implementation depends on your templating system)
        $this->render('Products/AddProductForm', ['title' => 'Add Product',]);
    }
    public function addProduct(): void
    {
        $this->validateCsrf();

        // Extract path and error from the array
        [$imagePath, $imageError] = $this->saveUploadedProductImage($_FILES['image'] ?? []);

        // Check for upload errors
        if ($imageError !== null) {
            $this->jsonResponse(['errors' => [$imageError]], 400);
            return;
        }

        $product = new Product(
            null,
            trim($this->input('name', '')),
            trim($this->input('description', '')),
            (float) $this->input('price', 0.0),
            trim($this->input('category', '')),
            (int) $this->input('stock', 0),
            $imagePath, // <- Now just the string path, not the array
            null,
            null,
            true
        );

        $variants = $this->input('variants', []);
        try {
            $result = $this->productService->createProductWithVariants($product, $variants);

            if (!empty($result['errors'])) {
                $this->jsonResponse(['errors' => $result['errors']], 400);
                return;
            }

            $this->jsonResponse(['message' => 'Product + variants added successfully'], 201);
            return;
        } catch (\Throwable $e) {
            $this->jsonResponse(['errors' => ['An unexpected error occurred.']], 500);
            return;
        }
    }

    private function saveUploadedProductImage(array $file): array
    {

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE)
            return [null, null]; // image optional

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            return [null, 'File exceeds upload_max_filesize'];
        }

        $maxBytes = 5 * 1024 * 1024;
        if (($file['size'] ?? 0) > $maxBytes)
            return [null, 'Image must be 5MB or smaller.'];

        $tmp = $file['tmp_name'] ?? '';
        if ($tmp === '' || !is_uploaded_file($tmp))
            return [null, 'Invalid uploaded file.'];

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp);

        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        if (!isset($allowed[$mime]))
            return [null, 'Only JPG, PNG, or WEBP images are allowed.'];

        $dirFs = __DIR__ . '/../../Public/images/products';
        if (!is_dir($dirFs) && !mkdir($dirFs, 0755, true))
            return [null, 'Could not create image folder.'];

        $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
        $destFs = $dirFs . '/' . $name;

        if (!move_uploaded_file($tmp, $destFs))
            return [null, 'Could not save uploaded image.'];

        return ['/images/products/' . $name, null];
    }





    /////// For user-facing catalog ///////

    public function products(): void
    {
        $this->render('Products/ProductLists', ['title' => 'Products']);
    }

    public function productLists(): void
    {
        $productList = $this->productService->getActiveProducts();
        $products = array_map(fn($p) => [
            'productId' => $p->getId(),
            'productName' => $p->getName(),
            'description' => $p->getDescription(),
            'price' => $p->getPrice(),
            'category' => $p->getCategory(),
            'stock' => $p->getStock(),
            'image' => $p->getImage() ?: null,
        ], $productList);

        $this->jsonResponse($products, 200);
    }
    public function toggleFavourite(): void
    {
        try {
            $this->validateCsrf();
            $productId = $this->input('productId', 0);

            $toggle = $this->productService->
                toggleFavourite($productId);

            $this->jsonResponse($toggle, 200);
            return;
        } catch (\Throwable $e) {
            $this->jsonResponse(
                ['error' => 'An unexpected error occurred. Please try again.'],
                500
            );
            return;
        }
    }

    public function productDetails(int $id): void
    {
        try {
            // Add debugging
            error_log("=== DEBUG: Loading product ID: $id ===");

            // Get product details
            $result = $this->productService->getProductDetails($id);

            // Log the full result
            error_log("Result: " . print_r($result, true));

            // Check what's in the result
            error_log("Has errors: " . (empty($result['errors']) ? 'NO' : 'YES'));
            error_log("Product is null: " . ($result['product'] === null ? 'YES' : 'NO'));

            if (!empty($result['errors'])) {
                error_log("Errors found: " . print_r($result['errors'], true));
            }

            if ($result['product'] === null) {
                error_log("Product is NULL - will redirect");
            }

            if (!empty($result['errors']) || $result['product'] === null) {
                error_log("REDIRECTING to /products");
                header('Location: /products');
                exit;
            }

            $product = $result['product'];
            error_log("Product loaded: " . $product->getName());

            // Get similar products
            $similarProducts = $this->productService->getSimilarProducts(
                $id,
                $product->getCategory(),
                4
            );

            error_log("Similar products count: " . count($similarProducts));

            // Pass everything to the view
            $this->render('Products/ProductDetails', [
                'title' => $product->getName(),
                'product' => $product,
                'variants' => $result['variants'],
                'sizes' => $result['sizes'],
                'colors' => $result['colors'],
                'similarProducts' => $similarProducts,
            ]);

        } catch (\Throwable $e) {
            error_log("=== EXCEPTION in productDetails ===");
            error_log("Error: " . $e->getMessage());
            error_log("File: " . $e->getFile() . " Line: " . $e->getLine());
            error_log("Stack trace: " . $e->getTraceAsString());
            header('Location: /products');
            exit;
        }
    }

    public function viewProductDetail(int $productId): void
    {
        try {
            $result = $this->productService->getProductDetails($productId);

            // Check errors
            if (!empty($result['errors']) || $result['product'] === null) {
                $this->jsonResponse(['error' => 'Product not found'], 404);
                return;
            }

            // Get similar products
            $product = $result['product'];
            $similarProducts = $this->productService->getSimilarProducts(
                productId: $productId,
                category: $product->getCategory(),
                limit: 4
            );

            // Map response
            $response = $this->mapProductResponse($result);

            // Add similar products to response
            $response['similarProducts'] = array_map(fn($p) => [
                'productId' => $p->getId(),
                'name' => $p->getName(),
                'price' => $p->getPrice(),
                'category' => $p->getCategory(),
                'image' => $p->getImage() ?: '/images/placeholder.jpg',
            ], $similarProducts);

            $this->jsonResponse($response, 200);

        } catch (\Throwable $e) {
            error_log("Error: " . $e->getMessage());
            $this->jsonResponse(['error' => 'An unexpected error occurred'], 500);
        }
    }

    private function mapProductResponse(array $data): array
    {
        $product = $data['product'];

        return [
            'product' => [
                'productId' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'price' => $product->getPrice(),
                'category' => $product->getCategory(),
                'image' => $product->getImage() ?: '/images/placeholder.jpg',
            ],
            // Transform variants here!
            'variants' => array_map(
                fn($variant) => [
                    'variantId' => $variant['variant_id'] ?? $variant['variantId'],
                    'size' => $variant['size'],
                    'color' => $variant['color'],
                    'stock' => $variant['stock_quantity'] ?? $variant['stock'],
                ],
                $data['variants']
            ),
            'sizes' => $data['sizes'],
            'colors' => $data['colors'],
        ];
    }
    public function addToBasket(): void
    {
        try {
            $this->validateCsrf();

            $variantId = (int) $this->input('variantId', 0);

            $result = $this->productService->addToBasket(
                variantId: $variantId,
                quantity: 1
            );
            $this->jsonResponse($result, 200);
            return;

        } catch (\Throwable $e) {
            $this->jsonResponse(
                ['error' => 'An unexpected error occurred. Please try again later.'],
                500
            );
            return;
        }

    }


}

