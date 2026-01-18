<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ControllerBase;
use App\Services\IProductService;
use Exception;

class FavouriteController extends ControllerBase
{

    private IProductService $productService;

    public function __construct(IProductService $productService)
    {
        $this->productService = $productService;
    }

    public function viewFavourites(): void
    {
        $this->ensureSession();

        $this->render('Products/Favourites', [
            'title' => 'My Favourites'
        ]);
    }

    public function getFavouriteList(): void
    {
        $this->ensureSession();

        $favourites = $_SESSION['favourites'] ?? [];
        $ids = array_map('intval', array_keys($favourites));

        $this->jsonResponse([
            'success' => true,
            'favourites' => $ids
        ]);
    }

    public function getFavouriteProducts(): void
    {
        $this->ensureSession();

        $favourites = $_SESSION['favourites'] ?? [];
        if (empty($favourites)) {
            $this->jsonResponse(['success' => true, 'products' => []]);
            return;
        }

        try {
            $allProducts = $this->productService->getActiveProducts();

            $favouriteProducts = array_filter($allProducts, function ($product) use ($favourites) {
                $productId = is_object($product) ? $product->getId() : ($product['productId'] ?? null);
                return $productId !== null && isset($favourites[(int) $productId]);
            });

            $productsArray = array_map(function ($product) {
                if (is_object($product)) {
                    return [
                        'productId' => $product->getId(),
                        'productName' => $product->getName(),
                        'category' => $product->getCategory(),
                        'price' => $product->getPrice(),
                        'image' => $product->getImage(),
                        'stock' => $product->getStock(),
                    ];
                }
                return $product;
            }, array_values($favouriteProducts));

            $this->jsonResponse([
                'success' => true,
                'products' => $productsArray
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'products' => [],
                'error' => 'Failed to load favourites'
            ], 500);
        }
    }

    public function toggleFavourite(): void
    {
        $this->validateCsrf();
        $this->ensureSession();

        $productId = (int) $this->input('productId', 0);
        if ($productId <= 0) {
            $this->jsonResponse(['error' => 'Invalid productId'], 400);
            return;
        }

        $result = $this->productService->toggleFavourite($productId);

        if (!empty($result['error'])) {
            $this->jsonResponse(['error' => $result['error']], 400);
            return;
        }

        $this->jsonResponse([
            'success' => true,
            'productId' => $productId,
            'favourited' => (bool) ($result['favourited'] ?? false),
        ]);
    }


    public function getFavouriteCount(): void
    {
        $this->ensureSession();

        $favourites = $_SESSION['favourites'] ?? [];
        $this->jsonResponse(['success' => true, 'count' => count($favourites)]);
    }


}
