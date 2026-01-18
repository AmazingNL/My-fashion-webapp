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

    /**
     * Save many order items for one order.
     * $cartItems must look like CartService::getCartItems() rows:
     * productId, variantId, quantity, price
     */
    public function createFromCart(int $orderId, array $cartItems): array
    {
        $created = [];

        foreach ($cartItems as $item) {
            $orderItem = new OrderItem(
                null,
                $orderId,
                (int)($item['productId'] ?? 0),
                (int)($item['variantId'] ?? 0),
                (int)($item['quantity'] ?? 0),
                (float)($item['price'] ?? 0),
                $item['createdAt']
            );

            // your repo interface uses save()
            $this->orderItemRepository->save($orderItem);

            $created[] = [
                'productName' => (string)($item['name'] ?? $item['productName'] ?? 'Product'),
                'quantity' => (int)$orderItem->getQuantity(),
                'price' => (float)$orderItem->getPrice(),
            ];
        }

        return $created;
    }

    public function getByOrderId(int $orderId): array
    {
        return $this->orderItemRepository->findByOrderId($orderId);
    }
}
