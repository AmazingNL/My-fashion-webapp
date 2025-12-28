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
        $this->render('Product/AddProductForm', ['title' => 'Add Product', ]);
    }
    public function addProduct(): void
    {
        // Implementation for adding a product
        $product = new Product(
            null,
            trim($this->input('name', '')),
            trim($this->input('description', '')),
            (float) $this->input('price', 0.0),
            trim($this->input('category', '')),
            (int) $this->input('stock', 0),
            $this->input('image', null),
            null,
            null,
            true
        );

        try {
            $error = $this->productService->createProduct($product);
            if (!empty($error)) {
                $this->jsonResponse(['error' => $error], 400);
                return;
            }
            $this->jsonResponse(['message' => 'Product added successfully'], 201);
        } catch (\Throwable $e) {
            $this->jsonResponse(
                ['error' => 'An unexpected error occurred. Please try again later.'],
                500
            );
            return;
        }

    }


    /////// For user-facing catalog ///////


    // Renders the catalog view (HTML)
    public function products(): void
    {
        // CSRF for ajax POSTs
        if (session_status() !== PHP_SESSION_ACTIVE)
            session_start();
        $_SESSION['csrf'] ??= bin2hex(random_bytes(16));

        $this->render('products/catalog', [
            'csrf' => $_SESSION['csrf'], 'title' => 'Product Catalog',
        ]);
    }

    // Returns JSON: products + variants (+ favourited info if logged in/session)
    public function apiIndex(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE)
            session_start();

        $data = $this->productService->getCatalogDataForUser(
            userId: $_SESSION['user_id'] ?? null
        );

        $this->jsonResponse($data, 200);
    }

    public function toggleFavourite(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE)
            session_start();
        $this->requirePost();
        $this->requireCsrf();

        $productId = (int) ($_POST['productId'] ?? 0);
        if ($productId <= 0) {
            $this->jsonResponse(['errors' => ['Invalid product id']], 422);
            return;
        }

        $userId = $_SESSION['user_id'] ?? null;

        $result = $this->productService->toggleFavourite($userId, $productId);

        // returns: {productId, favourited:true/false}
        $this->jsonResponse($result, 200);
    }

    public function addToBasket(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE)
            session_start();
        $this->requirePost();
        $this->requireCsrf();

        $variantId = (int) ($_POST['variantId'] ?? 0);
        $qty = (int) ($_POST['qty'] ?? 1);
        $qty = max(1, min($qty, 10));

        if ($variantId <= 0) {
            $this->jsonResponse(['errors' => ['Choose a size and colour first']], 422);
            return;
        }

        $result = $this->productService->addVariantToBasket(
            userId: $_SESSION['user_id'] ?? null,
            variantId: $variantId,
            qty: $qty
        );

        // returns: {ok:true, basketCount:int}
        $this->jsonResponse($result, 200);
    }

    // ---- helpers ----
    private function requirePost(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $this->jsonResponse(['errors' => ['Method not allowed']], 405);
            exit;
        }
    }

    private function requireCsrf(): void
    {
        $token = (string) ($_POST['csrf'] ?? '');
        if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
            $this->jsonResponse(['errors' => ['CSRF check failed']], 403);
            exit;
        }
    }
}

