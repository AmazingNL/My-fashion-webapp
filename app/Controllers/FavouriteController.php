<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ControllerBase;
use App\Services\IProductService;
use Throwable;

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

        $successQuery = trim((string) $this->input('success', ''));
        $errorQuery = trim((string) $this->input('error', ''));
        if ($successQuery !== '' || $errorQuery !== '') {
            $this->setFlash('favourite', $successQuery !== '' ? $successQuery : $errorQuery, $errorQuery !== '' ? 'error' : 'success');
            $this->redirect('/favourites');
            return;
        }

        [$success, $error] = $this->consumeFlash('favourite');

        $products = $this->getFavouriteProductsForView();

        $this->render('Products/Favourites', [
            'title' => 'My Favourites',
            'products' => $products,
            'success' => $success,
            'error' => $error,
        ]);
    }

    public function toggleFavourite(): void
    {
        $this->validateCsrf();
        $this->ensureSession();

        $productId = (int) $this->input('productId', 0);
        $redirect = $this->resolveRedirectUrl();
        $cleanRedirect = $this->cleanRedirectUrl($redirect);
        $toFavourites = str_starts_with($cleanRedirect, '/favourites');

        if ($productId <= 0) {
            if ($toFavourites) {
                $this->setFlash('favourite', 'Invalid product id.', 'error');
                $this->redirect($cleanRedirect);
                return;
            }

            $this->redirect($this->appendQueryParam($redirect, 'error', 'Invalid product id.'));
            return;
        }

        $result = $this->productService->toggleFavourite($productId);

        if (!empty($result['error'])) {
            if ($toFavourites) {
                $this->setFlash('favourite', (string) $result['error'], 'error');
                $this->redirect($cleanRedirect);
                return;
            }

            $this->redirect($this->appendQueryParam($redirect, 'error', (string) $result['error']));
            return;
        }

        $favourited = (bool) ($result['favourited'] ?? false);
        $message = $favourited ? 'Added to favourites.' : 'Removed from favourites.';

        if ($toFavourites) {
            $this->setFlash('favourite', $message, 'success');
            $this->redirect($cleanRedirect);
            return;
        }

        $this->redirect($this->appendQueryParam($redirect, 'success', $message));
    }

    public function clearFavourites(): void
    {
        $this->validateCsrf();
        $this->ensureSession();

        $_SESSION['favourites'] = [];
        $this->setFlash('favourite', 'Favourites cleared.', 'success');
        $this->redirect('/favourites');
    }

    private function getFavouriteProductsForView(): array
    {
        $ids = $this->getFavouriteIds();
        if (empty($ids)) {
            return [];
        }

        try {
            $allProducts = $this->productService->getActiveProducts();
        } catch (Throwable $e) {
            return [];
        }

        $rows = [];
        foreach ($allProducts as $product) {
            $mapped = $this->mapProduct($product);
            if (in_array((int) ($mapped['productId'] ?? 0), $ids, true)) {
                $rows[] = $mapped;
            }
        }

        return $rows;
    }

    private function getFavouriteIds(): array
    {
        $favourites = $_SESSION['favourites'] ?? [];
        return array_map('intval', array_keys($favourites));
    }

    private function mapProduct($product): array
    {
        if (is_object($product)) {
            return [
                'productId' => (int) $product->getId(),
                'productName' => (string) $product->getName(),
                'category' => (string) $product->getCategory(),
                'price' => (float) $product->getPrice(),
                'image' => (string) $product->getImage(),
                'stock' => (int) $product->getStock(),
            ];
        }

        return [
            'productId' => (int) ($product['productId'] ?? 0),
            'productName' => (string) ($product['productName'] ?? 'Product'),
            'category' => (string) ($product['category'] ?? ''),
            'price' => (float) ($product['price'] ?? 0),
            'image' => (string) ($product['image'] ?? ''),
            'stock' => (int) ($product['stock'] ?? 0),
        ];
    }

    private function resolveRedirectUrl(): string
    {
        $redirect = trim((string) $this->input('redirect', ''));
        if ($redirect === '') {
            $redirect = (string) ($_SERVER['HTTP_REFERER'] ?? '/favourites');
        }

        if (!str_starts_with($redirect, '/')) {
            $path = (string) (parse_url($redirect, PHP_URL_PATH) ?? '/favourites');
            $query = (string) (parse_url($redirect, PHP_URL_QUERY) ?? '');
            $redirect = $path . ($query !== '' ? '?' . $query : '');
        }

        return $redirect !== '' ? $redirect : '/favourites';
    }

    private function appendQueryParam(string $url, string $key, string $value): string
    {
        $path = (string) (parse_url($url, PHP_URL_PATH) ?? '/favourites');
        $query = [];
        parse_str((string) (parse_url($url, PHP_URL_QUERY) ?? ''), $query);
        $query[$key] = $value;

        return $path . '?' . http_build_query($query);
    }

    private function cleanRedirectUrl(string $url): string
    {
        $path = (string) (parse_url($url, PHP_URL_PATH) ?? '/favourites');
        $query = [];
        parse_str((string) (parse_url($url, PHP_URL_QUERY) ?? ''), $query);
        unset($query['success'], $query['error']);

        $queryString = http_build_query($query);
        return $path . ($queryString !== '' ? '?' . $queryString : '');
    }

}
