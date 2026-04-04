<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ControllerBase;
use App\Services\ICartService;
use App\ViewModel\CartVM;
use Exception;

class CartController extends ControllerBase
{
    private ICartService $cartService;

    public function __construct(ICartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function viewCart(): void
    {
        [$success, $error] = $this->consumeFlash('cart');

        $cartVm = new CartVM(
            'Shopping Cart',
            $this->cartService->getCartItems(),
            $this->cartService->getTotalPrice(),
            $this->cartService->getItemCount(),
            $this->cartService->isEmpty(),
            $success !== '' ? $success : $error,
            $success !== '' ? 'success' : 'error'
        );

        $this->render('Cart/ViewCart', [
            'cartVm' => $cartVm,
        ]);
    }

    public function addToBasket(): void
    {
        $this->validateCsrf();

        try {
            [$productId, $variantId, $quantity] = $this->readCartInputs();
            $this->checkValidIds($productId, $variantId);
            if ($quantity <= 0) {
                throw new Exception('Invalid input');
            }

            $this->cartService->addItem($productId, $variantId, $quantity);
            $this->setFlash('cart', 'Item added to your basket.', 'success');
            $this->redirect('/viewCart');
        } catch (Exception $e) {
            $this->setFlash('cart', $e->getMessage(), 'error');
            $this->redirect('/viewCart');
        }
    }

    public function updateQuantity(): void
    {
        $this->validateCsrf();
        try {
            [$productId, $variantId, $quantity] = $this->readCartInputs();
            $this->checkValidIds($productId, $variantId);
            if ($quantity <= 0) {
                $this->cartService->removeItem($productId, $variantId);
                $this->setFlash('cart', 'Item removed from your basket.', 'success');
                $this->redirect('/viewCart');
                return;
            }
            $result = $this->cartService->updateQuantity($productId, $variantId, $quantity);
            if (!$result) {
                $this->setFlash('cart', 'Item not found in cart', 'error');
                $this->redirect('/viewCart');
                return;
            }

            $this->setFlash('cart', 'Cart updated.', 'success');
            $this->redirect('/viewCart');
        } catch (Exception $e) {
            $this->setFlash('cart', $e->getMessage(), 'error');
            $this->redirect('/viewCart');
        }
    }

    public function removeFromBasket(): void
    {
        $this->validateCsrf();

        try {
            [$productId, $variantId] = $this->readCartInputs();
            $this->checkValidIds($productId, $variantId);

            $result = $this->cartService->removeItem($productId, $variantId);
            if (!$result) {
                $this->setFlash('cart', 'Item not found in cart', 'error');
                $this->redirect('/viewCart');
                return;
            }
            $this->setFlash('cart', 'Item removed from your basket.', 'success');
            $this->redirect('/viewCart');
        } catch (Exception $e) {
            $this->setFlash('cart', $e->getMessage(), 'error');
            $this->redirect('/viewCart');
        }
    }

    public function clearBasket(): void
    {
        $this->validateCsrf();

        try {
            $this->cartService->clearCart();
            $this->setFlash('cart', 'Cart cleared.', 'success');
            $this->redirect('/viewCart');
        } catch (Exception $e) {
            $this->setFlash('cart', $e->getMessage(), 'error');
            $this->redirect('/viewCart');
        }
    }

    private function readCartInputs(): array
    {
        return [
            (int) $this->input('productId', 0),
            (int) $this->input('variantId', 0),
            (int) $this->input('quantity', 1),
        ];
    }

    private function checkValidIds(int $productId, int $variantId): void
    {
        if ($productId <= 0 || $variantId <= 0) {
            throw new Exception('Invalid input');
        }
    }

}
