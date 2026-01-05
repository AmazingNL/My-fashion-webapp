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

    public function productDetails(int $id): void
    {
        // Simply render the HTML view - no data needed
        // JavaScript will fetch the data from the API endpoint
        $this->render('Products/ProductDetails', [
            'title' => 'Product Details'
        ]);
    }

    /**
     * API endpoint to fetch product details as JSON
     * Route: GET /api/products/{id}
     */
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
                'productName' => $p->getName(),
                'price' => $p->getPrice(),
                'category' => $p->getCategory(),
                'image' => $p->getImage() ?: '/assets/images/placeholder.jpg',
            ], $similarProducts);

            $this->jsonResponse($response, 200);

        } catch (\Throwable $e) {
            error_log($e); // or error_log($e->getMessage());
            $this->jsonResponse([
                'error' => $e->getMessage(),
                'type' => get_class($e),
            ], 500);
        }

    }

    /**
     * Helper method to map product data to response format
     */
    private function mapProductResponse(array $result): array
    {
        $product = $result['product'];
        $variants = $result['variants'] ?? [];

        return [
            'productId' => $product->getId(),
            'productName' => $product->getName(),
            'description' => $product->getDescription(),
            'price' => $product->getPrice(),
            'category' => $product->getCategory(),
            'image' => $product->getImage() ?: '/assets/images/placeholder.jpg',
            'variants' => array_map(fn($v) => [
                'variantId' => $v->getVariantId(),
                'size' => $v->getSize(),
                'colour' => $v->getColour(),
                'stockQuantity' => $v->getStockQuantity(),
            ], $variants),
        ];
    }

}

