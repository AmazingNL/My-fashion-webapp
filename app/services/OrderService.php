<?php

namespace App\Services;

use App\Models\CartItem;
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
     * Returns: ['orderId' => int, 'totalAmount' => float]
     */
    public function placeOrder(
        int $userId,
        string $shippingAddress,
        ?string $billingAddress = null,
        string $paymentMethod = 'credit_card',
        OrderStatus $orderStatus = OrderStatus::PENDING,
        PaymentStatus $paymentStatus = PaymentStatus::PENDING
    ): array {
        if ($userId <= 0) throw new InvalidArgumentException('Invalid user.');
        $shippingAddress = trim($shippingAddress);
        if ($shippingAddress === '') throw new InvalidArgumentException('Shipping address is required.');

        $billingAddress = trim($billingAddress ?? '');
        if ($billingAddress === '') $billingAddress = $shippingAddress;

        if ($this->cartService->isEmpty()) {
            throw new InvalidArgumentException('Cart is empty.');
        }

        // If you have validateCart() in CartService, keep this.
        if (method_exists($this->cartService, 'validateCart')) {
            $errs = $this->cartService->validateCart();
            if (!empty($errs)) throw new InvalidArgumentException(implode(' | ', $errs));
        }

        $cartItems = $this->cartService->getCartItems();
        if (empty($cartItems)) throw new InvalidArgumentException('Cart is empty.');

        $totalAmount = round((float)$this->cartService->getTotalPrice(), 2);
        if ($totalAmount <= 0) throw new InvalidArgumentException('Invalid total amount.');

        // Convert enums -> DB strings (because Order constructor expects strings)
        $statusDb = $this->orderStatusToDb($orderStatus);
        $paymentDb = $this->paymentStatusToDb($paymentStatus);

        // ✅ Matches your constructor exactly:
        // (?int, int, string, float, string, string, string, string, ?string, ?string)
        $order = new Order(
            null,           // orderId
            $userId,        // userId
            $statusDb,      // status (string)
            $totalAmount,   // totalAmount
            $shippingAddress,
            $billingAddress,
            $paymentMethod,
            $paymentDb,     // paymentStatus (string)
            null,           // createdAt
            null            // updatedAt
        );

        $orderId = (int)$this->orderRepo->create($order);
        if ($orderId <= 0) throw new RuntimeException('Failed to create order.');

        $this->orderItemService->createFromCart($orderId, $cartItems);
        $this->cartService->clearCart();

        return ['orderId' => $orderId, 'totalAmount' => $totalAmount];
    }

    /** Customer: list my orders */
    public function getMyOrders(int $userId): array
    {
        if ($userId <= 0) throw new InvalidArgumentException('Invalid user.');
        return $this->orderRepo->findByCustomerId($userId);
    }

    /** Customer: view one order (ownership check) */
    public function getMyOrder(int $userId, int $orderId): Order
    {
        $order = $this->requireOrder($orderId);
        if ((int)$order->getUserId() !== (int)$userId) {
            throw new RuntimeException('Not allowed.');
        }
        return $order;
    }

public function getAllOrders(): array
{
    // Repository already returns Order objects
    return $this->orderRepo->getAll();
}


    /** Customer: cancel order (only before shipped/delivered) */
    public function cancelMyOrder(int $userId, int $orderId): bool
    {
        $order = $this->getMyOrder($userId, $orderId);
        $status = strtolower((string)$order->getStatus());

        if (in_array($status, ['shipped', 'delivered'], true)) {
            throw new RuntimeException('Order can no longer be cancelled.');
        }
        if ($status === 'cancelled') return true;

        return (bool)$this->orderRepo->updateStatus($orderId, strtoupper(OrderStatus::CANCELLED->name));
    }

    /** Customer: update address (only before shipped) */
    public function updateMyAddresses(int $userId, int $orderId, string $shipping, ?string $billing = null): bool
    {
        $order = $this->getMyOrder($userId, $orderId);
        $status = strtolower((string)$order->getStatus());

        if (in_array($status, ['shipped', 'delivered'], true)) {
            throw new RuntimeException('Address cannot be changed after shipping.');
        }

        $shipping = trim($shipping);
        if ($shipping === '') throw new InvalidArgumentException('Shipping address is required.');

        $billing = trim($billing ?? '');
        if ($billing === '') $billing = $shipping;

        return (bool)$this->orderRepo->updateAddresses($orderId, $shipping, $billing);
    }

    /** Admin: update status using enum */
    public function adminUpdateStatus(int $orderId, OrderStatus $newStatus): bool
    {
        $order = $this->requireOrder($orderId);

        $old = strtolower((string)$order->getStatus());
        $new = $this->orderStatusToDb($newStatus);

        // Basic transition rules (edit if your workflow differs)
        $allowed = [
            'pending'    => ['processing', 'cancelled'],
            'processing' => ['paid', 'shipped', 'cancelled'],
            'paid'       => ['shipped', 'cancelled'],
            'shipped'    => ['delivered'],
            'delivered'  => [],
            'cancelled'  => [],
        ];

        if (!isset($allowed[$old]) || !in_array($new, $allowed[$old], true)) {
            throw new RuntimeException("Invalid status transition: {$old} -> {$new}");
        }

        return (bool)$this->orderRepo->updateStatus($orderId, $new);
    }

    /** Admin: update payment status using enum */
    public function adminUpdatePaymentStatus(int $orderId, PaymentStatus $paymentStatus): bool
    {
        $this->requireOrder($orderId);
        $pay = $this->paymentStatusToDb($paymentStatus);

        return (bool)$this->orderRepo->updatePaymentStatus($orderId, $pay);
    }


    // Private Helper methods//
    private function requireOrder(int $orderId): Order
    {
        if ($orderId <= 0) throw new InvalidArgumentException('Invalid order.');

        $order = $this->orderRepo->findById($orderId);
        if (!$order) throw new RuntimeException('Order not found.');

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
}
