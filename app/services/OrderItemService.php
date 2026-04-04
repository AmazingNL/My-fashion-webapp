<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Repositories\IOrderItemRepository;

class OrderItemService implements IOrderItemService
{
    private IOrderItemRepository $orderItemRepository;

    public function __construct(IOrderItemRepository $orderItemRepository)
    {
        $this->orderItemRepository = $orderItemRepository;
    }

    // Create order items from cart snapshot and persist to DB.
    public function createFromCart(int $orderId, array $cartItems): void
    {
        foreach ($cartItems as $item) {
            $orderItem = new OrderItem(
                0,
                $orderId,
                (int) ($item['productId'] ?? 0),
                (int) ($item['variantId'] ?? 0),
                (int) ($item['quantity'] ?? 0),
                (float) ($item['price'] ?? 0),
                null
            );
            $this->orderItemRepository->save($orderItem);
        }
    }

    // Retrieve all items for an order.
    public function getByOrderId(int $orderId): array
    {
        return $this->orderItemRepository->findByOrderId($orderId);
    }
}
