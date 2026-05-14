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
use App\Repositories\UserRepository;
use App\Services\EmailService;
use App\Services\ICartService;
use App\Services\IOrderService;
use App\Services\IOrderItemService;
use App\Services\PaymentService;

class CheckoutController extends ControllerBase
{
    private ICartService $cartService;
    private IOrderService $orderService;
    private IOrderItemService $orderItemService;
    private PaymentService $paymentService;
    private EmailService $emailService;
    private UserRepository $userRepository;

    // Wire the checkout controller to cart and order services.
    public function __construct(
        ICartService $cartService,
        IOrderService $orderService,
        IOrderItemService $orderItemService,
        PaymentService $paymentService,
        EmailService $emailService,
        UserRepository $userRepository
    ) {
        $this->cartService = $cartService;
        $this->orderService = $orderService;
        $this->orderItemService = $orderItemService;
        $this->paymentService = $paymentService;
        $this->emailService = $emailService;
        $this->userRepository = $userRepository;
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

    public function confirmPayment(): void
    {
        try {
            Middleware::requireAuth();
            Middleware::requireCustomer();
            $userId = $this->requireCheckoutUserIdOrFail();

            $data = $this->requestData();
            $orderId = (int) ($data['orderId'] ?? 0);
            $provider = strtolower(trim((string) ($data['provider'] ?? '')));
            $reference = trim((string) ($data['paymentReference'] ?? ''));

            if ($orderId <= 0 || $provider === '' || $reference === '') {
                $this->jsonResponse($this->error('Payment confirmation details are missing.'), 422);
            }

            $order = $this->orderService->getMyOrder($userId, $orderId);
            if ($order->paymentStatus === PaymentStatus::COMPLETED) {
                $this->jsonResponse($this->success([
                    'orderId' => $orderId,
                    'paymentStatus' => $order->paymentStatus->value,
                ], 'Payment already confirmed.'));
            }

            $payment = $this->paymentService->confirmPayment($provider, $reference, $orderId, (float) $order->totalAmount);
            if (empty($payment['paid'])) {
                $this->jsonResponse($this->error('Payment has not been completed yet.', ['status' => (string) ($payment['status'] ?? 'unknown')]), 422);
            }

            $this->orderService->markPaymentCompleted($orderId);
            $items = $this->orderItemService->getByOrderId($orderId);
            $this->sendOrderEmail($userId, $orderId, (float) $order->totalAmount, $items);

            $this->jsonResponse($this->success([
                'orderId' => $orderId,
                'payment' => $payment,
            ], 'Payment confirmed. Your order confirmation email has been sent.'));
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error('Payment confirmation failed.', ['detail' => $e->getMessage()]), 500);
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
            if ($this->paymentService->isOnlineProvider($dto->paymentMethod)) {
                $this->paymentService->assertConfigured($dto->paymentMethod);
            }

            $cartItems = $this->cartService->getCartItems();
            $result = $this->orderService->placeOrder(
                $userId,
                $dto->shippingAddress,
                $dto->billingAddress,
                $dto->paymentMethod,
                OrderStatus::PENDING,
                PaymentStatus::PENDING
            );

            $orderId = (int) ($result['orderId'] ?? 0);

            if ($this->paymentService->isOnlineProvider($dto->paymentMethod)) {
                $returnUrl = $dto->returnUrl !== '' ? $dto->returnUrl : $this->defaultReturnUrl();
                $payment = $this->paymentService->createPayment(
                    $dto->paymentMethod,
                    $orderId,
                    (float) ($result['totalAmount'] ?? 0),
                    $returnUrl,
                    $cartItems
                );

                $this->jsonResponse($this->success([
                    'orderId' => $orderId,
                    'order' => $result,
                    'payment' => $payment,
                ], 'Order created. Continue to payment.'), 201);
            }

            $this->sendOrderEmail($userId, $orderId, (float) ($result['totalAmount'] ?? 0), $cartItems);
            $this->jsonResponse($this->success([
                'orderId' => $orderId,
                'order' => $result,
            ], 'Order placed successfully. Your confirmation email has been sent.'), 201);
        } catch (\Throwable $e) {
            $message = (string) $e->getMessage();
            if (str_contains($message, 'orders_ibfk_1') || str_contains($message, 'FOREIGN KEY (`userId`)')) {
                $this->jsonResponse($this->error('Authentication expired. Please log in again.'), 401);
            }

            if (str_contains($message, 'Stripe is not configured') || str_contains($message, 'PayPal is not configured')) {
                $this->jsonResponse($this->error($message), 422);
            }

            if (str_contains($message, 'Payment provider request failed') || str_contains($message, 'checkout URL')) {
                $this->jsonResponse($this->error('Payment setup failed.', ['detail' => $message]), 422);
            }

            $this->jsonResponse($this->error('Checkout failed.', ['detail' => $e->getMessage()]), 500);
        }
    }

    private function sendOrderEmail(int $userId, int $orderId, float $total, array $items): void
    {
        $user = $this->userRepository->findById($userId);
        if ($user === null || trim((string) $user->email) === '') {
            return;
        }

        $this->emailService->sendOrderConfirmation(
            (string) $user->email,
            (string) ($user->firstName ?: 'there'),
            $orderId,
            $total,
            $items
        );
    }

    private function defaultReturnUrl(): string
    {
        $origin = (string) ($_SERVER['HTTP_ORIGIN'] ?? 'http://localhost:5173');
        return rtrim($origin, '/') . '/checkout';
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

