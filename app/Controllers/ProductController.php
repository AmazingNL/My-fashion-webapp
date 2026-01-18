<?php

namespace App\Controllers;

use App\Services\IProductService;
use App\Core\ControllerBase;

class ProductController extends ControllerBase
{
    private IProductService $productService;

    public function __construct(IProductService $productService)
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
            $product = $this->requireProductOr404($result);
            $response = $this->mapProductResponse($result);
            $response['similarProducts'] = $this->similarProductsPayload($productId, $product->getCategory());
            $this->jsonResponse($response, 200);
        } catch (\Throwable $e) {
            $this->handleApiException($e);
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

    private function requireProductOr404(array $result)
    {
        $product = $result['product'] ?? null;
        $errors = $result['errors'] ?? [];

        if ($product === null || !empty($errors)) {
            $this->jsonResponse(['error' => 'Product not found'], 404);
            exit; // or return; if your jsonResponse() already stops execution
        }
        return $product;
    }

    private function similarProductsPayload(int $productId, string $category): array
    {
        $similar = $this->productService->getSimilarProducts(
            productId: $productId,
            category: $category,
            limit: 4
        );

        return array_map([$this, 'mapSimilarProduct'], $similar);
    }

    private function mapSimilarProduct($p): array
    {
        return [
            'productId' => $p->getId(),
            'productName' => $p->getName(),
            'price' => $p->getPrice(),
            'category' => $p->getCategory(),
            'image' => $p->getImage() ?: '/assets/images/placeholder.jpg',
        ];
    }

    private function handleApiException(\Throwable $e): void
    {
        error_log((string) $e);
        $this->jsonResponse(['error' => 'Server error'], 500);
    }


}

