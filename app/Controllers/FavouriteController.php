<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ControllerBase;
use App\Services\Interfaces\IProductService;

class FavouriteController extends ControllerBase
{

    private IProductService $productService;

    public function __construct(IProductService $productService)
    {
        $this->productService = $productService;
    }

    public function viewFavourites(): void
    {
        try {
            $this->jsonResponse($this->success([
                'products' => [],
                'count' => 0,
            ]));
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error('Failed to load favourites.', ['detail' => $e->getMessage()]), 500);
        }
    }

    public function toggleFavourite(): void
    {
        try {
            $productId = (int) $this->input('productId', 0);

            if ($productId <= 0) {
                $this->jsonResponse($this->error('Invalid product id.'), 422);
            }

            $result = $this->productService->toggleFavourite($productId);

            if (!empty($result['error'])) {
                $this->jsonResponse($this->error((string) $result['error']), 422);
            }

            $favourited = (bool) ($result['favourited'] ?? false);
            $message = $favourited ? 'Added to favourites.' : 'Removed from favourites.';

            $this->jsonResponse($this->success([
                'productId' => $productId,
                'favourited' => $favourited,
            ], $message));
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error('Failed to update favourite.', ['detail' => $e->getMessage()]), 500);
        }
    }

    public function clearFavourites(): void
    {
        try {
            $this->jsonResponse($this->success([
                'products' => [],
            ], 'Favourites cleared.'));
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error('Failed to clear favourites.', ['detail' => $e->getMessage()]), 500);
        }
    }

}
