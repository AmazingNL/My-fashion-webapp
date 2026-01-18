<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ControllerBase;
use App\Core\Middleware;
use App\Models\OrderStatus;
use App\Services\IOrderService;
use App\Services\ICartService;
use App\Services\IOrderItemService;

class OrderController extends ControllerBase
{
    private IOrderService $orderService;
    private ICartService $cartService;
    private IOrderItemService $orderItemService;

    public function __construct(IOrderService $orderService, ICartService $cartService, IOrderItemService $orderItemService)
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

        // Views/Orders/index.php
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
            // Ownership check
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

    // GET /admin/orders/api
    public function adminApiList(): void
    {
        Middleware::requireAdmin();
        $orders = [];
        if (method_exists($this->orderService, 'getAllOrders')) {
            $orders = $this->orderService->getAllOrders();
        }

        $out = array_map(function ($o) {
            // Works if $o is an Order object with getters (like your customer apiList)
            if (is_object($o) && method_exists($o, 'getOrderId')) {
                return [
                    'orderId' => (int) $o->getOrderId(),
                    'status' => (string) $o->getStatus(),
                    'paymentStatus' => (string) $o->getPaymentStatus(),
                    'totalAmount' => (float) $o->getTotalAmount(),
                    'createdAt' => (string) $o->getCreatedAt(),
                ];
            }

            // Works if $o is an array row (repo returning assoc arrays)
            if (is_array($o)) {
                return [
                    'orderId' => (int) ($o['orderId'] ?? $o['id'] ?? 0),
                    'status' => (string) ($o['status'] ?? $o['orderStatus'] ?? 'pending'),
                    'paymentStatus' => (string) ($o['paymentStatus'] ?? $o['payment_status'] ?? ''),
                    'totalAmount' => (float) ($o['totalAmount'] ?? $o['total'] ?? 0),
                    'createdAt' => (string) ($o['createdAt'] ?? $o['created_at'] ?? ''),
                ];
            }

            return [];
        }, $orders);

        $this->jsonResponse(['orders' => $out], 200);
    }


    // GET /admin/orders/{id}/items/api
    public function adminApiItems(int $id): void
    {
        Middleware::requireAdmin();

        try {
            $items = $this->orderItemService->getByOrderId($id);

            $out = array_map(function ($it) {
                // Object style (like your customer apiItems)
                if (is_object($it) && method_exists($it, 'getOrderItemId')) {
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
                }

                // Array style
                if (is_array($it)) {
                    $qty = (float) ($it['quantity'] ?? 0);
                    $price = (float) ($it['price'] ?? 0);
                    return [
                        'orderItemId' => $it['orderItemId'] ?? null,
                        'productId' => $it['productId'] ?? null,
                        'variantId' => $it['variantId'] ?? null,
                        'quantity' => $qty,
                        'price' => $price,
                        'subtotal' => round($qty * $price, 2),
                        'createdAt' => $it['createdAt'] ?? '',
                        'productName' => $it['productName'] ?? '',
                        'productImage' => $it['productImage'] ?? '',
                        'size' => $it['variantSize'] ?? ($it['size'] ?? ''),
                        'colour' => $it['variantColour'] ?? ($it['colour'] ?? ''),
                        'productCategory' => $it['productCategory'] ?? '',
                    ];
                }

                return [];
            }, $items);

            $this->jsonResponse(['items' => $out], 200);
        } catch (\Throwable $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    // POST /admin/orders/{id}/status/api
    public function adminApiUpdateStatus(int $id): void
    {
        Middleware::requireAdmin();
        $this->validateCsrf();

        // Accept JSON body OR normal POST form
        $raw = file_get_contents('php://input') ?: '';
        $json = [];
        if ($raw !== '') {
            $tmp = json_decode($raw, true);
            if (is_array($tmp)) {
                $json = $tmp;
            }
        }

        $statusRaw = (string) ($json['status'] ?? $_POST['status'] ?? '');
        $statusRaw = trim($statusRaw);

        if ($statusRaw === '') {
            $this->jsonResponse(['error' => 'Status is required.'], 400);
        }

        $enum = $this->parseOrderStatus($statusRaw);
        if (!$enum) {
            $this->jsonResponse(['error' => 'Invalid status value.'], 400);
        }

        try {
            $ok = $this->orderService->adminUpdateStatus($id, $enum);
            $this->jsonResponse(['success' => (bool) $ok], $ok ? 200 : 400);
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

    private function parseOrderStatus(string $value): ?OrderStatus
    {
        $v = strtoupper(trim($value));

        foreach (OrderStatus::cases() as $case) {
            if ($case->name === $v) {
                return $case;
            }
        }
        return null;
    }

}
