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
        $shipping = $this->requireShipping($userId, $shippingAddress);
        $billing = $this->billingOrShipping($billingAddress, $shipping);

        $cartItems = $this->requireCartItems();
        $total = $this->requireTotal();

        $orderId = (int) $this->orderRepo->create(
            $this->buildOrder($userId, $shipping, $billing, $paymentMethod, $total, $orderStatus, $paymentStatus)
        );

        if ($orderId <= 0)
            throw new RuntimeException('Failed to create order.');

        $this->orderItemService->createFromCart($orderId, $cartItems);
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
        if ((int) $order->getUserId() !== (int) $userId) {
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
        return $this->orderRepo->countAll();
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
        $status = strtolower((string) $order->getStatus());

        if (in_array($status, ['shipped', 'delivered'], true)) {
            throw new RuntimeException('Order can no longer be cancelled.');
        }
        if ($status === 'cancelled')
            return true;

        return (bool) $this->orderRepo->updateStatus($orderId, strtoupper(OrderStatus::CANCELLED->name));
    }

    /** Customer: update address (only before shipped) */
    public function updateMyAddresses(int $userId, int $orderId, string $shipping, ?string $billing = null): bool
    {
        $order = $this->getMyOrder($userId, $orderId);
        $status = strtolower((string) $order->getStatus());

        if (in_array($status, ['shipped', 'delivered'], true)) {
            throw new RuntimeException('Address cannot be changed after shipping.');
        }

        $shipping = trim($shipping);
        if ($shipping === '')
            throw new InvalidArgumentException('Shipping address is required.');

        $billing = trim($billing ?? '');
        if ($billing === '')
            $billing = $shipping;

        return (bool) $this->orderRepo->updateAddresses($orderId, $shipping, $billing);
    }

    /** Admin: update status using enum */
    public function adminUpdateStatus(int $orderId, OrderStatus $newStatus): bool
    {
        $order = $this->requireOrder($orderId);

        $old = strtolower((string) $order->getStatus());
        $new = $this->orderStatusToDb($newStatus);

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
            $payment = $this->paymentStatusToDb(PaymentStatus::COMPLETED); // "completed"
        } elseif ($new === 'cancelled') {
            $payment = $this->paymentStatusToDb(PaymentStatus::FAILED); // "failed"
        }

        // Update status (and payment if needed)
        return (bool) $this->orderRepo->updateStatus($orderId, $new, $payment);

    }

    /** Admin: update payment status using enum */
    public function adminUpdatePaymentStatus(int $orderId, PaymentStatus $paymentStatus): bool
    {
        $this->requireOrder($orderId);
        $pay = $this->paymentStatusToDb($paymentStatus);

        return (bool) $this->orderRepo->updatePaymentStatus($orderId, $pay);
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

    /**
     * Convert PHP enum -> DB enum string.
     * MUST match your DB ENUM values exactly.
     */
    private function orderStatusToDb(OrderStatus $status): string
    {
        // OrderStatus::CANCELLED -> "cancelled"
        return strtolower($status->name);
    }

    private function paymentStatusToDb(PaymentStatus $status): string
    {
        // PaymentStatus::COMPLETED -> "completed"
        return strtolower($status->name);
    }

    private function requireShipping(int $userId, string $shippingAddress): string
    {
        if ($userId <= 0)
            throw new InvalidArgumentException('Invalid user.');
        $shipping = trim($shippingAddress);
        if ($shipping === '')
            throw new InvalidArgumentException('Shipping address is required.');
        return $shipping;
    }

    private function billingOrShipping(?string $billingAddress, string $shipping): string
    {
        $billing = trim((string) ($billingAddress ?? ''));
        return $billing === '' ? $shipping : $billing;
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

    private function buildOrder(
        int $userId,
        string $shipping,
        string $billing,
        string $paymentMethod,
        float $total,
        OrderStatus $orderStatus,
        PaymentStatus $paymentStatus
    ): Order {
        return new Order(
            null,
            $userId,
            $this->orderStatusToDb($orderStatus),
            $total,
            $shipping,
            $billing,
            $paymentMethod,
            $this->paymentStatusToDb($paymentStatus),
            null,
            null
        );
    }

}
