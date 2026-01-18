<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ControllerBase;
use App\Core\Middleware;
use App\Models\OrderStatus;
use App\Models\PaymentStatus;
use App\Services\ICartService;
use App\Services\IOrderService;
use App\Services\IOrderItemService;
use App\Services\IActivityLogService;

class CheckoutController extends ControllerBase
{
    private ICartService $cartService;
    private IOrderService $orderService;
    private IOrderItemService $orderItemService;
    private IActivityLogService $logService;

    public function __construct(
        ICartService $cartService,
        IOrderService $orderService,
        IOrderItemService $orderItemService,
        IActivityLogService $logService
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
        ], );
    }

    // POST /checkout/place
    public function processCheckout(): void
    {
        Middleware::requireAuth();
        Middleware::requireCustomer();
        $this->validateCsrf();

        $userId = $this->requireUser();
        $this->ensureCartNotEmpty($userId);

        $data = $this->checkoutInput();
        $this->validateCartOrFail($userId);

        $this->placeOrderAndRespond($userId, $data);
    }


    // GET /checkout/confirmation/{id} (optional)
    public function confirmation(int $orderId): void
    {
        Middleware::requireAuth();
        Middleware::requireCustomer();

        $userId = $this->sessionUserId();
        if (!$userId)
            $this->redirect('/');

        $order = $this->orderService->getMyOrder((int) $userId, (int) $orderId);
        $items = $this->orderItemService->getByOrderId((int) $orderId);

        $this->logService->log(
            $userId,
            'Order Confirmation Viewed',
            'order',
            (int) $orderId,
            'User viewed confirmation page.'
        );

        $this->render('Checkout/OrderConfirmation', [
            'title' => 'Order Confirmation',
            'order' => $order,
            'orderItems' => $items,
        ]);
    }



    // Private and Helper Methods //
    private function requireUser(): int
    {
        $id = (int) ($this->sessionUserId() ?? 0);
        if ($id <= 0) {
            $this->jsonResponse(['error' => 'login_required'], 401);
            exit;
        }
        return $id;
    }

    private function ensureCartNotEmpty(int $userId): void
    {
        if (!$this->cartService->isEmpty())
            return;

        $this->logService->log($userId, 'Checkout Failed', 'checkout', null, 'Cart is empty.');
        $this->jsonResponse(['error' => 'Cart is empty'], 400);
        exit;
    }

    private function checkoutInput(): array
    {
        return [
            'shipping' => trim((string) $this->input('shippingAddress', '')),
            'billing' => trim((string) $this->input('billingAddress', '')) ?: null,
            'payment' => trim((string) $this->input('paymentMethod', 'credit_card')),
        ];
    }

    private function validateCartOrFail(int $userId): void
    {
        $errors = $this->cartService->validateCart();
        if (empty($errors))
            return;

        $this->logService->log(
            $userId,
            'Checkout Failed',
            'checkout',
            null,
            implode(' | ', $errors)
        );
        $this->jsonResponse(['error' => implode(' | ', $errors)], 400);
        exit;
    }

    private function placeOrderAndRespond(int $userId, array $data): void
    {
        try {
            $res = $this->orderService->placeOrder(
                $userId,
                $data['shipping'],
                $data['billing'],
                $data['payment'],
                OrderStatus::PENDING,
                PaymentStatus::PENDING
            );

            $this->cartService->clearCart();

            $this->jsonResponse([
                'message' => 'Order placed successfully',
                'orderId' => (int) $res['orderId'],
                'totalAmount' => (float) $res['totalAmount'],
                'redirect' => "/orders/{$res['orderId']}",
            ], 201);
        } catch (\Throwable $e) {
            $this->logService->log($userId, 'Checkout Failed', 'checkout', null, $e->getMessage());
            $this->jsonResponse(['error' => 'Checkout failed'], 400);
        }
    }

    private function sessionUserId(): ?int
    {
        $this->ensureSession();
        return isset($_SESSION['userId']) ? (int) $_SESSION['userId'] : null;
    }
}
