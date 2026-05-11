<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ControllerBase;
use App\Core\Middleware;
use App\DTO\CartItemRequestDto;
use App\Mappers\CartMapper;
use App\Services\ICartService;

class CartController extends ControllerBase
{
    private ICartService $cartService;

    public function __construct(ICartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function viewCart(): void
    {
        try {
            $this->requireCartUser();

            $this->jsonResponse($this->success([
                'cart' => $this->cartSummary(),
            ]));
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error($e->getMessage()), 422);
        }
    }

    public function addToCart(): void
    {
        try {
            $this->requireCartUser();
            $dto = $this->readCartInputs();
            $this->cartService->validateCartItemRequest($dto);

            $this->cartService->addItem($dto->productId, $dto->variantId, $dto->quantity);
            $this->jsonResponse($this->success($this->cartSummary(), 'Item added to your cart.'), 201);
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error($e->getMessage()), 422);
        }
    }

    public function updateQuantity(): void
    {
        try {
            $this->requireCartUser();
            $dto = $this->readCartInputs();
            $this->cartService->validateCartItemIds($dto->productId, $dto->variantId);
            if ($dto->quantity <= 0) {
                $this->cartService->removeItem($dto->productId, $dto->variantId);
                $this->jsonResponse($this->success($this->cartSummary(), 'Item removed from your cart.'));
            }
            $result = $this->cartService->updateQuantity($dto->productId, $dto->variantId, $dto->quantity);
            if (!$result) {
                $this->jsonResponse($this->error('Item not found in cart.'), 404);
            }

            $this->jsonResponse($this->success($this->cartSummary(), 'Cart updated.'));
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error($e->getMessage()), 422);
        }
    }

    public function removeFromCart(): void
    {
        try {
            $this->requireCartUser();
            $dto = $this->readCartInputs();
            $this->cartService->validateCartItemIds($dto->productId, $dto->variantId);

            $result = $this->cartService->removeItem($dto->productId, $dto->variantId);
            if (!$result) {
                $this->jsonResponse($this->error('Item not found in cart.'), 404);
            }
            $this->jsonResponse($this->success($this->cartSummary(), 'Item removed from your cart.'));
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error($e->getMessage()), 422);
        }
    }

    public function clearCart(): void
    {
        try {
            $this->requireCartUser();
            $this->cartService->clearCart();
            $this->jsonResponse($this->success($this->cartSummary(), 'Cart cleared.'));
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error($e->getMessage()), 422);
        }
    }

    private function cartSummary(): array
    {
        return CartMapper::mapToCartSummaryDto(
            $this->cartService->getCartItems(),
            $this->cartService->getTotalPrice(),
            $this->cartService->getItemCount(),
            $this->cartService->isEmpty()
        )->jsonSerialize();
    }

    private function readCartInputs(): CartItemRequestDto
    {
        return CartMapper::mapToCartItemRequestDto($this->requestData());
    }

    private function requireCartUser(): void
    {
        Middleware::requireCustomer();

        $userId = (int) ($this->currentUserId() ?? 0);
        if ($userId <= 0) {
            $this->jsonResponse($this->error('Authentication is required.'), 401);
        }

        $this->cartService->setUserId($userId);
    }

}
