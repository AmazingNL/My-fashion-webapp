<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ControllerBase;
use App\Services\CartService;
use App\Services\ActivityLogService;
use Exception;

class CartController extends ControllerBase
{
    private CartService $cartService;
    private ActivityLogService $logService;

    public function __construct(CartService $cartService, ActivityLogService $logService)
    {
        $this->cartService = $cartService;
        $this->logService  = $logService;
    }

    // ==================== VIEW ====================

    public function viewCart(): void
    {
        $this->render('Cart/ViewCart', [
            'title'     => 'Shopping Cart',
            'cartItems' => $this->cartService->getCartItems(),
            'total'     => $this->cartService->getTotalPrice(),
            'itemCount' => $this->cartService->getItemCount(),
            'isEmpty'   => $this->cartService->isEmpty(),
        ]);
    }

    // ==================== AJAX ====================

    public function addToBasket(): void
    {
        $this->validateCsrf();

        try {
            [$productId, $variantId, $quantity] = $this->readAddInputs();
            $this->assertValidAddInputs($productId, $variantId, $quantity);

            $this->cartService->addItem($productId, $variantId, $quantity);
            $this->logCartAction('Added to Cart', $productId, $variantId, "Quantity: {$quantity}");

            $this->jsonResponse($this->successPayload('Added to basket', $variantId));
        } catch (Exception $e) {
            $this->jsonResponse($this->errorPayload($e->getMessage()), 400);
        }
    }

    public function updateQuantity(): void
    {
        $this->validateCsrf();

        try {
            [$productId, $variantId, $quantity] = $this->readAddInputs();
            $this->assertValidAddInputs($productId, $variantId, $quantity);

            $result = $this->cartService->updateQuantity($productId, $variantId, $quantity);
            if (!$result) {
                $this->jsonResponse($this->errorPayload('Item not found in cart'), 404);
                return;
            }

            $action = $quantity === 0 ? 'Removed from Cart' : 'Updated Cart Quantity';
            $this->logCartAction($action, $productId, $variantId, "New quantity: {$quantity}");

            $message = $quantity === 0 ? 'Item removed' : 'Cart updated';
            $this->jsonResponse($this->successPayload($message, $variantId));
        } catch (Exception $e) {
            $this->jsonResponse($this->errorPayload($e->getMessage()), 400);
        }
    }

    public function removeFromBasket(): void
    {
        $this->validateCsrf();

        try {
            [$productId, $variantId] = $this->readRemoveInputs();
            $this->assertValidRemoveInputs($productId, $variantId);

            $result = $this->cartService->removeItem($productId, $variantId);
            if (!$result) {
                $this->jsonResponse($this->errorPayload('Item not found in cart'), 404);
                return;
            }

            $this->logCartAction('Removed from Cart', $productId, $variantId, '');
            $this->jsonResponse($this->successPayload('Item removed', $variantId));
        } catch (Exception $e) {
            $this->jsonResponse($this->errorPayload($e->getMessage()), 400);
        }
    }

    public function clearBasket(): void
    {
        $this->validateCsrf();

        try {
            $this->cartService->clearCart();
            $this->logCartAction('Cleared Cart', 0, 0, 'All items removed from cart');

            $this->jsonResponse([
                'success' => true,
                'message' => 'Cart cleared',
                'count'   => 0,
                'total'   => 0,
            ]);
        } catch (Exception $e) {
            $this->jsonResponse($this->errorPayload($e->getMessage()), 500);
        }
    }

    public function getBasketCount(): void
    {
        try {
            $this->jsonResponse([
                'success' => true,
                'count'   => $this->cartService->getItemCount(),
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'count'   => 0,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function validateCart(): void
    {
        $errors = $this->cartService->validateCart();

        if (!$errors) {
            $this->jsonResponse(['success' => true, 'message' => 'Cart is valid']);
            return;
        }

        $this->jsonResponse(['success' => false, 'errors' => $errors], 400);
    }

    // ==================== PRIVATE HELPERS ====================

    private function readAddInputs(): array
    {
        return [
            (int) $this->input('productId', 0),
            (int) $this->input('variantId', 0),
            (int) $this->input('quantity', 1),
        ];
    }


    private function readRemoveInputs(): array
    {
        return [
            (int) $this->input('productId', 0),
            (int) $this->input('variantId', 0),
        ];
    }

    private function assertValidAddInputs(int $productId, int $variantId, int $quantity): void
    {
        if ($productId <= 0 || $variantId <= 0 || $quantity <= 0) {
            throw new Exception('Invalid input');
        }
    }


    private function assertValidRemoveInputs(int $productId, int $variantId): void
    {
        if ($productId <= 0 || $variantId <= 0) {
            throw new Exception('Invalid input');
        }
    }

    private function successPayload(string $message, int $variantId): array
    {
        return [
            'success'        => true,
            'message'        => $message,
            'count'          => $this->cartService->getItemCount(),
            'total'          => $this->cartService->getTotalPrice(),
            'variantId'      => $variantId,
            'remainingStock' => $this->cartService->getVirtualVariantStock($variantId),
        ];
    }

    private function errorPayload(string $message): array
    {
        return ['success' => false, 'message' => $message];
    }

    private function logCartAction(string $action, int $productId, int $variantId, string $details): void
    {
        $userId = $this->currentUserId();
        if (!$userId) return;

        $extra = trim("Product ID: {$productId}, Variant ID: {$variantId}. {$details}");
        $this->logService->log($userId, $action, 'cart', $productId ?: null, $extra);
    }
}
