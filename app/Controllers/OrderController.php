<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ControllerBase;
use App\Core\Middleware;
use App\Mappers\OrderMapper;
use App\Mappers\UpdateOrderStatusMapper;
use App\Models\OrderStatus;
use App\Services\IOrderService;
use App\Services\IOrderItemService;

class OrderController extends ControllerBase
{
    private IOrderService $orderService;
    private IOrderItemService $orderItemService;

    public function __construct(IOrderService $orderService, IOrderItemService $orderItemService)
    {
        $this->orderService = $orderService;
        $this->orderItemService = $orderItemService;
    }

    public function index(): void
    {
        try {
            Middleware::requireAuth();
            Middleware::requireCustomer();

            $successQuery = trim((string) $this->input('success', ''));
            $errorQuery = trim((string) $this->input('error', ''));

            $userId = (int) ($this->currentUserId() ?? 0);
            if ($userId <= 0) {
                $this->jsonResponse($this->error('Authentication is required.'), 401);
            }

            $orders = $this->orderService->getMyOrders($userId);
            $orders = $this->filterOrders($orders);

            $this->jsonResponse($this->success([
                'orders' => OrderMapper::mapToOrderDtos($orders),
                'statusFilter' => strtolower(trim((string) $this->input('status', ''))),
                'search' => strtolower(trim((string) $this->input('q', ''))),
                'success' => $successQuery,
                'error' => $errorQuery,
            ]));
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error('Failed to load orders.', ['detail' => $e->getMessage()]), 500);
        }
    }

    public function show(int $id): void
    {
        try {
            Middleware::requireAuth();
            Middleware::requireCustomer();

            $userId = (int) ($this->currentUserId() ?? 0);
            if ($userId <= 0) {
                $this->jsonResponse($this->error('Authentication is required.'), 401);
            }

            $order = $this->orderService->getMyOrder($userId, $id);
            $items = $this->orderItemService->getByOrderId($id);

            $this->jsonResponse($this->success(OrderMapper::mapToOrderDetailsDto($order, $items)));
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error('Order not found.', ['detail' => $e->getMessage()]), 404);
        }
    }

    public function cancel(int $id): void
    {
        try {
            Middleware::requireAuth();
            Middleware::requireCustomer();

            $userId = (int) ($this->currentUserId() ?? 0);
            if ($userId <= 0) {
                $this->jsonResponse($this->error('Authentication is required.'), 401);
            }

            $ok = $this->orderService->cancelMyOrder($userId, $id);
            $message = $ok ? 'Order cancelled successfully.' : 'Unable to cancel order.';
            $this->jsonResponse($ok
                ? $this->success(['orderId' => $id], $message)
                : $this->error($message),
                $ok ? 200 : 422
            );
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error('Cancel failed.', ['detail' => $e->getMessage()]), 500);
        }
    }

    public function adminUpdateStatus(int $id): void
    {
        try {
            Middleware::requireAdmin();

            $dto = UpdateOrderStatusMapper::mapToUpdateOrderStatusDto($this->requestData());
            $newStatus = OrderStatus::from($dto->status);

            $result = $this->orderService->adminUpdateStatus($id, $newStatus);
            $this->jsonResponse($this->success([
                'orderId' => $id,
                'status' => $newStatus->value,
                'allowedTransitions' => $result['allowedTransitions'] ?? [],
            ], 'Order status updated. ' . ($result['message'] ?? '')));
        } catch (\ValueError $e) {
            $this->jsonResponse($this->error('Invalid order status.'), 422);
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error($e->getMessage()), 500);
        }
    }

    private function filterOrders(array $orders): array
    {
        try {
            $statusFilter = strtolower(trim((string) $this->input('status', '')));
            $search = strtolower(trim((string) $this->input('q', '')));

            if ($statusFilter === '' && $search === '') {
                return $orders;
            }

            $filtered = [];
            foreach ($orders as $order) {
                $status = strtolower((string) ($order->status?->value ?? ''));
                $orderId = (string) ($order->orderId ?? '');
                $amount = (string) ($order->totalAmount ?? '');
                $date = (string) ($order->createdAt ?? '');

                // Check status filter
                if ($statusFilter !== '' && $status !== $statusFilter) {
                    continue;
                }

                // Check search query
                if ($search !== '') {
                    $searchable = strtolower("$orderId $status $amount $date");
                    if (!str_contains($searchable, $search)) {
                        continue;
                    }
                }

                $filtered[] = $order;
            }

            return $filtered;
        } catch (\Throwable $e) {
            return $orders;
        }
    }

}
