<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ControllerBase;
use App\Core\Middleware;
use App\DTO\CheckoutRequestDto;
use App\Mappers\CartMapper;
use App\Mappers\CheckoutRequestMapper;
use App\Mappers\OrderMapper;
use App\Models\OrderStatus;
use App\Models\PaymentStatus;
use App\Services\ICartService;
use App\Services\IOrderService;
use App\Services\IOrderItemService;

class CheckoutController extends ControllerBase
{
    private ICartService $cartService;
    private IOrderService $orderService;
    private IOrderItemService $orderItemService;

    // Wire the checkout controller to cart and order services.
    public function __construct(
        ICartService $cartService,
        IOrderService $orderService,
        IOrderItemService $orderItemService
    ) {
        $this->cartService = $cartService;
        $this->orderService = $orderService;
        $this->orderItemService = $orderItemService;
    }

    // Show the checkout form and the current cart summary.
    public function showCheckout(): void
    {
        try {
            Middleware::requireAuth();
            Middleware::requireCustomer();
            $this->requireCheckoutUserIdOrFail();

            if ($this->cartService->isEmpty()) {
                $this->jsonResponse($this->error('Your cart is empty.'), 422);
            }
            $this->jsonResponse($this->success([
                'cartItems' => CartMapper::mapToCartItemDtos($this->cartService->getCartItems()),
                'total' => $this->cartService->getTotalPrice(),
            ]));
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error('Failed to load checkout.', ['detail' => $e->getMessage()]), 500);
        }
    }

    // POST /checkout/place
    // Validate the cart and place the order from the submitted form.
    public function processCheckout(): void
    {
        try {
            Middleware::requireAuth();
            Middleware::requireCustomer();
            $userId = $this->requireCheckoutUserIdOrFail();

            if (!$this->validateCheckoutCart()) {
                return;
            }

            $checkoutInput = $this->readCheckoutInput();
            if ($checkoutInput === null) {
                return;
            }

            $this->placeCheckoutOrder($userId, $checkoutInput);
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error('Checkout failed.', ['detail' => $e->getMessage()]), 500);
        }
    }

    // Ensure we have a valid logged-in customer id before placing an order.
    private function requireCheckoutUserIdOrFail(): int
    {
        $userId = (int) ($this->currentUserId() ?? 0);
        if ($userId > 0) {
            $this->cartService->setUserId($userId);
            return $userId;
        }

        $this->jsonResponse($this->error('Authentication expired. Please log in again.'), 401);
        throw new \RuntimeException('Authentication is required.');
    }

    // Re-validate cart state to prevent checkout with stale product/stock data.
    private function validateCheckoutCart(): bool
    {
        $errors = $this->cartService->validateCart();
        if (empty($errors)) {
            return true;
        }

        $this->jsonResponse($this->error('Cart validation failed.', $errors), 422);
        return false;
    }

    // Read and validate checkout form fields.
    private function readCheckoutInput(): ?CheckoutRequestDto
    {
        $dto = CheckoutRequestMapper::mapToCheckoutRequestDto($this->requestData());
        if ($dto->shippingAddress === '') {
            $this->jsonResponse($this->error('Shipping address is required.'), 422);
            return null;
        }

        return $dto;
    }

    // Create the order and handle known failure paths.
    private function placeCheckoutOrder(int $userId, CheckoutRequestDto $dto): void
    {
        try {
            $result = $this->orderService->placeOrder(
                $userId,
                $dto->shippingAddress,
                $dto->billingAddress,
                $dto->paymentMethod,
                OrderStatus::PENDING,
                PaymentStatus::PENDING
            );

            $orderId = (int) ($result['orderId'] ?? 0);
            $this->cartService->clearCart();
            $this->jsonResponse($this->success([
                'orderId' => $orderId,
                'order' => $result,
            ], 'Order placed successfully.'), 201);
        } catch (\Throwable $e) {
            $message = (string) $e->getMessage();
            if (str_contains($message, 'orders_ibfk_1') || str_contains($message, 'FOREIGN KEY (`userId`)')) {
                $this->jsonResponse($this->error('Authentication expired. Please log in again.'), 401);
            }

            $this->jsonResponse($this->error('Checkout failed.', ['detail' => $e->getMessage()]), 500);
        }
    }


    // GET /checkout/confirmation/{id}
    // Render the order confirmation page for the current customer.
    public function confirmation(int $orderId): void
    {
        try {
            Middleware::requireAuth();
            Middleware::requireCustomer();

            $userId = (int) ($this->currentUserId() ?? 0);
            if ($userId <= 0) {
                $this->jsonResponse($this->error('Authentication is required.'), 401);
            }

            $order = $this->orderService->getMyOrder($userId, $orderId);
            $items = $this->orderItemService->getByOrderId($orderId);

            $this->jsonResponse($this->success(
                OrderMapper::mapToOrderDetailsDto($order, $items),
                'Your order has been placed successfully!'
            ));
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error('Order not found.'), 404);
        }
    }
}

