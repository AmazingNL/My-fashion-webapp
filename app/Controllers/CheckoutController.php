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
        Middleware::requireAuth();
        Middleware::requireCustomer();

        $this->ensureSession();
        $flash = $_SESSION['checkout_flash'] ?? null;
        unset($_SESSION['checkout_flash']);

        if ($this->cartService->isEmpty()) {
            $this->setFlash('checkout', 'Your cart is empty.', 'error');
            $this->redirect('/viewCart');
            return;
        }
        $this->render('Checkout/Index', [
            'title' => 'Checkout',
            'cartItems' => $this->cartService->getCartItems(),
            'total' => $this->cartService->getTotalPrice(),
            'noticeMessage' => is_array($flash) ? (string) ($flash['message'] ?? '') : '',
            'noticeType' => is_array($flash) ? (string) ($flash['type'] ?? 'success') : 'success',
        ]);
    }

    // POST /checkout/place
    // Validate the cart and place the order from the submitted form.
    public function processCheckout(): void
    {
        Middleware::requireAuth();
        Middleware::requireCustomer();
        $this->validateCsrf();
        $userId = $this->requireCheckoutUserId();
        if ($userId === null) {
            return;
        }

        if (!$this->validateCheckoutCartOrRedirect()) {
            return;
        }

        $checkoutInput = $this->readCheckoutInputOrRedirect();
        if ($checkoutInput === null) {
            return;
        }

        [$shipping, $billing, $payment] = $checkoutInput;
        $this->placeCheckoutOrder($userId, $shipping, $billing, $payment);
    }

    // Ensure we have a valid logged-in customer id before placing an order.
    private function requireCheckoutUserId(): ?int
    {
        $userId = (int) ($this->currentUserId() ?? 0);
        if ($userId > 0) {
            return $userId;
        }

        $this->setFlash('checkout', 'Your session expired. Please log in again.', 'error');
        $this->redirect('/?error=login_required');
        return null;
    }

    // Re-validate cart state to prevent checkout with stale product/stock data.
    private function validateCheckoutCartOrRedirect(): bool
    {
        $errors = $this->cartService->validateCart();
        if (empty($errors)) {
            return true;
        }

        $this->setFlash('checkout', implode(' | ', $errors), 'error');
        $this->redirect('/checkout');
        return false;
    }

    // Read and validate checkout form fields.
    private function readCheckoutInputOrRedirect(): ?array
    {
        $shipping = trim((string) $this->input('shippingAddress', ''));
        if ($shipping === '') {
            $this->setFlash('checkout', 'Shipping address is required', 'error');
            $this->redirect('/checkout');
            return null;
        }

        $billing = trim((string) $this->input('billingAddress', '')) ?: $shipping;
        $payment = trim((string) $this->input('paymentMethod', 'credit_card'));

        return [$shipping, $billing, $payment];
    }

    // Create the order and handle known failure paths.
    private function placeCheckoutOrder(int $userId, string $shipping, string $billing, string $payment): void
    {
        try {
            $result = $this->orderService->placeOrder(
                $userId,
                $shipping,
                $billing,
                $payment,
                OrderStatus::PENDING,
                PaymentStatus::PENDING
            );

            $orderId = (int) ($result['orderId'] ?? 0);
            $this->cartService->clearCart();
            $this->redirect('/orders/' . $orderId . '?success=' . urlencode('Order placed successfully.'));
        } catch (\Throwable $e) {
            $message = (string) $e->getMessage();
            if (str_contains($message, 'orders_ibfk_1') || str_contains($message, 'FOREIGN KEY (`userId`)')) {
                // Session user no longer exists in DB (stale login after DB reset/import).
                $this->ensureSession();
                unset($_SESSION['userId'], $_SESSION['role']);
                $this->redirect('/?error=login_required');
                return;
            }

            $this->setFlash('checkout', 'Checkout failed: ' . $e->getMessage(), 'error');
            $this->redirect('/checkout');
        }
    }


    // GET /checkout/confirmation/{id}
    // Render the order confirmation page for the current customer.
    public function confirmation(int $orderId): void
    {
        Middleware::requireAuth();
        Middleware::requireCustomer();

        $userId = (int) ($this->currentUserId() ?? 0);
        if ($userId <= 0) {
            $this->redirect('/checkout');
            return;
        }

        try {
            $order = $this->orderService->getMyOrder($userId, $orderId);
            $items = $this->orderItemService->getByOrderId($orderId);

            $this->render('Checkout/OrderConfirmation', [
                'title' => 'Order Confirmation',
                'order' => $order,
                'orderItems' => $items,
                'success' => 'Your order has been placed successfully!',
            ]);
        } catch (\Throwable $e) {
            $this->setFlash('checkout', 'Order not found', 'error');
            $this->redirect('/checkout');
        }
    }
}

