<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ControllerBase;
use App\Core\Middleware;
use App\Models\OrderStatus;
use App\Models\PaymentStatus;
use App\Services\OrderService;
use App\Services\CartService;
use App\Services\OrderItemService;

class OrderController extends ControllerBase
{
    private OrderService $orderService;
    private CartService $cartService;
    private OrderItemService $orderItemService;

    public function __construct(OrderService $orderService, CartService $cartService, OrderItemService $orderItemService)
    {
        $this->orderService = $orderService;
        $this->cartService = $cartService;
        $this->orderItemService = $orderItemService;
    }

    /* ==========
     * Pages
     * ========== */

    // GET /orders
    public function index(): void
    {
        Middleware::requireAuth();
        Middleware::requireCustomer();

        $userId = $this->sessionUserId();
        if (!$userId) {
            $this->redirect('/?error=login_required');
        }

        $orders = $this->orderService->getMyOrders($userId);

        // View file suggestion: Views/Orders/index.php
        $this->render('Orders/Index', [
            'orders' => $orders,
        ]);
    }

    // GET /orders/{id}
    public function show(int $id): void
    {
        Middleware::requireAuth();
        Middleware::requireCustomer();

        $userId = $this->sessionUserId();
        if (!$userId) {
            $this->redirect('/?error=login_required');
        }

        $orders = $this->orderService->getMyOrder($userId, $id);

        $this->render('Orders/ShowOrders', [
            'order' => $orders,
        ]);
    }

    /* ==========
     * Actions
     * ========== */


    // POST /orders/{id}/cancel
    public function cancel(int $id): void
    {
        Middleware::requireAuth();
        Middleware::requireCustomer();

        $this->validateCsrf();

        $userId = $this->sessionUserId();
        if (!$userId) {
            $this->jsonResponse(['error' => 'login_required'], 401);
        }

        try {
            // If you already added cancelMyOrder in your rewritten service, call it here.
            // Otherwise you can implement a simple updateStatus in repo+service.
            $ok = $this->orderService->cancelMyOrder($userId, $id);

            $this->jsonResponse([
                'message' => $ok ? 'Order cancelled' : 'Unable to cancel order',
            ], $ok ? 200 : 400);

        } catch (\Throwable $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    /* ==========
     * API
     * ========== */

    // GET /api/orders
    public function apiList(): void
    {
        Middleware::requireAuth();
        Middleware::requireCustomer();

        $userId = $this->sessionUserId();
        if (!$userId) {
            $this->jsonResponse(['error' => 'login_required'], 401);
        }

        $orders = $this->orderService->getMyOrders($userId);

        $out = array_map(function ($o) {
            return [
                'orderId' => (int) $o->getOrderId(),
                'status' => (string) $o->getStatus(),
                'paymentStatus' => (string) $o->getPaymentStatus(),
                'totalAmount' => (float) $o->getTotalAmount(),
                'createdAt' => (string) $o->getCreatedAt(),
            ];
        }, $orders);

        $this->jsonResponse(['orders' => $out], 200);
    }


    // GET /api/orders/{id}
    public function apiShow(int $id): void
    {
        Middleware::requireAuth();
        Middleware::requireCustomer();

        $userId = $this->sessionUserId();
        if (!$userId) {
            $this->jsonResponse(['error' => 'login_required'], 401);
        }

        try {
            $order = $this->orderService->getMyOrder($userId, $id);

            $this->jsonResponse([
                'order' => [
                    'orderId' => (int) $order->getOrderId(),
                    'status' => (string) $order->getStatus(),
                    'paymentStatus' => (string) $order->getPaymentStatus(),
                    'totalAmount' => (float) $order->getTotalAmount(),
                    'createdAt' => (string) $order->getCreatedAt(),
                    'shippingAddress' => (string) $order->getShippingAddress(),
                    'billingAddress' => (string) $order->getBillingAddress(),
                    'paymentMethod' => (string) $order->getPaymentMethod(),
                ]
            ], 200);

        } catch (\Throwable $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 404);
        }
    }

    // GET /api/orders/{id}/items
    public function apiItems(int $id): void
    {
        Middleware::requireAuth();
        Middleware::requireCustomer();

        $userId = $this->sessionUserId();
        if (!$userId) {
            $this->jsonResponse(['error' => 'login_required'], 401);
        }

        try {
            // Ownership check (important)
            $this->orderService->getMyOrder($userId, $id);

            $items = $this->orderItemService->getByOrderId($id);

            // Convert objects -> arrays for clean JSON
            $out = array_map(function ($it) {
                return [
                    'orderItemId' => $it->getOrderItemId(),
                    'productId' => $it->getProductId(),
                    'variantId' => $it->getVariantId(),
                    'quantity' => $it->getQuantity(),
                    'price' => $it->getPrice(),
                    'subtotal' => round($it->getQuantity() * $it->getPrice(), 2),
                    'createdAt' => $it->getCreatedAt(),
                    'productName' => $it->getProductName(),
                    'productImage' => $it->getProductImage(),
                    'size' => $it->getVariantSize(),
                    'colour' => $it->getVariantColor(),
                    'productCategory' => $it->getProductCategory(),

                ];
            }, $items);

            $this->jsonResponse(['items' => $out], 200);
        } catch (\Throwable $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }


    /* ==========
     * Helpers
     * ========== */

    private function sessionUserId(): ?int
    {
        $this->ensureSession();
        // supports both keys because your project currently uses both 
        return isset($_SESSION['userId']) ? (int) $_SESSION['userId'] : null;
    }


}
