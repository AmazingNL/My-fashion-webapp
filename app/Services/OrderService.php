<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentStatus;
use App\Repositories\IOrderRepository;
use InvalidArgumentException;
use RuntimeException;

class OrderService implements IOrderService
{
    private IOrderRepository $orderRepo;
    private OrderItemService $orderItemService;
    private CartService $cartService;

    public function __construct(
        IOrderRepository $orderRepo,
        OrderItemService $orderItemService,
        CartService $cartService
    ) {
        $this->orderRepo = $orderRepo;
        $this->orderItemService = $orderItemService;
        $this->cartService = $cartService;
    }

    /**
     * Place an order from cart snapshot.
     */
    public function placeOrder(
        int $userId,
        string $shippingAddress,
        ?string $billingAddress = null,
        string $paymentMethod = 'credit_card',
        OrderStatus $orderStatus = OrderStatus::PENDING,
        PaymentStatus $paymentStatus = PaymentStatus::PENDING
    ): array {
        [$shipping, $billing] = $this->normalizeAddresses(
            $userId, $shippingAddress, $billingAddress
            );

        $items = $this->requireCartItems();
        $total = $this->requireTotal();
        $orderId = $this->createOrderRecord(
            $userId,
            $shipping,
            $billing,
            $paymentMethod,
            $orderStatus,
            $paymentStatus,
            $total
        );

        $this->orderItemService->createFromCart($orderId, $items);
        $this->cartService->clearCart();

        return ['orderId' => $orderId, 'totalAmount' => $total];
    }


    /** Customer: list my orders */
    public function getMyOrders(int $userId): array
    {
        if ($userId <= 0)
            throw new InvalidArgumentException('Invalid user.');
        return $this->orderRepo->findByCustomerId($userId);
    }

    /** Customer: view one order (ownership check) */
    public function getMyOrder(int $userId, int $orderId): Order
    {
        $order = $this->requireOrder($orderId);
        if ((int) $order->userId !== (int) $userId) {
            throw new RuntimeException('Not allowed.');
        }
        return $order;
    }

    public function getAllOrders(): array
    {
        // Repository already returns Order objects
        return $this->orderRepo->getAll();
    }

    public function countAllOrders(): int
    {
        return count($this->orderRepo->getAll());
    }

    public function getOrderById(int $orderId): Order
    {
        return $this->requireOrder($orderId);
    }

    public function getItemsByOrderId(int $orderId): array
    {
        $this->requireOrder($orderId); // ensures order exists
        return $this->orderItemService->getByOrderId($orderId);
    }


    /** Customer: cancel order (only before shipped/delivered) */
    public function cancelMyOrder(int $userId, int $orderId): bool
    {
        $order = $this->getMyOrder($userId, $orderId);
        $status = strtolower($order->status->value);

        if (in_array($status, ['shipped', 'delivered'], true)) {
            throw new RuntimeException('Order can no longer be cancelled.');
        }
        if ($status === 'cancelled')
            return true;

        return (bool) $this->orderRepo->updateStatus($orderId, OrderStatus::CANCELLED->value);
    }

    /** Admin: update status using enum */
    public function adminUpdateStatus(int $orderId, OrderStatus $newStatus): bool
    {
        $order = $this->requireOrder($orderId);

        $old = strtolower($order->status->value);
        $new = strtolower($newStatus->value);

        // Basic transition rules
        $allowed = [
            'pending' => ['processing', 'cancelled'],
            'processing' => ['paid', 'shipped', 'cancelled'],
            'paid' => ['shipped', 'cancelled'],
            'shipped' => ['delivered'],
            'delivered' => [],
            'cancelled' => [],
        ];

        if (!isset($allowed[$old]) || !in_array($new, $allowed[$old], true)) {
            throw new RuntimeException("Invalid status transition: {$old} -> {$new}");
        }

        // Auto payment update rule:
        $payment = null;

        if ($new === 'processing') {
            $payment = strtolower(PaymentStatus::COMPLETED->name); // "completed"
        } elseif ($new === 'cancelled') {
            $payment = strtolower(PaymentStatus::FAILED->name); // "failed"
        }

        // Update status (and payment if needed)
        return (bool) $this->orderRepo->updateStatus($orderId, $new, $payment);

    }


    // Private Helper methods//
    private function requireOrder(int $orderId): Order
    {
        if ($orderId <= 0)
            throw new InvalidArgumentException('Invalid order.');

        $order = $this->orderRepo->findById($orderId);
        if (!$order)
            throw new RuntimeException('Order not found.');

        return $order;
    }

    private function requireCartItems(): array
    {
        if ($this->cartService->isEmpty())
            throw new InvalidArgumentException('Cart is empty.');

        if (method_exists($this->cartService, 'validateCart')) {
            $errs = $this->cartService->validateCart();
            if (!empty($errs))
                throw new InvalidArgumentException(implode(' | ', $errs));
        }

        $items = $this->cartService->getCartItems();
        if (empty($items))
            throw new InvalidArgumentException('Cart is empty.');
        return $items;
    }

    private function requireTotal(): float
    {
        $total = round((float) $this->cartService->getTotalPrice(), 2);
        if ($total <= 0)
            throw new InvalidArgumentException('Invalid total amount.');
        return $total;
    }

    private function normalizeAddresses(int $userId, string $shippingAddress, ?string $billingAddress): array
    {
        if ($userId <= 0) {
            throw new InvalidArgumentException('Invalid user.');
        }

        $shipping = trim($shippingAddress);
        if ($shipping === '') {
            throw new InvalidArgumentException('Shipping address is required.');
        }

        $billing = trim((string) ($billingAddress ?? '')) ?: $shipping;
        return [$shipping, $billing];
    }

    private function createOrderRecord(
        int $userId,
        string $shipping,
        string $billing,
        string $paymentMethod,
        OrderStatus $orderStatus,
        PaymentStatus $paymentStatus,
        float $total
    ): int {
        $order = new Order(
            0,
            $userId,
            $orderStatus,
            $total,
            $shipping,
            $billing,
            $paymentMethod,
            $paymentStatus,
            null,
            null
        );

        $orderId = (int) $this->orderRepo->create($order);
        if ($orderId <= 0) {
            throw new RuntimeException('Failed to create order.');
        }

        return $orderId;
    }
}
