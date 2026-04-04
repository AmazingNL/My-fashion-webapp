<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ControllerBase;
use App\Core\Middleware;
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
        Middleware::requireAuth();
        Middleware::requireCustomer();

        try {
            $successQuery = trim((string) $this->input('success', ''));
            $errorQuery = trim((string) $this->input('error', ''));
            if ($successQuery !== '' || $errorQuery !== '') {
                $this->setFlash('order', $successQuery !== '' ? $successQuery : $errorQuery, $errorQuery !== '' ? 'error' : 'success');
                $this->redirect('/orders');
                return;
            }

            $userId = (int) ($this->currentUserId() ?? 0);
            if ($userId <= 0) {
                $this->redirect('/?error=login_required');
                return;
            }

            $orders = $this->orderService->getMyOrders($userId);
            $orders = $this->filterOrders($orders);
            [$success, $error] = $this->consumeFlash('order');

            $this->render('Orders/Index', [
                'orders' => $orders,
                'statusFilter' => strtolower(trim((string) $this->input('status', ''))),
                'search' => strtolower(trim((string) $this->input('q', ''))),
                'success' => $success,
                'error' => $error,
            ]);
        } catch (\Throwable $e) {
            $this->setFlash('order', 'Failed to load orders: ' . $e->getMessage(), 'error');
            $this->redirect('/orders');
        }
    }

    public function show(int $id): void
    {
        Middleware::requireAuth();
        Middleware::requireCustomer();

        try {
            $successQuery = trim((string) $this->input('success', ''));
            $errorQuery = trim((string) $this->input('error', ''));
            if ($successQuery !== '' || $errorQuery !== '') {
                $this->setFlash('order', $successQuery !== '' ? $successQuery : $errorQuery, $errorQuery !== '' ? 'error' : 'success');
                $this->redirect('/orders/' . $id);
                return;
            }

            $userId = (int) ($this->currentUserId() ?? 0);
            if ($userId <= 0) {
                $this->redirect('/?error=login_required');
                return;
            }

            $order = $this->orderService->getMyOrder($userId, $id);
            $items = $this->orderItemService->getByOrderId($id);
            [$success, $error] = $this->consumeFlash('order');

            $this->render('Orders/ShowOrders', [
                'order' => $order,
                'items' => $items,
                'success' => $success,
                'error' => $error,
            ]);
        } catch (\Throwable $e) {
            $this->setFlash('order', 'Order not found: ' . $e->getMessage(), 'error');
            $this->redirect('/orders');
        }
    }

    public function cancel(int $id): void
    {
        Middleware::requireAuth();
        Middleware::requireCustomer();
        $this->validateCsrf();

        try {
            $userId = (int) ($this->currentUserId() ?? 0);
            if ($userId <= 0) {
                $this->redirect('/?error=login_required');
                return;
            }

            $ok = $this->orderService->cancelMyOrder($userId, $id);
            $message = $ok ? 'Order cancelled successfully.' : 'Unable to cancel order.';
            $this->setFlash('order', $message, $ok ? 'success' : 'error');
            $this->redirect('/orders');
        } catch (\Throwable $e) {
            $this->setFlash('order', 'Cancel failed: ' . $e->getMessage(), 'error');
            $this->redirect('/orders');
        }
    }

    public function adminUpdateStatus(int $id): void
    {
        Middleware::requireAdmin();
        $this->validateCsrf();

        try {
            $rawStatus = strtolower(trim((string) $this->input('status', '')));
            $newStatus = OrderStatus::from($rawStatus);

            $this->orderService->adminUpdateStatus($id, $newStatus);
            $this->setFlash('admin', 'Order status updated.', 'success');
        } catch (\ValueError $e) {
            $this->setFlash('admin', 'Invalid order status.', 'error');
        } catch (\Throwable $e) {
            $this->setFlash('admin', $e->getMessage(), 'error');
        }

        $this->redirect('/admin/orders');
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
