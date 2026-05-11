<?php

namespace App\Controllers;

use App\Mappers\ProductResponseMapper;
use App\Services\IProductService;
use App\Core\ControllerBase;

class ProductController extends ControllerBase
{
    private IProductService $productService;

    public function __construct(IProductService $productService)
    {
        $this->productService = $productService;
    }


    public function productLists(): void
    {
        try {
            $this->jsonResponse($this->success(ProductResponseMapper::mapToProductListDto(
                $this->productService->getProductListData($_GET)
            )));
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error(
                'Failed to load products.',
                [],
                ProductResponseMapper::mapToProductListDto($this->productService->getEmptyProductListData())
            ), 500);
        }
    }

    public function productDetails(int $id): void
    {
        try {
            $result = $this->productService->getProductDetails($id);
            $product = $this->requireProductOr404($result);
            if (!$product) {
                $this->jsonResponse($this->error('Product not found.'), 404);
            }
            $variants = $result['variants'] ?? [];

            $this->jsonResponse($this->success(ProductResponseMapper::mapToProductDetailsDto($product, $variants)));
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error('Product not found.'), 404);
        }
    }

    private function requireProductOr404(array $result): mixed
    {
        $product = $result['product'] ?? null;
        $errors = $result['errors'] ?? [];
        if ($product === null || !empty($errors)) {
            return null;
        }
        return $product;
    }

}

