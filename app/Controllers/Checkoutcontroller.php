<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ControllerBase;
use App\Core\Middleware;
use App\Models\OrderStatus;
use App\Models\PaymentStatus;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\OrderItemService;
use App\Services\ActivityLogService;

class CheckoutController extends ControllerBase
{
    private CartService $cartService;
    private OrderService $orderService;
    private OrderItemService $orderItemService;
    private ActivityLogService $logService;

    public function __construct(
        CartService $cartService,
        OrderService $orderService,
        OrderItemService $orderItemService,
        ActivityLogService $logService
    ) {
        $this->cartService = $cartService;
        $this->orderService = $orderService;
        $this->orderItemService = $orderItemService;
        $this->logService = $logService;
    }

    // GET /checkout
    public function showCheckout(): void
    {
        Middleware::requireAuth();
        Middleware::requireCustomer();

        if ($this->cartService->isEmpty()) {
            $this->redirect('/viewCart?error=cart_empty');
        }

        $this->render('Checkout/Index', [
            'title' => 'Checkout',
            'cartItems' => $this->cartService->getCartItems(),
            'total' => $this->cartService->getTotalPrice()
        ],);
    }

    // POST /checkout/place
    public function processCheckout(): void
    {
        Middleware::requireAuth();
        Middleware::requireCustomer();

        $this->validateCsrf();

        $userId = $this->sessionUserId();
        if (!$userId) {
            $this->jsonResponse(['error' => 'login_required'], 401);
        }

        if ($this->cartService->isEmpty()) {
            $this->logService->log(
                $userId,
                'Checkout Failed',
                'checkout',
                null,
                'Cart is empty.'
            );
            $this->jsonResponse(['error' => 'Cart is empty'], 400);
        }

        $shipping = trim((string)$this->input('shippingAddress', ''));
        $billing  = trim((string)$this->input('billingAddress', ''));
        $paymentMethod = trim((string)$this->input('paymentMethod', 'credit_card'));

        if ($shipping === '') {
            $this->logService->log(
                $userId,
                'Checkout Failed',
                'checkout',
                null,
                'Missing shipping address.'
            );
            $this->jsonResponse(['error' => 'Shipping address is required'], 400);
        }

        // Stock / cart validation from your CartService :contentReference[oaicite:1]{index=1}
        $errors = $this->cartService->validateCart();
        if (!empty($errors)) {
            $this->logService->log(
                $userId,
                'Checkout Failed',
                'checkout',
                null,
                'Cart validation failed: ' . implode(' | ', $errors)
            );
            $this->jsonResponse(['error' => implode(' | ', $errors)], 400);
        }

        try {
            // Use enums in code, service converts to DB string as needed
            $result = $this->orderService->placeOrder(
                (int)$userId,
                $shipping,
                $billing !== '' ? $billing : null,
                $paymentMethod,
                OrderStatus::PENDING,
                PaymentStatus::PENDING
            );

            $orderId = (int)($result['orderId'] ?? 0);
            $totalAmount = (float)($result['totalAmount'] ?? 0);

            // If OrderService already clears cart, this is still safe
            $this->cartService->clearCart();

            $this->logService->log(
                $userId,
                'Order Placed',
                'order',
                $orderId ?: null,
                'Order placed. Total: €' . number_format($totalAmount, 2, '.', '')
            );

            $this->jsonResponse([
                'message' => 'Order placed successfully',
                'orderId' => $orderId,
                'totalAmount' => $totalAmount,
                'redirect' => $orderId ? "/orders/{$orderId}" : "/orders",
            ], 201);

        } catch (\Throwable $e) {
            $this->logService->log(
                $userId,
                'Checkout Failed',
                'checkout',
                null,
                'Exception: ' . $e->getMessage()
            );

            $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    // GET /checkout/confirmation/{id} (optional)
    public function confirmation(int $orderId): void
    {
        Middleware::requireAuth();
        Middleware::requireCustomer();

        $userId = $this->sessionUserId();
        if (!$userId) $this->redirect('/');

        $order = $this->orderService->getMyOrder((int)$userId, (int)$orderId);
        $items = $this->orderItemService->getByOrderId((int)$orderId);

        $this->logService->log(
            $userId,
            'Order Confirmation Viewed',
            'order',
            (int)$orderId,
            'User viewed confirmation page.'
        );

        $this->render('Checkout/OrderConfirmation', [
            'title' => 'Order Confirmation',
            'order' => $order,
            'orderItems' => $items,
        ]);
    }

    private function sessionUserId(): ?int
    {
        $this->ensureSession();
        return isset($_SESSION['userId']) ? (int)$_SESSION['userId'] : null;
    }
}
