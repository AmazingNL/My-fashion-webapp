<?php

namespace App\Controllers;

use App\Services\IProductService;
use App\Core\ControllerBase;
use App\ViewModel\ProductDetailsVM;
use App\ViewModel\ProductListVM;

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
            $this->renderProductLists(
                $this->buildProductListViewData()
            );
        } catch (\Throwable $e) {
            $this->renderProductLists(
                $this->buildEmptyProductListViewData()
            );
        }
    }

    public function productDetails(int $id): void
    {
        try {
            $result = $this->productService->getProductDetails($id);
            $product = $this->requireProductOr404($result);
            $variants = $result['variants'] ?? [];
            $productDetailsVm = new ProductDetailsVM(
                $product,
                $variants                
            );
            $this->render('Products/ProductDetails', [
                'title' => 'Product Details',
                'productDetailsVm' => $productDetailsVm,
            ]);
        } catch (\Throwable $e) {
            $this->render('Products/ProductDetails', [
                'title' => 'Product Not Found',
                'productDetailsVm' => new ProductDetailsVM()
            ]);
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

    private function filterProducts(array $products, string $search, string $category, ?float $minPrice, ?float $maxPrice): array
    {
        $selectedCategories = array_values(array_filter(array_map('trim', explode(',', strtolower($category)))));
        return array_values(array_filter($products, function ($product) use ($search, $selectedCategories, $minPrice, $maxPrice): bool {
            $name = strtolower((string) ($product['productName'] ?? ''));
            $description = strtolower((string) ($product['description'] ?? ''));
            $productCategory = strtolower((string) ($product['category'] ?? ''));
            $price = (float) ($product['price'] ?? 0);
            if ($search !== '') {
                $filed = strtolower($search);
                if (!str_contains($name, $filed) && !str_contains($description, $filed) && !str_contains($productCategory, $filed) && !str_contains((string) $price, $filed)) {
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

    private function buildProductListViewData(): array
    {
        $pageSize = max(1, (int) ($_GET['pageSize'] ?? 12));
        $search = trim((string) ($_GET['search'] ?? ''));
        $categoryInput = $_GET['category'] ?? '';
        if (is_array($categoryInput)) {
            $selectedCategory = implode(',', array_values(array_filter(array_map(static fn($item): string => trim((string) $item), $categoryInput))));
        } else {
            $selectedCategory = trim((string) $categoryInput);
        }
        $minPrice = isset($_GET['minPrice']) && $_GET['minPrice'] !== '' ? (float) $_GET['minPrice'] : null;
        $maxPrice = isset($_GET['maxPrice']) && $_GET['maxPrice'] !== '' ? (float) $_GET['maxPrice'] : null;

        $products = $this->productService->getActiveProducts();
        $filterCategories = $this->extractFilterCategories($products);
        $filteredProducts = $this->filterProducts($products, $search, $selectedCategory, $minPrice, $maxPrice);
        $totalCount = count($filteredProducts);
        $totalPages = max(1, (int) ceil($totalCount / $pageSize));
        $page = min(max(1, (int) ($_GET['page'] ?? 1)), $totalPages);
        $offset = ($page - 1) * $pageSize;
        $pagedProducts = array_slice($filteredProducts, $offset, $pageSize);

        return [
            'title' => 'Products',
            'productListVm' => new ProductListVM($pagedProducts, $totalCount, $page, $pageSize),
            'filterCategories' => $filterCategories,
            'currentFilters' => [
                'search' => $search,
                'category' => $selectedCategory,
                'minPrice' => $minPrice,
                'maxPrice' => $maxPrice,
                'pageSize' => $pageSize,
            ],
        ];
    }

    private function buildEmptyProductListViewData(): array
    {
        return [
            'title' => 'Products',
            'productListVm' => new ProductListVM(),
            'filterCategories' => [],
            'currentFilters' => [],
        ];
    }

    private function renderProductLists(array $viewData): void
    {
        $this->render('Products/ProductLists', $viewData);
    }

}

